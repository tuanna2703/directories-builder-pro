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
        \DirectoriesBuilderPro\Plugin::instance()->get_module_manager()->register_controller( $controller );

        $ajax = new Claim_Ajax();
        $ajax_manager = \DirectoriesBuilderPro\Plugin::instance()->get_ajax_manager();

        $ajax_manager->register( 'dbp_submit_claim', [ $ajax, 'handle_submit' ] );
    }
}
