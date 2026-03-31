<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Business\Ajax;
use DirectoriesBuilderPro\Repositories\Business_Repository;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Business_Ajax class.
 *
 * AJAX handlers for business hours and meta updates.
 *
 * @package DirectoriesBuilderPro\Modules\Business\Ajax
 */
class Business_Ajax {
    /**
     * Get business hours (public, nopriv).
     */
    public function handle_get_hours(): void {
        $business_id = absint( $_POST['business_id'] ?? $_GET['business_id'] ?? 0 );
        if ( ! $business_id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid business ID.', 'directories-builder-pro' ) ], 400 );
        }
        $repository = new Business_Repository();
        $business   = $repository->find_by_id( $business_id );
        if ( ! $business ) {
            wp_send_json_error( [ 'message' => __( 'Business not found.', 'directories-builder-pro' ) ], 404 );
        }
        $hours = $business['hours'] ?? '[]';
        if ( is_string( $hours ) ) {
            $hours = json_decode( $hours, true ) ?: [];
        }
        wp_send_json_success( [
            'hours'   => $hours,
            'is_open' => dbp_is_business_open( $hours ),
        ] );
    }
    /**
     * Update business meta (authenticated, owner/admin only).
     */
    public function handle_update_meta(): void {
        check_ajax_referer( 'dbp_nonce', 'nonce' );
        $business_id = absint( $_POST['business_id'] ?? 0 );
        $meta_key    = sanitize_key( $_POST['meta_key'] ?? '' );
        $meta_value  = sanitize_text_field( wp_unslash( $_POST['meta_value'] ?? '' ) );
        if ( ! $business_id || empty( $meta_key ) ) {
            wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'directories-builder-pro' ) ], 400 );
        }
        // Verify permissions.
        $repository = new Business_Repository();
        $business   = $repository->find_by_id( $business_id );
        if ( ! $business ) {
            wp_send_json_error( [ 'message' => __( 'Business not found.', 'directories-builder-pro' ) ], 404 );
        }
        $current_user_id = get_current_user_id();
        if ( ! current_user_can( 'manage_options' ) && (int) ( $business['claimed_by'] ?? 0 ) !== $current_user_id ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'directories-builder-pro' ) ], 403 );
        }
        $result = $repository->update_meta( $business_id, $meta_key, $meta_value );
        if ( $result ) {
            wp_send_json_success( [ 'message' => __( 'Meta updated successfully.', 'directories-builder-pro' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Failed to update meta.', 'directories-builder-pro' ) ], 500 );
        }
    }
}