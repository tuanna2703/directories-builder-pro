<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Maps;

use DirectoriesBuilderPro\Core\Base\Module_Base;
use DirectoriesBuilderPro\Modules\Maps\Controllers\Map_Controller;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Maps_Module extends Module_Base {

    public function get_name(): string {
        return 'maps';
    }

    protected function init(): void {
        $controller = new Map_Controller();
        \DirectoriesBuilderPro\Plugin::instance()->get_module_manager()->register_controller( $controller );
        
        // Module_Manager will detect register_settings() and execute it on admin_init.
    }

    public function register_settings(): void {
        register_setting( 'dbp_settings', 'dbp_google_maps_key', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ] );
    }
}
