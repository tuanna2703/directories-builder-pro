<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Search;

use DirectoriesBuilderPro\Core\Base\Module_Base;
use DirectoriesBuilderPro\Modules\Search\Controllers\Search_Controller;
use DirectoriesBuilderPro\Modules\Search\Ajax\Search_Ajax;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Search_Module extends Module_Base {

    public function get_name(): string {
        return 'search';
    }

    protected function init(): void {
        $controller = new Search_Controller();
        add_action( 'rest_api_init', [ $controller, 'register_routes' ] );

        $ajax = new Search_Ajax();
        $ajax_manager = \DirectoriesBuilderPro\Plugin::instance()->get_ajax_manager();

        $ajax_manager->register( 'dbp_search', [ $ajax, 'handle_search' ], true );
        $ajax_manager->register( 'dbp_autocomplete', [ $ajax, 'handle_autocomplete' ], true );

        // Register shortcodes.
        add_shortcode( 'dbp_search_bar', [ $this, 'render_search_bar' ] );
        add_shortcode( 'dbp_search_results', [ $this, 'render_search_results' ] );
    }

    public function render_search_bar( array $atts = [] ): string {
        ob_start();
        include DBP_PATH . 'modules/search/templates/search-bar.php';
        return ob_get_clean();
    }

    public function render_search_results( array $atts = [] ): string {
        ob_start();
        include DBP_PATH . 'modules/search/templates/search-results.php';
        return ob_get_clean();
    }
}
