<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Controllers;
use DirectoriesBuilderPro\Core\Base\Controller_Base;
use DirectoriesBuilderPro\Core\Managers\Form_Manager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Form_Controller class.
 *
 * REST API endpoints for the form engine.
 *
 * @package DirectoriesBuilderPro\Modules\Form\Controllers
 */
class Form_Controller extends Controller_Base {
    /**
     * Register REST routes.
     *
     * @return void
     */
    public function register_routes(): void {
        // GET /forms — list all registered forms.
        $this->register_route( '/forms', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'list_forms' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ] );
        // GET /forms/{name}/schema — get form schema + current values.
        $this->register_route( '/forms/(?P<name>[a-z_]+)/schema', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_schema' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'args' => [
                'name' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_key',
                ],
                'object_id' => [
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ] );
        // POST /forms/{name}/save — save form values.
        $this->register_route( '/forms/(?P<name>[a-z_]+)/save', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_form' ],
            'permission_callback' => [ $this, 'save_permission_check' ],
            'args' => [
                'name' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_key',
                ],
                'values' => [
                    'required' => true,
                    'type'     => 'object',
                ],
                'object_id' => [
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ] );
    }
    /**
     * List all registered form names and titles.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function list_forms( WP_REST_Request $request ): WP_REST_Response {
        $forms = Form_Manager::get_instance()->get_all();
        $list  = [];
        foreach ( $forms as $form ) {
            $list[] = [
                'name'  => $form->get_name(),
                'title' => $form->get_title(),
            ];
        }
        return $this->success( $list );
    }
    /**
     * Get form schema with current values.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_schema( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $name = $request->get_param( 'name' );
        $form = Form_Manager::get_instance()->get( $name );
        if ( $form === null ) {
            return $this->error( __( 'Form not found.', 'directories-builder-pro' ), 404, 'form_not_found' );
        }
        $object_id = $request->get_param( 'object_id' ) ?: null;
        return $this->success( [
            'schema' => $form->get_schema(),
            'values' => $form->get_values( $object_id ),
        ] );
    }
    /**
     * Save form values.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function save_form( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $name = $request->get_param( 'name' );
        $form = Form_Manager::get_instance()->get( $name );
        if ( $form === null ) {
            return $this->error( __( 'Form not found.', 'directories-builder-pro' ), 404, 'form_not_found' );
        }
        $values    = (array) $request->get_param( 'values' );
        $object_id = $request->get_param( 'object_id' ) ?: null;
        // Additional validation for post_meta forms.
        if ( $form->get_storage_type() === 'post_meta' && $object_id !== null ) {
            if ( get_post_type( $object_id ) !== 'dbp_business' ) {
                return $this->error( __( 'Invalid business post.', 'directories-builder-pro' ), 400, 'invalid_post' );
            }
        }
        // Additional validation for user_meta forms.
        if ( $form->get_storage_type() === 'user_meta' && $object_id !== null ) {
            if ( get_userdata( $object_id ) === false ) {
                return $this->error( __( 'Invalid user.', 'directories-builder-pro' ), 400, 'invalid_user' );
            }
        }
        $result = $form->save( $values, $object_id );
        if ( $result instanceof WP_Error ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => $result->get_error_messages(),
            ], 400 );
        }
        return $this->success( [
            'success' => true,
            'message' => __( 'Settings saved successfully.', 'directories-builder-pro' ),
        ] );
    }
    /**
     * Permission check for save endpoint.
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    public function save_permission_check( WP_REST_Request $request ): bool|WP_Error {
        $name      = sanitize_key( $request->get_param( 'name' ) ?? '' );
        $object_id = absint( $request->get_param( 'object_id' ) ?? 0 );
        return match ( $name ) {
            'plugin_settings' => current_user_can( 'manage_options' ),
            'business_settings' => $this->can_edit_business( $object_id ),
            'user_profile' => $this->can_edit_user( $object_id ),
            default => current_user_can( 'manage_options' ),
        };
    }
    /**
     * Check if current user can edit a business.
     *
     * @param int $post_id Post ID.
     * @return bool
     */
    private function can_edit_business( int $post_id ): bool {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }
        if ( $post_id <= 0 ) {
            return false;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }
        // Check if business is claimed by current user.
        $business_service = new \DirectoriesBuilderPro\Services\Business_Service();
        $business = $business_service->get_business_by_post_id( $post_id );
        if ( $business && isset( $business['claimed_by'] ) ) {
            return (int) $business['claimed_by'] === get_current_user_id();
        }
        return false;
    }
    /**
     * Check if current user can edit a user profile.
     *
     * @param int $user_id User ID.
     * @return bool
     */
    private function can_edit_user( int $user_id ): bool {
        if ( $user_id <= 0 ) {
            return false;
        }
        if ( $user_id === get_current_user_id() ) {
            return true;
        }
        return current_user_can( 'edit_user', $user_id );
    }
}
