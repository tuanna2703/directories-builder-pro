<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Maps\Controllers;

use DirectoriesBuilderPro\Core\Base\Controller_Base;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Map_Controller class.
 *
 * Exposes endpoints for Map configurations and GeoJSON cluster data.
 *
 * @package DirectoriesBuilderPro\Modules\Maps\Controllers
 */
class Map_Controller extends Controller_Base {

    /**
     * Get the REST base route.
     *
     * @return string
     */
    public function get_rest_base(): string {
        return 'map';
    }

    /**
     * Register map-specific routes.
     *
     * @return void
     */
    public function register_routes(): void {
        register_rest_route(
            $this->get_namespace(),
            '/' . $this->get_rest_base() . '/config',
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_config' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->get_namespace(),
            '/' . $this->get_rest_base() . '/geojson',
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_geojson' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Get maps configuration.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_config( WP_REST_Request $request ): WP_REST_Response {
        $config = [
            'key'     => get_option( 'dbp_google_maps_key', '' ),
            'default_lat' => (float) get_option( 'dbp_default_lat', 0 ),
            'default_lng' => (float) get_option( 'dbp_default_lng', 0 ),
            'default_zoom' => (int) get_option( 'dbp_default_zoom', 12 ),
        ];
        
        return $this->success( $config );
    }

    /**
     * Get GeoJSON data for map plotting.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_geojson( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $repo = new \DirectoriesBuilderPro\Repositories\Business_Repository();
        
        // Find all active businesses
        $businesses = $repo->find( [ 'status' => 'active' ], 1000 );
        
        $features = [];
        foreach ( $businesses as $b ) {
            if ( ! empty( $b['lat'] ) && ! empty( $b['lng'] ) ) {
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [ (float) $b['lng'], (float) $b['lat'] ]
                    ],
                    'properties' => [
                        'id' => $b['id'],
                        'title' => $b['name'],
                        'url' => get_permalink( (int) $b['wp_post_id'] )
                    ]
                ];
            }
        }
        
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
        
        return $this->success( $geojson );
    }
}
