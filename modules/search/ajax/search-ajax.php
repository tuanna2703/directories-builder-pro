<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Search\Ajax;
use DirectoriesBuilderPro\Services\Search_Service;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Search_Ajax {
    public function handle_search(): void {
        $service = new Search_Service();
        $results = $service->search( [
            'q'         => sanitize_text_field( wp_unslash( $_GET['q'] ?? $_POST['q'] ?? '' ) ),
            'lat'       => isset( $_GET['lat'] ) ? floatval( $_GET['lat'] ) : ( isset( $_POST['lat'] ) ? floatval( $_POST['lat'] ) : null ),
            'lng'       => isset( $_GET['lng'] ) ? floatval( $_GET['lng'] ) : ( isset( $_POST['lng'] ) ? floatval( $_POST['lng'] ) : null ),
            'radius_km' => floatval( $_GET['radius_km'] ?? $_POST['radius_km'] ?? 10 ),
            'category'  => sanitize_text_field( wp_unslash( $_GET['category'] ?? $_POST['category'] ?? '' ) ),
            'min_rating' => isset( $_GET['min_rating'] ) ? floatval( $_GET['min_rating'] ) : null,
            'price'     => isset( $_GET['price'] ) ? absint( $_GET['price'] ) : null,
            'open_now'  => ! empty( $_GET['open_now'] ?? $_POST['open_now'] ?? false ),
            'orderby'   => sanitize_text_field( wp_unslash( $_GET['orderby'] ?? $_POST['orderby'] ?? 'relevance' ) ),
            'page'      => absint( $_GET['page'] ?? $_POST['page'] ?? 1 ),
            'per_page'  => absint( $_GET['per_page'] ?? $_POST['per_page'] ?? 12 ),
        ] );
        // Render as HTML partials for non-JS fallback.
        ob_start();
        foreach ( $results['items'] as $business ) {
            include DBP_PATH . 'public/partials/business-card.php';
        }
        $html = ob_get_clean();
        wp_send_json_success( [
            'html'  => $html,
            'items' => $results['items'],
            'total' => $results['total'],
            'pages' => $results['pages'],
        ] );
    }
    public function handle_autocomplete(): void {
        $query = sanitize_text_field( wp_unslash( $_GET['q'] ?? $_POST['q'] ?? '' ) );
        if ( mb_strlen( $query ) < 2 ) {
            wp_send_json_success( [ 'suggestions' => [] ] );
        }
        $service     = new Search_Service();
        $suggestions = $service->autocomplete( $query );
        wp_send_json_success( [ 'suggestions' => $suggestions ] );
    }
}