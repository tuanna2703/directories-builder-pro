<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Search\Controllers;
use DirectoriesBuilderPro\Core\Base\Controller_Base;
use DirectoriesBuilderPro\Services\Search_Service;
use WP_REST_Request;
use WP_REST_Response;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Search_Controller extends Controller_Base {
    private Search_Service $service;
    public function __construct() {
        $this->service = new Search_Service();
    }
    public function register_routes(): void {
        $this->register_route( '/search', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'search' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'q'          => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'lat'        => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
                'lng'        => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
                'radius_km'  => [ 'type' => 'number', 'default' => 10, 'sanitize_callback' => 'floatval' ],
                'category'   => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'min_rating' => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
                'price'      => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
                'open_now'   => [ 'type' => 'boolean' ],
                'orderby'    => [ 'type' => 'string', 'default' => 'relevance', 'sanitize_callback' => 'sanitize_text_field' ],
                'page'       => [ 'type' => 'integer', 'default' => 1, 'sanitize_callback' => 'absint' ],
                'per_page'   => [ 'type' => 'integer', 'default' => 12, 'sanitize_callback' => 'absint' ],
            ],
        ] );
        $this->register_route( '/autocomplete', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'autocomplete' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'q' => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );
    }
    public function search( WP_REST_Request $request ): WP_REST_Response {
        $results = $this->service->search( $request->get_params() );
        return $this->success( [
            'businesses' => $results['items'],
            'total'      => $results['total'],
            'pages'      => $results['pages'],
        ] );
    }
    public function autocomplete( WP_REST_Request $request ): WP_REST_Response {
        $suggestions = $this->service->autocomplete( $request->get_param( 'q' ) );
        return $this->success( [ 'suggestions' => $suggestions ] );
    }
}