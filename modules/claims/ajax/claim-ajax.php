<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Claims\Ajax;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Claim_Ajax {
    public function handle_submit(): void {
        check_ajax_referer( 'dbp_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'directories-builder-pro' ) ], 401 );
        }
        global $wpdb;
        $business_id = absint( $_POST['business_id'] ?? 0 );
        $user_id     = get_current_user_id();
        // Validate business exists.
        $business_service = new \DirectoriesBuilderPro\Services\Business_Service();
        $business = $business_service->get_business( $business_id );
        if ( ! $business ) {
            wp_send_json_error( [ 'message' => __( 'Business not found.', 'directories-builder-pro' ) ], 404 );
        }
        if ( ! empty( $business['claimed_by'] ) ) {
            wp_send_json_error( [ 'message' => __( 'This business has already been claimed.', 'directories-builder-pro' ) ], 409 );
        }
        $verification = sanitize_text_field( wp_unslash( $_POST['verification_method'] ?? 'email' ) );
        if ( ! in_array( $verification, [ 'phone', 'email', 'document' ], true ) ) {
            $verification = 'email';
        }
        $table = $wpdb->prefix . 'dbp_claims';
        $wpdb->insert( $table, [
            'business_id'         => $business_id,
            'user_id'             => $user_id,
            'owner_name'          => sanitize_text_field( wp_unslash( $_POST['owner_name'] ?? '' ) ),
            'email'               => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
            'phone'               => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
            'verification_method' => $verification,
            'status'              => 'pending',
        ], [ '%d', '%d', '%s', '%s', '%s', '%s', '%s' ] );
        $claim_id = (int) $wpdb->insert_id;
        // Notify admin.
        wp_mail(
            get_option( 'admin_email' ),
            sprintf( __( '[Directory] New claim for "%s"', 'directories-builder-pro' ), $business['name'] ),
            sprintf( __( 'A new claim has been submitted for "%s". Review it in the dashboard.', 'directories-builder-pro' ), $business['name'] )
        );
        wp_send_json_success( [
            'claim_id' => $claim_id,
            'message'  => __( 'Your claim has been submitted and is pending review.', 'directories-builder-pro' ),
        ] );
    }
}