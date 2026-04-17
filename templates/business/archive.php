<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Business Archive / Search Page
 *
 * Search page with search bar, results grid, map, and initial server-side results.
 * This is a full-page template loaded via template_include filter.
 *
 * @slug     business/archive
 * @version  1.0.0
 *
 * @args optional: initial_results (array) — pre-loaded search results
 *                 search_args (array) — search parameters
 *
 * @package DirectoriesBuilderPro\Templates\Business
 */

// If loaded as a CPT archive template override, prepare $args from query.
if ( empty( $args ) ) {
    $search_service  = new \DirectoriesBuilderPro\Services\Search_Service();
    $initial_results = $search_service->search( [
        'q'        => sanitize_text_field( $_GET['q'] ?? '' ),
        'page'     => 1,
        'per_page' => (int) get_option( 'dbp_results_per_page', 12 ),
    ] );
    $geojson = \DirectoriesBuilderPro\Modules\Maps\Services\Map_Service::build_geojson( $initial_results['items'] );
    $args = [
        'initial_results' => $initial_results,
        'search_args'     => [],
        '_geojson'        => $geojson,
    ];
}

$initial_results = $args['initial_results'] ?? [ 'items' => [], 'total' => 0 ];
$geojson         = $args['_geojson'] ?? \DirectoriesBuilderPro\Modules\Maps\Services\Map_Service::build_geojson( $initial_results['items'] ?? [] );

get_header();
?>
<div class="dbp-archive-business dbp-container">
    <header class="dbp-archive-business__header">
        <h1 class="dbp-archive-business__title"><?php esc_html_e( 'Business Directory', 'directories-builder-pro' ); ?></h1>
    </header>

    <?php // Search bar. ?>
    <?php dbp_template( 'search/bar', [
        'default_query'    => sanitize_text_field( $_GET['q'] ?? '' ),
        'default_location' => sanitize_text_field( $_GET['location'] ?? '' ),
    ] ); ?>

    <?php // Search results with map. ?>
    <?php dbp_template( 'search/results', [
        'businesses'   => $initial_results['items'] ?? [],
        'total'        => $initial_results['total'] ?? 0,
        'current_page' => 1,
    ] ); ?>
</div>

<!-- Initialize map with GeoJSON data -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var mapEl = document.getElementById('dbp-map');
        if (mapEl) {
            mapEl.setAttribute('data-geojson', <?php echo wp_json_encode( wp_json_encode( $geojson ) ); ?>);
        }
    });
</script>
<?php get_footer(); ?>
