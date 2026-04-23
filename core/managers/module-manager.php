<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Managers;
use DirectoriesBuilderPro\Core\Base\Module_Base;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Module_Manager class.
 *
 * Instantiates and stores all feature modules.
 * Provides access to modules by their unique name.
 *
 * @package DirectoriesBuilderPro\Core\Managers
 */
class Module_Manager {
    /**
     * Registered module instances.
     *
     * @var array<string, Module_Base>
     */
    private array $modules = [];

    /**
     * @var \DirectoriesBuilderPro\Core\Managers\Template_Manager|null
     */
    private ?\DirectoriesBuilderPro\Core\Managers\Template_Manager $template_manager = null;

    /**
     * @var \DirectoriesBuilderPro\Core\Managers\Ajax_Manager|null
     */
    private ?\DirectoriesBuilderPro\Core\Managers\Ajax_Manager $ajax_manager = null;

    /**
     * @var \DirectoriesBuilderPro\Core\Managers\Form_Manager|null
     */
    private ?\DirectoriesBuilderPro\Core\Managers\Form_Manager $form_manager = null;
    /**
     * Registered controllers.
     *
     * @var array
     */
    private array $controllers = [];

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        add_action( 'admin_init', [ $this, 'register_admin_settings' ] );
    }

    /**
     * Register a REST controller.
     *
     * @param \DirectoriesBuilderPro\Core\Base\Controller_Base $controller
     * @return void
     */
    public function register_controller( \DirectoriesBuilderPro\Core\Base\Controller_Base $controller ): void {
        $this->controllers[] = $controller;
    }

    /**
     * Initialize REST routes.
     *
     * @return void
     */
    public function register_rest_routes(): void {
        foreach ( $this->controllers as $controller ) {
            $controller->register_routes();
        }
    }

    /**
     * Initialize admin settings by delegating to modules that need them.
     *
     * @return void
     */
    public function register_admin_settings(): void {
        foreach ( $this->modules as $module ) {
            if ( method_exists( $module, 'register_settings' ) ) {
                $module->register_settings();
            }
        }
    }

    /**
     * Register an array of module class names.
     *
     * Each class is instantiated (which triggers init()) and stored.
     *
     * @param array<class-string<Module_Base>> $classes Array of fully-qualified class names.
     * @return void
     */
    public function register_modules( array $classes ): void {
        foreach ( $classes as $class ) {
            if ( ! class_exists( $class ) ) {
                continue;
            }
            /** @var Module_Base $module */
            $module = new $class();
            if ( $module instanceof Module_Base ) {
                $this->modules[ $module->get_name() ] = $module;
            }
        }
    }
    /**
     * Get a registered module by name.
     *
     * @param string $name Module name.
     * @return Module_Base|null
     */
    public function get_module( string $name ): ?Module_Base {
        return $this->modules[ $name ] ?? null;
    }

    public function set_template_manager( \DirectoriesBuilderPro\Core\Managers\Template_Manager $tm ): void {
        $this->template_manager = $tm;
    }

    public function set_ajax_manager( \DirectoriesBuilderPro\Core\Managers\Ajax_Manager $am ): void {
        $this->ajax_manager = $am;
    }

    public function set_form_manager( \DirectoriesBuilderPro\Core\Managers\Form_Manager $fm ): void {
        $this->form_manager = $fm;
    }
    /**
     * Get all registered modules.
     *
     * @return array<string, Module_Base>
     */
    public function get_modules(): array {
        return $this->modules;
    }
}