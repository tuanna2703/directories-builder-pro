<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin View: Business Edit Meta Box
 *
 * Registers a single "Business Settings" meta box that renders
 * the business form via the Form Engine.
 *
 * Replaces the previous multiple meta boxes (Location, Contact,
 * Details, Status) and save_post hook.
 *
 * @package DirectoriesBuilderPro\Admin\Views
 */

// Register a single meta box for business settings.
add_action( 'add_meta_boxes', static function (): void {
    add_meta_box(
        'dbp-business-settings',
        __( 'Business Settings', 'directories-builder-pro' ),
        'dbp_render_business_form_meta_box',
        'dbp_business',
        'normal',
        'high'
    );
} );

/**
 * Render the business settings form meta box.
 *
 * @param \WP_Post $post The current post object.
 * @return void
 */
function dbp_render_business_form_meta_box( \WP_Post $post ): void {
    $form = \DirectoriesBuilderPro\Core\Managers\Form_Manager::get_instance()
            ->get( 'business_settings' );

    $form_html = '';
    
    if ( ! $form ) {
        $form_html = '<p>' . esc_html__( 'Business settings form could not be loaded.', 'directories-builder-pro' ) . '</p>';
    } else {
        ob_start();
        $form->render_form( $post->ID );
        $form_html = ob_get_clean() ?: '';
    }

    echo \DirectoriesBuilderPro\Modules\Template\Template_Module::render( 'admin/business-edit', [
        'form_html' => $form_html,
        'post_id'   => $post->ID,
    ] );
}
