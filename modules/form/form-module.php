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
        \DirectoriesBuilderPro\Plugin::instance()->get_module_manager()->register_controller( $controller );

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
        /**
         * Action: allow third-party code to register custom forms.
         *
         * @param Form_Manager $form_manager The form manager instance.
         */
        do_action( 'dbp/form/init', $form_manager );
    }
}
