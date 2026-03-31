<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro;
use DirectoriesBuilderPro\Core\Managers\Module_Manager;
use DirectoriesBuilderPro\Core\Managers\Asset_Manager;
use DirectoriesBuilderPro\Core\Managers\Ajax_Manager;
use DirectoriesBuilderPro\Core\Database\Migrations;
use DirectoriesBuilderPro\PostTypes\Business_Post_Type;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Plugin class — Singleton.
 *
 * The main entry point for Directories Builder Pro.
 * Wires all managers, registers modules, CPT, taxonomies,
 * admin pages, and template overrides.
 *
 * @package DirectoriesBuilderPro
 */
class Plugin {
    /**
     * Singleton instance.
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;
    /**
     * Module manager.
     *
     * @var Module_Manager
     */
    private Module_Manager $module_manager;
    /**
     * Asset manager.
     *
     * @var Asset_Manager
     */
    private Asset_Manager $asset_manager;
    /**
     * AJAX manager.
     *
     * @var Ajax_Manager
     */
    private Ajax_Manager $ajax_manager;
    /**
     * Business post type handler.
     *
     * @var Business_Post_Type
     */
    private Business_Post_Type $business_post_type;
    /**
     * Get the singleton instance.
     *
     * @return Plugin
     */
    public static function instance(): Plugin {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Private constructor — initializes managers and hooks.
     */
    private function __construct() {
        // Check for database upgrade on admin init.
        if ( is_admin() && Migrations::needs_upgrade() ) {
            Migrations::run();
        }
        // Initialize managers.
        $this->module_manager   = new Module_Manager();
        $this->asset_manager    = new Asset_Manager();
        $this->ajax_manager     = new Ajax_Manager();
        $this->business_post_type = new Business_Post_Type();
        // Register hooks.
        add_action( 'init', [ $this, 'on_init' ] );
        add_action( 'wp_enqueue_scripts', [ $this->asset_manager, 'enqueue_frontend' ] );
        add_action( 'admin_enqueue_scripts', [ $this->asset_manager, 'enqueue_admin' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
        add_filter( 'template_include', [ $this, 'override_templates' ] );
        // Load business editor meta boxes.
        if ( is_admin() ) {
            require_once DBP_PATH . 'admin/views/business-edit.php';
        }
    }
    /**
     * Fires on WordPress 'init' hook.
     *
     * Registers CPT, taxonomies, and initializes modules.
     *
     * @return void
     */
    public function on_init(): void {
        // Register Custom Post Type and taxonomies.
        $this->business_post_type->register();
        // Register modules.
        $this->register_modules();
        // Load text domain.
        load_plugin_textdomain(
            'directories-builder-pro',
            false,
            dirname( DBP_BASENAME ) . '/languages'
        );
    }
    /**
     * Register all feature modules.
     *
     * @return void
     */
    private function register_modules(): void {
        $this->module_manager->register_modules( [
            \DirectoriesBuilderPro\Modules\Reviews\Reviews_Module::class,
            \DirectoriesBuilderPro\Modules\Business\Business_Module::class,
            \DirectoriesBuilderPro\Modules\Search\Search_Module::class,
            \DirectoriesBuilderPro\Modules\Maps\Maps_Module::class,
            \DirectoriesBuilderPro\Modules\Claims\Claims_Module::class,
        ] );
    }
    /**
     * Register admin menu pages.
     *
     * Hooked to admin_menu.
     *
     * @return void
     */
    public function register_admin_pages(): void {
        // Main menu page.
        add_menu_page(
            __( 'Directories Builder Pro', 'directories-builder-pro' ),
            __( 'Directory', 'directories-builder-pro' ),
            'manage_options',
            'dbp-dashboard',
            [ $this, 'render_dashboard_page' ],
            'dashicons-location',
            26
        );
        // Submenu: Dashboard (same as parent).
        add_submenu_page(
            'dbp-dashboard',
            __( 'Dashboard', 'directories-builder-pro' ),
            __( 'Dashboard', 'directories-builder-pro' ),
            'manage_options',
            'dbp-dashboard',
            [ $this, 'render_dashboard_page' ]
        );
        // Submenu: Review Moderation.
        add_submenu_page(
            'dbp-dashboard',
            __( 'Review Moderation', 'directories-builder-pro' ),
            __( 'Moderate Reviews', 'directories-builder-pro' ),
            'manage_options',
            'dbp-review-moderation',
            [ $this, 'render_moderation_page' ]
        );
        // Submenu: Settings.
        add_submenu_page(
            'dbp-dashboard',
            __( 'Settings', 'directories-builder-pro' ),
            __( 'Settings', 'directories-builder-pro' ),
            'manage_options',
            'dbp-settings',
            [ $this, 'render_settings_page' ]
        );
    }
    /**
     * Render the dashboard admin page.
     *
     * @return void
     */
    public function render_dashboard_page(): void {
        require_once DBP_PATH . 'admin/pages/dashboard.php';
    }
    /**
     * Render the review moderation page.
     *
     * @return void
     */
    public function render_moderation_page(): void {
        require_once DBP_PATH . 'admin/views/review-moderation.php';
    }
    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render_settings_page(): void {
        require_once DBP_PATH . 'admin/pages/settings.php';
    }
    /**
     * Override templates for the dbp_business CPT.
     *
     * Hooked to template_include filter.
     *
     * @param string $template Default template path.
     * @return string
     */
    public function override_templates( string $template ): string {
        if ( is_singular( 'dbp_business' ) ) {
            $custom = DBP_PATH . 'public/templates/single-business.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }
        if ( is_post_type_archive( 'dbp_business' ) ) {
            $custom = DBP_PATH . 'public/templates/archive-business.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }
        return $template;
    }
    /**
     * Get the module manager.
     *
     * @return Module_Manager
     */
    public function get_module_manager(): Module_Manager {
        return $this->module_manager;
    }
    /**
     * Get the asset manager.
     *
     * @return Asset_Manager
     */
    public function get_asset_manager(): Asset_Manager {
        return $this->asset_manager;
    }
    /**
     * Get the AJAX manager.
     *
     * @return Ajax_Manager
     */
    public function get_ajax_manager(): Ajax_Manager {
        return $this->ajax_manager;
    }
    /**
     * Prevent cloning.
     */
    private function __clone() {}
}