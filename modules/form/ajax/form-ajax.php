<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Ajax;
use DirectoriesBuilderPro\Core\Managers\Form_Manager;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Form_Ajax class.
 *
 * AJAX fallback handler for form save operations.
 *
 * @package DirectoriesBuilderPro\Modules\Form\Ajax
 */
class Form_Ajax {
    /**
     * Handle AJAX form save.
     *
     * @return void
     */
    public function handle_save(): void {
        check_ajax_referer( 'dbp_form_nonce', 'nonce' );
        $name      = sanitize_key( wp_unslash( $_POST['form_name'] ?? '' ) );
        $object_id = isset( $_POST['object_id'] ) ? absint( $_POST['object_id'] ) : null;
        $values    = isset( $_POST['values'] ) ? (array) $_POST['values'] : [];
        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => __( 'Form name is required.', 'directories-builder-pro' ) ] );
            return;
        }
        $form = Form_Manager::get_instance()->get( $name );
        if ( $form === null ) {
            wp_send_json_error( [ 'message' => __( 'Form not found.', 'directories-builder-pro' ) ] );
            return;
        }
        // Permission checks.
        if ( ! $this->check_permission( $name, $object_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'directories-builder-pro' ) ] );
            return;
        }
        // Validate object for post_meta forms.
        if ( $form->get_storage_type() === 'post_meta' && $object_id !== null ) {
            if ( get_post_type( $object_id ) !== 'dbp_business' ) {
                wp_send_json_error( [ 'message' => __( 'Invalid business post.', 'directories-builder-pro' ) ] );
                return;
            }
        }
        // Validate object for user_meta forms.
        if ( $form->get_storage_type() === 'user_meta' && $object_id !== null ) {
            if ( get_userdata( $object_id ) === false ) {
                wp_send_json_error( [ 'message' => __( 'Invalid user.', 'directories-builder-pro' ) ] );
                return;
            }
        }
        $result = $form->save( $values, $object_id );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_messages() ] );
            return;
        }
        wp_send_json_success( [ 'message' => __( 'Saved.', 'directories-builder-pro' ) ] );
    }
    /**
     * Check permissions for AJAX save.
     *
     * @param string   $name      Form name.
     * @param int|null $object_id Object ID.
     * @return bool
     */
    private function check_permission( string $name, ?int $object_id ): bool {
        return match ( $name ) {
            'plugin_settings' => current_user_can( 'manage_options' ),
            'business_settings' => current_user_can( 'manage_options' ) || (
                $object_id !== null
                && current_user_can( 'edit_post', $object_id )
            ),
            'user_profile' => (
                $object_id !== null
                && ( $object_id === get_current_user_id() || current_user_can( 'edit_user', $object_id ) )
            ),
            default => current_user_can( 'manage_options' ),
        };
    }
}
