<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Business;

use DirectoriesBuilderPro\Core\Base\Module_Base;
use DirectoriesBuilderPro\Modules\Business\Controllers\Business_Controller;
use DirectoriesBuilderPro\Modules\Business\Ajax\Business_Ajax;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Business_Module class.
 *
 * @package DirectoriesBuilderPro\Modules\Business
 */
class Business_Module extends Module_Base {

    public function get_name(): string {
        return 'business';
    }

    protected function init(): void {
        $controller = new Business_Controller();
        add_action( 'rest_api_init', [ $controller, 'register_routes' ] );

        $ajax = new Business_Ajax();
        $ajax_manager = \DirectoriesBuilderPro\Plugin::instance()->get_ajax_manager();

        $ajax_manager->register( 'dbp_get_business_hours', [ $ajax, 'handle_get_hours' ], true );
        $ajax_manager->register( 'dbp_update_business_meta', [ $ajax, 'handle_update_meta' ] );
    }
}
