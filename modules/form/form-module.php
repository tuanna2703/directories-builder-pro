<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form;
use DirectoriesBuilderPro\Core\Base\Module_Base;
use DirectoriesBuilderPro\Core\Fields\Fields_Manager;
use DirectoriesBuilderPro\Core\Managers\Form_Manager;
use DirectoriesBuilderPro\Modules\Form\Controllers\Form_Controller;
use DirectoriesBuilderPro\Modules\Form\Ajax\Form_Ajax;
use DirectoriesBuilderPro\Modules\Form\Forms\Plugin_Settings_Form;
use DirectoriesBuilderPro\Modules\Form\Forms\Business_Settings_Form;
use DirectoriesBuilderPro\Modules\Form\Forms\User_Profile_Form;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Form_Module class.
 *
 * Entry point for the Form Engine module. Registers field types, form
 * definitions, REST routes, AJAX handlers, and admin assets.
 *
 * @package DirectoriesBuilderPro\Modules\Form
 */
class Form_Module extends Module_Base {
    public function get_name(): string {
        return 'form';
    }
    protected function init(): void {
        // Register built-in field types.
        Fields_Manager::instance()->register_defaults();

        // Register REST routes.
        $controller = new Form_Controller();
        add_action( 'rest_api_init', [ $controller, 'register_routes' ] );

        // Register AJAX handler.
        $ajax = new Form_Ajax();
        $ajax_manager = \DirectoriesBuilderPro\Plugin::instance()->get_ajax_manager();
        $ajax_manager->register( 'dbp_save_form', [ $ajax, 'handle_save' ] );

        // Register built-in forms.
        $form_manager = Form_Manager::get_instance();
        $form_manager->register( new Plugin_Settings_Form() );
        $form_manager->register( new Business_Settings_Form() );
        $form_manager->register( new User_Profile_Form() );

        /**
         * Action: allow third-party code to register custom forms.
         *
         * @param Form_Manager $form_manager The form manager instance.
         */
        do_action( 'dbp/form/init', $form_manager );

        // Enqueue form engine assets on admin pages.
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }
    /**
     * Enqueue form engine CSS/JS on relevant admin pages.
     *
     * @param string $hook_suffix The current admin page hook.
     * @return void
     */
    public function enqueue_admin_assets( string $hook_suffix ): void {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        // Load on DBP plugin admin pages and dbp_business post edit screens.
        $is_dbp_page = (
            str_contains( $hook_suffix, 'dbp-' ) ||
            ( $screen->post_type === 'dbp_business' && in_array( $screen->base, [ 'post', 'post-new' ], true ) )
        );

        // Also load on the user profile page.
        $is_user_page = in_array( $screen->base, [ 'profile', 'user-edit' ], true );

        if ( ! $is_dbp_page && ! $is_user_page ) {
            return;
        }

        // CSS.
        wp_enqueue_style(
            'dbp-form-engine',
            DBP_URL . 'modules/form/assets/form-engine.css',
            [],
            DBP_VERSION
        );

        // JS — depends on jQuery, wp-color-picker, and media.
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script(
            'dbp-form-engine',
            DBP_URL . 'modules/form/assets/form-engine.js',
            [ 'jquery', 'wp-color-picker' ],
            DBP_VERSION,
            true
        );

        // Localize form data.
        wp_localize_script( 'dbp-form-engine', 'dbpFormData', [
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'dbp_form_nonce' ),
            'restBase'  => rest_url( 'directories-builder-pro/v1/' ),
            'restNonce' => wp_create_nonce( 'wp_rest' ),
            'autosave'  => false,
            'i18n'      => [
                'selectImage'      => __( 'Select Image', 'directories-builder-pro' ),
                'useImage'         => __( 'Use Image', 'directories-builder-pro' ),
                'fieldRequired'    => __( 'This field is required.', 'directories-builder-pro' ),
                'invalidEmail'     => __( 'Invalid email address.', 'directories-builder-pro' ),
                'validationFailed' => __( 'Please fix the errors above.', 'directories-builder-pro' ),
                'saved'            => __( 'Settings saved successfully.', 'directories-builder-pro' ),
                'error'            => __( 'An error occurred. Please try again.', 'directories-builder-pro' ),
            ],
        ] );
    }
}
