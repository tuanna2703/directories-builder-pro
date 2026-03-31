<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Reviews;

use DirectoriesBuilderPro\Core\Base\Module_Base;
use DirectoriesBuilderPro\Modules\Reviews\Controllers\Review_Controller;
use DirectoriesBuilderPro\Modules\Reviews\Ajax\Review_Ajax;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reviews_Module class.
 *
 * Entry point for the reviews feature module.
 * Registers REST controllers and AJAX handlers.
 *
 * @package DirectoriesBuilderPro\Modules\Reviews
 */
class Reviews_Module extends Module_Base {

    /**
     * Get the module name.
     *
     * @return string
     */
    public function get_name(): string {
        return 'reviews';
    }

    /**
     * Initialize the module.
     *
     * @return void
     */
    protected function init(): void {
        // Register REST routes.
        $controller = new Review_Controller();
        add_action( 'rest_api_init', [ $controller, 'register_routes' ] );

        // Register AJAX handlers.
        $ajax = new Review_Ajax();
        $ajax_manager = \DirectoriesBuilderPro\Plugin::instance()->get_ajax_manager();

        $ajax_manager->register( 'dbp_submit_review', [ $ajax, 'handle_submit' ] );
        $ajax_manager->register( 'dbp_vote_review', [ $ajax, 'handle_vote' ] );
        $ajax_manager->register( 'dbp_flag_review', [ $ajax, 'handle_flag' ] );
    }
}
