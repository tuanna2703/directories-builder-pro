<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Claims\Controllers;
use DirectoriesBuilderPro\Core\Base\Controller_Base;
use DirectoriesBuilderPro\Services\Business_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Claim_Controller extends Controller_Base {
    public function register_routes(): void {
        // POST /claims
        $this->register_route( '/claims', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'submit_claim' ],
            'permission_callback' => [ $this, 'check_logged_in' ],
            'args'                => [
                'business_id'         => [ 'required' => true, 'type' => 'integer', 'sanitize_callback' => 'absint' ],
                'owner_name'          => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'email'               => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_email' ],
                'phone'               => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'verification_method' => [ 'type' => 'string', 'default' => 'email', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );
        // GET /claims/{id}
        $this->register_route( '/claims/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_claim' ],
            'permission_callback' => [ $this, 'check_admin' ],
        ] );
        // PUT /claims/{id}/approve
        $this->register_route( '/claims/(?P<id>\d+)/approve', [
            'methods'             => 'PUT',
            'callback'            => [ $this, 'approve_claim' ],
            'permission_callback' => [ $this, 'check_admin' ],
        ] );
        // PUT /claims/{id}/reject
        $this->register_route( '/claims/(?P<id>\d+)/reject', [
            'methods'             => 'PUT',
            'callback'            => [ $this, 'reject_claim' ],
            'permission_callback' => [ $this, 'check_admin' ],
            'args'                => [
                'reason' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ],
            ],
        ] );
    }
    public function submit_claim( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $business_id = (int) $request->get_param( 'business_id' );
        $user_id     = get_current_user_id();
        // Check business exists.
        $business_service = new Business_Service();
        $business = $business_service->get_business( $business_id );
        if ( ! $business ) {
            return $this->error( __( 'Business not found.', 'directories-builder-pro' ), 404 );
        }
        // Check not already claimed.
        if ( ! empty( $business['claimed_by'] ) ) {
            return $this->error( __( 'This business has already been claimed.', 'directories-builder-pro' ), 409 );
        }
        // Check user hasn't already submitted a claim for this business.
        $repository = new \DirectoriesBuilderPro\Modules\Claims\Repositories\Claim_Repository();
        
        if ( $repository->has_pending_claim( $business_id, $user_id ) ) {
            return $this->error( __( 'You already have a pending claim for this business.', 'directories-builder-pro' ), 409 );
        }
        // Validate verification method.
        $verification = $request->get_param( 'verification_method' );
        if ( ! in_array( $verification, [ 'phone', 'email', 'document' ], true ) ) {
            $verification = 'email';
        }
        // Insert claim.
        $claim_id = $repository->insert([
            'business_id'         => $business_id,
            'user_id'             => $user_id,
            'owner_name'          => $request->get_param( 'owner_name' ),
            'email'               => $request->get_param( 'email' ),
            'phone'               => $request->get_param( 'phone' ) ?? '',
            'verification_method' => $verification,
            'status'              => 'pending',
        ]);
        // Send admin notification email.
        wp_mail(
            get_option( 'admin_email' ),
            /* translators: %s: business name */
            sprintf( __( '[Directory] New business claim for "%s"', 'directories-builder-pro' ), $business['name'] ),
            sprintf(
                /* translators: %1$s: business name, %2$s: owner name, %3$s: email, %4$s: admin URL */
                __( "A new claim has been submitted for \"%1\$s\".\n\nOwner: %2\$s\nEmail: %3\$s\n\nReview this claim: %4\$s", 'directories-builder-pro' ),
                $business['name'],
                $request->get_param( 'owner_name' ),
                $request->get_param( 'email' ),
                admin_url( 'admin.php?page=dbp-dashboard' )
            )
        );
        return $this->success( [
            'id'      => $claim_id,
            'message' => __( 'Your claim has been submitted and is pending review.', 'directories-builder-pro' ),
        ], 201 );
    }
    public function get_claim( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id         = (int) $request->get_param( 'id' );
        $repository = new \DirectoriesBuilderPro\Modules\Claims\Repositories\Claim_Repository();
        
        $claim = $repository->find_by_id( $id );
        
        if ( ! $claim ) {
            return $this->error( __( 'Claim not found.', 'directories-builder-pro' ), 404 );
        }
        return $this->success( $claim );
    }
    public function approve_claim( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id         = (int) $request->get_param( 'id' );
        $repository = new \DirectoriesBuilderPro\Modules\Claims\Repositories\Claim_Repository();
        
        $claim = $repository->find_by_id( $id );
        
        if ( ! $claim ) {
            return $this->error( __( 'Claim not found.', 'directories-builder-pro' ), 404 );
        }
        if ( $claim['status'] !== 'pending' ) {
            return $this->error( __( 'This claim has already been processed.', 'directories-builder-pro' ), 409 );
        }
        // Update claim status.
        $repository->approve( $id, (int) $claim['user_id'] );
        // Set business claimed_by.
        $business_service = new Business_Service();
        $business_service->update_business( (int) $claim['business_id'], [
            'claimed_by' => (int) $claim['user_id'],
        ] );
        // Notify claimant.
        $business = $business_service->get_business( (int) $claim['business_id'] );
        wp_mail(
            $claim['email'],
            /* translators: %s: business name */
            sprintf( __( 'Your claim for "%s" has been approved!', 'directories-builder-pro' ), $business['name'] ?? '' ),
            /* translators: %s: business name */
            sprintf( __( 'Congratulations! Your claim for "%s" has been approved. You can now manage your business listing.', 'directories-builder-pro' ), $business['name'] ?? '' )
        );
        /**
         * Fires after a business claim is approved.
         */
        do_action( 'dbp/business/claimed', (int) $claim['business_id'], (int) $claim['user_id'] );
        return $this->success( [ 'message' => __( 'Claim approved.', 'directories-builder-pro' ) ] );
    }
    public function reject_claim( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id     = (int) $request->get_param( 'id' );
        $reason = $request->get_param( 'reason' ) ?? '';
        
        $repository = new \DirectoriesBuilderPro\Modules\Claims\Repositories\Claim_Repository();
        
        $claim = $repository->find_by_id( $id );
        
        if ( ! $claim ) {
            return $this->error( __( 'Claim not found.', 'directories-builder-pro' ), 404 );
        }
        
        $repository->reject( $id, $reason );
        // Notify claimant.
        $business_service = new Business_Service();
        $business = $business_service->get_business( (int) $claim['business_id'] );
        $message = sprintf(
            /* translators: %s: business name */
            __( 'Your claim for "%s" has been reviewed and was not approved.', 'directories-builder-pro' ),
            $business['name'] ?? ''
        );
        if ( $reason ) {
            /* translators: %s: rejection reason */
            $message .= "\n\n" . sprintf( __( 'Reason: %s', 'directories-builder-pro' ), $reason );
        }
        wp_mail(
            $claim['email'],
            /* translators: %s: business name */
            sprintf( __( 'Update on your claim for "%s"', 'directories-builder-pro' ), $business['name'] ?? '' ),
            $message
        );
        return $this->success( [ 'message' => __( 'Claim rejected.', 'directories-builder-pro' ) ] );
    }
    public function check_logged_in(): bool|WP_Error {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'Login required.', 'directories-builder-pro' ), [ 'status' => 401 ] );
        }
        return true;
    }
    public function check_admin(): bool|WP_Error {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'Admin access required.', 'directories-builder-pro' ), [ 'status' => 403 ] );
        }
        return true;
    }
}