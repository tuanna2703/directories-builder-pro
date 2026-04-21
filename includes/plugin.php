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
     * Template manager.
     *
     * @var \DirectoriesBuilderPro\Core\Managers\Template_Manager
     */
    private \DirectoriesBuilderPro\Core\Managers\Template_Manager $template_manager;

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
        $this->template_manager = new \DirectoriesBuilderPro\Core\Managers\Template_Manager();
        
        $this->business_post_type = new Business_Post_Type();
        // Register hooks.
        add_action( 'init', [ $this, 'on_init' ] );
        add_action( 'wp_enqueue_scripts', [ $this->asset_manager, 'enqueue_frontend' ] );
        add_action( 'admin_enqueue_scripts', [ $this->asset_manager, 'enqueue_admin' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
        // Note: template_include filter is now handled by Template_Module.
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
            \DirectoriesBuilderPro\Modules\Template\Template_Module::class,
            \DirectoriesBuilderPro\Modules\Form\Form_Module::class,
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
        // Submenu: User Profile.
        add_submenu_page(
            'dbp-dashboard',
            __( 'Profile Settings', 'directories-builder-pro' ),
            __( 'My Profile', 'directories-builder-pro' ),
            'read',
            'dbp-user-profile',
            [ $this, 'render_user_profile_page' ]
        );
    }
    /**
     * Render the dashboard admin page.
     *
     * @return void
     */
    public function render_dashboard_page(): void {
        global $wpdb;
        $businesses_table = $wpdb->prefix . 'dbp_businesses';
        $reviews_table    = $wpdb->prefix . 'dbp_reviews';
        $claims_table     = $wpdb->prefix . 'dbp_claims';

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $stats = [
            'total_businesses'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$businesses_table} WHERE status = 'active'" ),
            'reviews_this_week' => (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$reviews_table} WHERE status = 'approved' AND created_at >= %s",
                gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
            ) ),
            'pending_claims'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$claims_table} WHERE status = 'pending'" ),
            'pending_reviews'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reviews_table} WHERE status = 'pending'" ),
        ];
        $recent_activity = $wpdb->get_results(
            "SELECT r.*, b.name as business_name
             FROM {$reviews_table} r
             LEFT JOIN {$businesses_table} b ON r.business_id = b.id
             ORDER BY r.created_at DESC
             LIMIT 10",
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        dbp_template( 'admin/dashboard', [
            'stats'           => $stats,
            'recent_activity' => $recent_activity ?: [],
        ] );
    }
    /**
     * Render the review moderation page.
     *
     * @return void
     */
    public function render_moderation_page(): void {
        // Load the list table class.
        require_once DBP_PATH . 'admin/views/review-moderation.php';
        $table = new \DBP_Review_List_Table();
        $table->prepare_items();
        ob_start();
        $table->display();
        $table_html = ob_get_clean();

        dbp_template( 'admin/moderation', [
            'table_html'     => $table_html,
            'current_status' => sanitize_text_field( $_GET['status'] ?? '' ),
        ] );
    }
    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render_settings_page(): void {
        $form = \DirectoriesBuilderPro\Core\Managers\Form_Manager::get_instance()
                ->get( 'plugin_settings' );
        $form_html = '';
        if ( $form ) {
            ob_start();
            $form->render_form();
            $form_html = ob_get_clean();
        }

        dbp_template( 'admin/settings', [
            'form_html' => $form_html,
        ] );
    }
    /**
     * Render the user profile settings page.
     *
     * @return void
     */
    public function render_user_profile_page(): void {
        $user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id();
        if ( $user_id !== get_current_user_id() && ! current_user_can( 'edit_user', $user_id ) ) {
            wp_die( esc_html__( 'You do not have permission to edit this user.', 'directories-builder-pro' ) );
        }
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            wp_die( esc_html__( 'User not found.', 'directories-builder-pro' ) );
        }

        $form = \DirectoriesBuilderPro\Core\Managers\Form_Manager::get_instance()
                ->get( 'user_profile' );
        $form_html = '';
        if ( $form ) {
            ob_start();
            $form->render_form( $user_id );
            $form_html = ob_get_clean();
        }

        dbp_template( 'admin/user-profile', [
            'form_html'    => $form_html,
            'user_id'      => $user_id,
            'display_name' => $user->display_name,
        ] );
    }
    /**
     * Override templates for the dbp_business CPT.
     *
     * Hooked to template_include filter.
     *
     * @param string $template Default template path.
     * @return string
     */
    /**
     * @deprecated Handled by Template_Module::override_cpt_templates()
     */
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
     * Get the Template manager.
     *
     * @return \DirectoriesBuilderPro\Core\Managers\Template_Manager
     */
    public function get_template_manager(): \DirectoriesBuilderPro\Core\Managers\Template_Manager {
        return $this->template_manager;
    }

    /**
     * Prevent cloning.
     */
    private function __clone() {}
}