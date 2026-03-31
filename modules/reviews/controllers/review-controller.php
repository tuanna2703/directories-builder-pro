<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Reviews\Controllers;
use DirectoriesBuilderPro\Core\Base\Controller_Base;
use DirectoriesBuilderPro\Services\Review_Service;
use DirectoriesBuilderPro\Repositories\Review_Repository;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Review_Controller class.
 *
 * REST API controller for review operations.
 *
 * @package DirectoriesBuilderPro\Modules\Reviews\Controllers
 */
class Review_Controller extends Controller_Base {
    /**
     * Review service.
     *
     * @var Review_Service
     */
    private Review_Service $service;
    /**
     * Review repository.
     *
     * @var Review_Repository
     */
    private Review_Repository $repository;
    /**
     * Constructor.
     */
    public function __construct() {
        $this->service    = new Review_Service();
        $this->repository = new Review_Repository();
    }
    /**
     * Register all REST routes for reviews.
     *
     * @return void
     */
    public function register_routes(): void {
        // GET /reviews
        $this->register_route( '/reviews', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_reviews' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'business_id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'page' => [
                    'default'           => 1,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default'           => 10,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'orderby' => [
                    'default'           => 'relevance',
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ] );
        // POST /reviews
        $this->register_route( '/reviews', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'submit_review' ],
            'permission_callback' => [ $this, 'check_logged_in' ],
            'args'                => [
                'business_id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'rating' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'content' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'wp_kses_post',
                ],
                'photos' => [
                    'type'    => 'array',
                    'default' => [],
                ],
            ],
        ] );
        // PUT /reviews/{id}
        $this->register_route( '/reviews/(?P<id>\d+)', [
            'methods'             => 'PUT',
            'callback'            => [ $this, 'update_review' ],
            'permission_callback' => [ $this, 'check_review_edit_permission' ],
            'args'                => [
                'id' => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ] );
        // DELETE /reviews/{id}
        $this->register_route( '/reviews/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [ $this, 'delete_review' ],
            'permission_callback' => [ $this, 'check_review_edit_permission' ],
            'args'                => [
                'id' => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ] );
        // POST /reviews/{id}/vote
        $this->register_route( '/reviews/(?P<id>\d+)/vote', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'vote_review' ],
            'permission_callback' => [ $this, 'check_logged_in' ],
            'args'                => [
                'id'   => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'type' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ] );
        // POST /reviews/{id}/flag
        $this->register_route( '/reviews/(?P<id>\d+)/flag', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'flag_review' ],
            'permission_callback' => [ $this, 'check_logged_in' ],
            'args'                => [
                'id' => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ] );
    }
    /**
     * GET /reviews — Get reviews for a business.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public function get_reviews( WP_REST_Request $request ): WP_REST_Response {
        $reviews = $this->service->get_reviews_for_business(
            (int) $request->get_param( 'business_id' ),
            [
                'page'     => (int) $request->get_param( 'page' ),
                'per_page' => (int) $request->get_param( 'per_page' ),
                'orderby'  => $request->get_param( 'orderby' ),
            ]
        );
        // Enrich review data.
        foreach ( $reviews as &$review ) {
            $user = get_userdata( (int) $review['user_id'] );
            $review['author_name']   = $user ? $user->display_name : __( 'Anonymous', 'directories-builder-pro' );
            $review['author_avatar'] = get_avatar_url( (int) $review['user_id'], [ 'size' => 40 ] );
            $review['is_elite']      = (bool) get_user_meta( (int) $review['user_id'], 'dbp_elite', true );
            $review['time_ago']      = dbp_time_ago( $review['created_at'] );
            $review['photo_urls']    = $this->get_photo_urls( $review['photos'] ?? '' );
        }
        unset( $review );
        return $this->success( [
            'reviews' => $reviews,
            'total'   => $this->repository->count( [
                'business_id' => (int) $request->get_param( 'business_id' ),
                'status'      => 'approved',
            ] ),
        ] );
    }
    /**
     * POST /reviews — Submit a new review.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function submit_review( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $result = $this->service->submit_review( [
            'business_id' => (int) $request->get_param( 'business_id' ),
            'rating'      => (int) $request->get_param( 'rating' ),
            'content'     => $request->get_param( 'content' ),
            'photos'      => $request->get_param( 'photos' ) ?: [],
        ] );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        return $this->success( [
            'id'      => $result,
            'message' => __( 'Review submitted successfully.', 'directories-builder-pro' ),
        ], 201 );
    }
    /**
     * PUT /reviews/{id} — Update a review.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function update_review( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id   = (int) $request->get_param( 'id' );
        $data = [];
        if ( $request->has_param( 'content' ) ) {
            $data['content'] = wp_kses_post( $request->get_param( 'content' ) );
        }
        if ( $request->has_param( 'rating' ) ) {
            $data['rating'] = max( 1, min( 5, (int) $request->get_param( 'rating' ) ) );
        }
        // Business owner response.
        if ( $request->has_param( 'response' ) ) {
            $data['response']      = wp_kses_post( $request->get_param( 'response' ) );
            $data['response_date'] = current_time( 'mysql' );
        }
        if ( empty( $data ) ) {
            return $this->error( __( 'No data to update.', 'directories-builder-pro' ), 400 );
        }
        $result = $this->repository->update( $id, $data );
        if ( ! $result ) {
            return $this->error( __( 'Failed to update review.', 'directories-builder-pro' ), 500 );
        }
        return $this->success( [ 'message' => __( 'Review updated successfully.', 'directories-builder-pro' ) ] );
    }
    /**
     * DELETE /reviews/{id} — Delete a review.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function delete_review( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id     = (int) $request->get_param( 'id' );
        $review = $this->repository->find_by_id( $id );
        if ( ! $review ) {
            return $this->error( __( 'Review not found.', 'directories-builder-pro' ), 404 );
        }
        $this->repository->delete( $id );
        // Recalculate business average.
        $business_service = new \DirectoriesBuilderPro\Services\Business_Service();
        $business_service->calculate_average_rating( (int) $review['business_id'] );
        return $this->success( [ 'message' => __( 'Review deleted successfully.', 'directories-builder-pro' ) ] );
    }
    /**
     * POST /reviews/{id}/vote — Vote on a review.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function vote_review( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $review_id = (int) $request->get_param( 'id' );
        $vote_type = $request->get_param( 'type' );
        $user_id   = get_current_user_id();
        if ( ! in_array( $vote_type, [ 'helpful', 'not_helpful' ], true ) ) {
            return $this->error( __( 'Invalid vote type.', 'directories-builder-pro' ), 400 );
        }
        if ( $this->repository->has_voted( $review_id, $user_id ) ) {
            return $this->error( __( 'You have already voted on this review.', 'directories-builder-pro' ), 409 );
        }
        $this->repository->insert_vote( $review_id, $user_id, $vote_type );
        $counts = $this->repository->get_vote_counts( $review_id );
        return $this->success( [
            'message' => __( 'Vote recorded.', 'directories-builder-pro' ),
            'counts'  => $counts,
        ] );
    }
    /**
     * POST /reviews/{id}/flag — Flag a review.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function flag_review( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $review_id = (int) $request->get_param( 'id' );
        $user_id   = get_current_user_id();
        if ( $this->repository->has_flagged( $review_id, $user_id ) ) {
            return $this->error( __( 'You have already flagged this review.', 'directories-builder-pro' ), 409 );
        }
        $this->repository->insert_vote( $review_id, $user_id, 'flag' );
        // Notify admin if threshold reached (3 flags).
        $flag_count = $this->repository->get_flag_count( $review_id );
        if ( $flag_count >= 3 ) {
            $review = $this->repository->find_by_id( $review_id );
            if ( $review && $review['status'] !== 'spam' ) {
                wp_mail(
                    get_option( 'admin_email' ),
                    /* translators: %d: review ID */
                    sprintf( __( '[Directory] Review #%d has been flagged multiple times', 'directories-builder-pro' ), $review_id ),
                    /* translators: %d: review ID */
                    sprintf( __( 'Review #%d has received %d flags and may need moderation.', 'directories-builder-pro' ), $review_id, $flag_count )
                );
            }
        }
        return $this->success( [ 'message' => __( 'Review flagged. Thank you for your report.', 'directories-builder-pro' ) ] );
    }
    /**
     * Check if the user is logged in.
     *
     * @return bool|WP_Error
     */
    public function check_logged_in(): bool|WP_Error {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'You must be logged in.', 'directories-builder-pro' ), [ 'status' => 401 ] );
        }
        return true;
    }
    /**
     * Check if the current user can edit a review.
     *
     * @param WP_REST_Request $request REST request.
     * @return bool|WP_Error
     */
    public function check_review_edit_permission( WP_REST_Request $request ): bool|WP_Error {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'You must be logged in.', 'directories-builder-pro' ), [ 'status' => 401 ] );
        }
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }
        $review_id = (int) $request->get_param( 'id' );
        $review    = $this->repository->find_by_id( $review_id );
        if ( ! $review ) {
            return new WP_Error( 'rest_not_found', __( 'Review not found.', 'directories-builder-pro' ), [ 'status' => 404 ] );
        }
        if ( (int) $review['user_id'] !== get_current_user_id() ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to edit this review.', 'directories-builder-pro' ), [ 'status' => 403 ] );
        }
        return true;
    }
    /**
     * Get photo URLs from comma-separated attachment IDs.
     *
     * @param string $photos Comma-separated IDs.
     * @return array
     */
    private function get_photo_urls( string $photos ): array {
        if ( empty( $photos ) ) {
            return [];
        }
        $ids  = array_map( 'absint', explode( ',', $photos ) );
        $urls = [];
        foreach ( $ids as $id ) {
            $url = wp_get_attachment_image_url( $id, 'medium' );
            if ( $url ) {
                $urls[] = $url;
            }
        }
        return $urls;
    }
}