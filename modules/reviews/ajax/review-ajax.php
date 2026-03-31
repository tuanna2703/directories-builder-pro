<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Reviews\Ajax;
use DirectoriesBuilderPro\Services\Review_Service;
use DirectoriesBuilderPro\Repositories\Review_Repository;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Review_Ajax class.
 *
 * Handles AJAX requests for review submission, voting, and flagging.
 *
 * @package DirectoriesBuilderPro\Modules\Reviews\Ajax
 */
class Review_Ajax {
    /**
     * Handle review submission (dbp_submit_review).
     *
     * @return void
     */
    public function handle_submit(): void {
        check_ajax_referer( 'dbp_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [
                'message' => __( 'You must be logged in to submit a review.', 'directories-builder-pro' ),
            ], 401 );
        }
        $service = new Review_Service();
        $result  = $service->submit_review( [
            'business_id' => absint( $_POST['business_id'] ?? 0 ),
            'rating'      => absint( $_POST['rating'] ?? 0 ),
            'content'     => wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) ),
            'photos'      => isset( $_POST['photos'] ) ? array_map( 'absint', (array) $_POST['photos'] ) : [],
        ] );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [
                'message' => $result->get_error_message(),
            ], $result->get_error_data()['status'] ?? 400 );
        }
        wp_send_json_success( [
            'review_id' => $result,
            'message'   => __( 'Your review has been submitted successfully!', 'directories-builder-pro' ),
        ] );
    }
    /**
     * Handle review voting (dbp_vote_review).
     *
     * @return void
     */
    public function handle_vote(): void {
        check_ajax_referer( 'dbp_nonce', 'nonce' );
        if ( ! current_user_can( 'read' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to vote.', 'directories-builder-pro' ),
            ], 403 );
        }
        $review_id = absint( $_POST['review_id'] ?? 0 );
        $vote_type = sanitize_text_field( wp_unslash( $_POST['vote_type'] ?? '' ) );
        $user_id   = get_current_user_id();
        if ( ! in_array( $vote_type, [ 'helpful', 'not_helpful' ], true ) ) {
            wp_send_json_error( [
                'message' => __( 'Invalid vote type.', 'directories-builder-pro' ),
            ], 400 );
        }
        $repository = new Review_Repository();
        if ( $repository->has_voted( $review_id, $user_id ) ) {
            wp_send_json_error( [
                'message' => __( 'You have already voted on this review.', 'directories-builder-pro' ),
            ], 409 );
        }
        $repository->insert_vote( $review_id, $user_id, $vote_type );
        $counts = $repository->get_vote_counts( $review_id );
        // Award points to the review author for helpful votes.
        if ( $vote_type === 'helpful' ) {
            $review = $repository->find_by_id( $review_id );
            if ( $review ) {
                $user_service = new \DirectoriesBuilderPro\Services\User_Service();
                $user_service->award_points( (int) $review['user_id'], 2, 'helpful_vote_received' );
            }
        }
        wp_send_json_success( [
            'counts'  => $counts,
            'message' => __( 'Your vote has been recorded.', 'directories-builder-pro' ),
        ] );
    }
    /**
     * Handle review flagging (dbp_flag_review).
     *
     * @return void
     */
    public function handle_flag(): void {
        check_ajax_referer( 'dbp_nonce', 'nonce' );
        if ( ! current_user_can( 'read' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to flag reviews.', 'directories-builder-pro' ),
            ], 403 );
        }
        $review_id = absint( $_POST['review_id'] ?? 0 );
        $user_id   = get_current_user_id();
        $repository = new Review_Repository();
        if ( $repository->has_flagged( $review_id, $user_id ) ) {
            wp_send_json_error( [
                'message' => __( 'You have already flagged this review.', 'directories-builder-pro' ),
            ], 409 );
        }
        $repository->insert_vote( $review_id, $user_id, 'flag' );
        // Notify admin if threshold reached.
        $flag_count = $repository->get_flag_count( $review_id );
        if ( $flag_count >= 3 ) {
            $review = $repository->find_by_id( $review_id );
            if ( $review && $review['status'] !== 'spam' ) {
                wp_mail(
                    get_option( 'admin_email' ),
                    /* translators: %d: review ID */
                    sprintf( __( '[Directory] Review #%d flagged multiple times', 'directories-builder-pro' ), $review_id ),
                    /* translators: %1$d: review ID, %2$d: flag count */
                    sprintf( __( 'Review #%1$d has received %2$d flags and may need moderation.', 'directories-builder-pro' ), $review_id, $flag_count )
                );
            }
        }
        wp_send_json_success( [
            'message' => __( 'Thank you for your report.', 'directories-builder-pro' ),
        ] );
    }
}