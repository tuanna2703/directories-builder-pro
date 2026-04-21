<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Business\Controllers;
use DirectoriesBuilderPro\Core\Base\Controller_Base;
use DirectoriesBuilderPro\Services\Business_Service;
use DirectoriesBuilderPro\Services\Search_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Business_Controller class.
 *
 * REST API controller for business CRUD operations.
 *
 * @package DirectoriesBuilderPro\Modules\Business\Controllers
 */
class Business_Controller extends Controller_Base {
    private Business_Service $service;
    public function __construct() {
        $this->service = new Business_Service();
    }
    public function register_routes(): void {
        // GET /businesses
        $this->register_route( '/businesses', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_businesses' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'category' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'lat'      => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
                'lng'      => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
                'radius'   => [ 'type' => 'number', 'default' => 10, 'sanitize_callback' => 'floatval' ],
                'page'     => [ 'type' => 'integer', 'default' => 1, 'sanitize_callback' => 'absint' ],
                'per_page' => [ 'type' => 'integer', 'default' => 12, 'sanitize_callback' => 'absint' ],
            ],
        ] );
        // GET /businesses/{id}
        $this->register_route( '/businesses/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_business' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'id' => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
            ],
        ] );
        // POST /businesses
        $this->register_route( '/businesses', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'create_business' ],
            'permission_callback' => [ $this, 'check_admin' ],
        ] );
        // PUT /businesses/{id}
        $this->register_route( '/businesses/(?P<id>\d+)', [
            'methods'             => 'PUT',
            'callback'            => [ $this, 'update_business' ],
            'permission_callback' => [ $this, 'check_business_edit_permission' ],
        ] );
        // POST /checkins
        $this->register_route( '/checkins', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'record_checkin' ],
            'permission_callback' => [ $this, 'check_logged_in' ],
            'args'                => [
                'business_id' => [ 'required' => true, 'type' => 'integer', 'sanitize_callback' => 'absint' ],
            ],
        ] );
    }
    /**
     * GET /businesses
     */
    public function get_businesses( WP_REST_Request $request ): WP_REST_Response {
        $search_service = new Search_Service();
        $results = $search_service->search( [
            'category' => $request->get_param( 'category' ),
            'lat'      => $request->get_param( 'lat' ),
            'lng'      => $request->get_param( 'lng' ),
            'radius_km' => $request->get_param( 'radius' ),
            'page'     => $request->get_param( 'page' ),
            'per_page' => $request->get_param( 'per_page' ),
        ] );
        return $this->success( [
            'businesses' => $results['items'],
            'total'      => $results['total'],
            'pages'      => $results['pages'],
        ] );
    }
    /**
     * GET /businesses/{id}
     */
    public function get_business( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id       = (int) $request->get_param( 'id' );
        $business = $this->service->get_business( $id );
        if ( ! $business ) {
            return $this->error( __( 'Business not found.', 'directories-builder-pro' ), 404 );
        }
        // Add computed fields.
        $business['thumbnail_url'] = get_the_post_thumbnail_url( (int) $business['wp_post_id'], 'large' )
            ?: dbp_get_placeholder_image_url();
        $terms = wp_get_post_terms( (int) $business['wp_post_id'], 'dbp_category', [ 'fields' => 'names' ] );
        $business['category'] = is_array( $terms ) && ! empty( $terms ) ? $terms[0] : '';
        $business['is_claimed']  = ! empty( $business['claimed_by'] );
        $business['is_featured'] = (bool) ( $business['featured'] ?? false );
        $business['is_open']     = dbp_is_business_open( $business['hours'] ?? '[]' );
        return $this->success( $business );
    }
    /**
     * POST /businesses
     */
    public function create_business( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $result = $this->service->create_business( $request->get_params() );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        return $this->success( [ 'id' => $result, 'message' => __( 'Business created.', 'directories-builder-pro' ) ], 201 );
    }
    /**
     * PUT /businesses/{id}
     */
    public function update_business( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $result = $this->service->update_business(
            (int) $request->get_param( 'id' ),
            $request->get_params()
        );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        return $this->success( [ 'message' => __( 'Business updated.', 'directories-builder-pro' ) ] );
    }
    public function record_checkin( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $business_id = (int) $request->get_param( 'business_id' );
        $user_id     = get_current_user_id();
        
        $repository = new \DirectoriesBuilderPro\Repositories\Business_Repository();

        if ( $repository->has_checked_in_today( $business_id, $user_id ) ) {
            return $this->error( __( 'You have already checked in today.', 'directories-builder-pro' ), 409 );
        }

        $checkin_id = $repository->insert_checkin( $business_id, $user_id );
        // Award points.
        $user_service = new \DirectoriesBuilderPro\Services\User_Service();
        $user_service->award_points( $user_id, 5, 'checkin' );
        /**
         * Fires after a check-in is recorded.
         *
         * @param int $checkin_id  Check-in ID.
         * @param int $business_id Business ID.
         * @param int $user_id     User ID.
         */
        do_action( 'dbp/checkin/recorded', $checkin_id, $business_id, $user_id );
        return $this->success( [
            'id'      => $checkin_id,
            'message' => __( 'Checked in successfully!', 'directories-builder-pro' ),
        ], 201 );
    }
    public function check_admin(): bool|WP_Error {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'Admin access required.', 'directories-builder-pro' ), [ 'status' => 403 ] );
        }
        return true;
    }
    public function check_logged_in(): bool|WP_Error {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'You must be logged in.', 'directories-builder-pro' ), [ 'status' => 401 ] );
        }
        return true;
    }
    public function check_business_edit_permission( WP_REST_Request $request ): bool|WP_Error {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'Login required.', 'directories-builder-pro' ), [ 'status' => 401 ] );
        }
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }
        $id       = (int) $request->get_param( 'id' );
        $business = $this->service->get_business( $id );
        if ( $business && (int) ( $business['claimed_by'] ?? 0 ) === get_current_user_id() ) {
            return true;
        }
        return new WP_Error( 'rest_forbidden', __( 'Permission denied.', 'directories-builder-pro' ), [ 'status' => 403 ] );
    }
}