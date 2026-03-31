<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Services;
use DirectoriesBuilderPro\Repositories\Review_Repository;
use WP_Error;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Review_Service class.
 *
 * Business logic for reviews: submission, approval, trust scoring, moderation.
 *
 * @package DirectoriesBuilderPro\Services
 */
class Review_Service {
    /**
     * Repository instance.
     *
     * @var Review_Repository
     */
    private Review_Repository $repository;
    /**
     * Business service instance.
     *
     * @var Business_Service
     */
    private Business_Service $business_service;
    /**
     * Constructor.
     */
    public function __construct() {
        $this->repository       = new Review_Repository();
        $this->business_service = new Business_Service();
    }
    /**
     * Submit a new review.
     *
     * @param array $data Review data: business_id, rating, content, photos (optional).
     * @return int|WP_Error Review ID on success, WP_Error on failure.
     */
    public function submit_review( array $data ): int|WP_Error {
        $user_id     = get_current_user_id();
        $business_id = (int) ( $data['business_id'] ?? 0 );
        $rating      = (int) ( $data['rating'] ?? 0 );
        $content     = $data['content'] ?? '';
        $min_length  = (int) get_option( 'dbp_min_review_length', 25 );
        // Validate.
        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'You must be logged in to submit a review.', 'directories-builder-pro' ), [ 'status' => 401 ] );
        }
        if ( $business_id <= 0 ) {
            return new WP_Error( 'invalid_business', __( 'Invalid business ID.', 'directories-builder-pro' ), [ 'status' => 400 ] );
        }
        if ( $rating < 1 || $rating > 5 ) {
            return new WP_Error( 'invalid_rating', __( 'Rating must be between 1 and 5.', 'directories-builder-pro' ), [ 'status' => 400 ] );
        }
        if ( mb_strlen( $content ) < $min_length ) {
            return new WP_Error(
                'content_too_short',
                /* translators: %d: minimum character count */
                sprintf( __( 'Review must be at least %d characters.', 'directories-builder-pro' ), $min_length ),
                [ 'status' => 400 ]
            );
        }
        // One review per user per business.
        if ( $this->repository->user_has_reviewed( $business_id, $user_id ) ) {
            return new WP_Error( 'already_reviewed', __( 'You have already reviewed this business.', 'directories-builder-pro' ), [ 'status' => 409 ] );
        }
        // Rate limiting: max 3 reviews per user per hour.
        if ( $this->is_rate_limited( $user_id ) ) {
            return new WP_Error( 'rate_limited', __( 'You are submitting reviews too quickly. Please wait before trying again.', 'directories-builder-pro' ), [ 'status' => 429 ] );
        }
        // Calculate trust score.
        $trust_score = $this->calculate_trust_score( $user_id, $data );
        /**
         * Filter the calculated trust score.
         *
         * @param int   $trust_score The calculated trust score.
         * @param int   $user_id     The user ID.
         * @param array $data        The review data.
         */
        $trust_score = (int) apply_filters( 'dbp/review/trust_score', $trust_score, $user_id, $data );
        // Determine status.
        $moderation_mode = get_option( 'dbp_moderation_mode', 'manual' );
        if ( $trust_score < 15 ) {
            $status = 'pending'; // Low trust always goes to moderation.
        } elseif ( $moderation_mode === 'auto_approve' ) {
            $status = 'approved';
        } else {
            $status = 'pending';
        }
        // Handle photo attachment IDs.
        $photos = '';
        if ( ! empty( $data['photos'] ) && is_array( $data['photos'] ) ) {
            $max_photos = (int) get_option( 'dbp_max_photos_per_review', 5 );
            $photo_ids  = array_slice( array_map( 'absint', $data['photos'] ), 0, $max_photos );
            $photos     = implode( ',', $photo_ids );
        }
        // Insert review.
        $insert_data = [
            'business_id' => $business_id,
            'user_id'     => $user_id,
            'rating'      => $rating,
            'content'     => wp_kses_post( $content ),
            'status'      => $status,
            'trust_score' => $trust_score,
            'photos'      => $photos,
        ];
        $review_id = $this->repository->insert( $insert_data );
        if ( $review_id === false ) {
            return new WP_Error( 'insert_failed', __( 'Failed to submit review.', 'directories-builder-pro' ), [ 'status' => 500 ] );
        }
        // Update business average rating if auto-approved.
        if ( $status === 'approved' ) {
            $this->business_service->calculate_average_rating( $business_id );
        }
        /**
         * Fires after a review is submitted.
         *
         * @param int $review_id   The review ID.
         * @param int $business_id The business ID.
         * @param int $user_id     The user ID.
         */
        do_action( 'dbp/review/submitted', $review_id, $business_id, $user_id );
        return $review_id;
    }
    /**
     * Calculate trust score for a reviewer.
     *
     * @param int   $user_id     User ID.
     * @param array $review_data Review data.
     * @return int Trust score.
     */
    public function calculate_trust_score( int $user_id, array $review_data ): int {
        $score = 0;
        // Account age.
        $user = get_userdata( $user_id );
        if ( $user ) {
            $registered  = strtotime( $user->user_registered );
            $days        = ( time() - $registered ) / DAY_IN_SECONDS;
            if ( $days > 180 ) {
                $score += 20;
            } elseif ( $days > 30 ) {
                $score += 10;
            }
        }
        // Prior approved reviews.
        $approved_count = $this->repository->count( [
            'user_id' => $user_id,
            'status'  => 'approved',
        ] );
        if ( $approved_count >= 5 ) {
            $score += 15;
        }
        // Profile photo set.
        $avatar_url = get_avatar_url( $user_id );
        if ( $avatar_url && ! str_contains( $avatar_url, 'gravatar.com/avatar/?d=' ) ) {
            $score += 10;
        }
        // Review length.
        $content_length = mb_strlen( $review_data['content'] ?? '' );
        if ( $content_length >= 100 ) {
            $score += 10;
        }
        // Prior spam flags.
        $spam_count = $this->repository->count( [
            'user_id' => $user_id,
            'status'  => 'spam',
        ] );
        if ( $spam_count > 0 ) {
            $score -= 30;
        }
        return max( 0, $score );
    }
    /**
     * Approve a review.
     *
     * @param int $id Review ID.
     * @return bool
     */
    public function approve_review( int $id ): bool {
        $review = $this->repository->find_by_id( $id );
        if ( ! $review ) {
            return false;
        }
        $result = $this->repository->update( $id, [ 'status' => 'approved' ] );
        if ( $result ) {
            $this->business_service->calculate_average_rating( (int) $review['business_id'] );
            /**
             * Fires after a review is approved.
             *
             * @param int $review_id The review ID.
             */
            do_action( 'dbp/review/approved', $id );
        }
        return $result;
    }
    /**
     * Reject a review.
     *
     * @param int    $id     Review ID.
     * @param string $reason Rejection reason.
     * @return bool
     */
    public function reject_review( int $id, string $reason = '' ): bool {
        return $this->repository->update( $id, [ 'status' => 'rejected' ] );
    }
    /**
     * Mark a review as spam.
     *
     * @param int $id Review ID.
     * @return bool
     */
    public function mark_spam( int $id ): bool {
        $review = $this->repository->find_by_id( $id );
        if ( ! $review ) {
            return false;
        }
        $result = $this->repository->update( $id, [ 'status' => 'spam' ] );
        if ( $result ) {
            $this->business_service->calculate_average_rating( (int) $review['business_id'] );
        }
        return $result;
    }
    /**
     * Get reviews for a business.
     *
     * @param int   $business_id Business ID.
     * @param array $args        page, per_page, orderby, status.
     * @return array
     */
    public function get_reviews_for_business( int $business_id, array $args = [] ): array {
        return $this->repository->find_by_business( $business_id, $args );
    }
    /**
     * Check rate limiting for review submissions.
     *
     * @param int $user_id User ID.
     * @return bool True if rate limited.
     */
    private function is_rate_limited( int $user_id ): bool {
        global $wpdb;
        $table = $this->repository->get_table_name();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $recent_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND created_at >= %s",
                $user_id,
                gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS )
            )
        );
        return $recent_count >= 3;
    }
    /**
     * Get the repository instance.
     *
     * @return Review_Repository
     */
    public function get_repository(): Review_Repository {
        return $this->repository;
    }
}