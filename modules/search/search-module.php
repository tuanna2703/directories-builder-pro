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
        \DirectoriesBuilderPro\Plugin::instance()->get_module_manager()->register_controller( $controller );

        $ajax = new Search_Ajax();
        $ajax_manager = \DirectoriesBuilderPro\Plugin::instance()->get_ajax_manager();

        $ajax_manager->register( 'dbp_search', [ $ajax, 'handle_search' ], true );
        $ajax_manager->register( 'dbp_autocomplete', [ $ajax, 'handle_autocomplete' ], true );

    }
}
