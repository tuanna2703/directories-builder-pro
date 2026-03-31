<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Claims;

use DirectoriesBuilderPro\Core\Base\Module_Base;
use DirectoriesBuilderPro\Modules\Claims\Controllers\Claim_Controller;
use DirectoriesBuilderPro\Modules\Claims\Ajax\Claim_Ajax;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Claims_Module extends Module_Base {

    public function get_name(): string {
        return 'claims';
    }

    protected function init(): void {
        $controller = new Claim_Controller();
        add_action( 'rest_api_init', [ $controller, 'register_routes' ] );

        $ajax = new Claim_Ajax();
        $ajax_manager = \DirectoriesBuilderPro\Plugin::instance()->get_ajax_manager();

        $ajax_manager->register( 'dbp_submit_claim', [ $ajax, 'handle_submit' ] );
    }
}
