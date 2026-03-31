<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Maps;

use DirectoriesBuilderPro\Core\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Maps_Module extends Module_Base {

    public function get_name(): string {
        return 'maps';
    }

    protected function init(): void {
        // Register maps API key setting.
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings(): void {
        register_setting( 'dbp_settings', 'dbp_google_maps_key', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ] );
    }
}
