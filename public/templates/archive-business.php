<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Business Archive / Search Page
 *
 * @package DirectoriesBuilderPro\Public\Templates
 */
get_header();
// Get initial results for server-side rendering.
$search_service = new \DirectoriesBuilderPro\Services\Search_Service();
$initial_results = $search_service->search( [
    'q'        => sanitize_text_field( $_GET['q'] ?? '' ),
    'page'     => 1,
    'per_page' => (int) get_option( 'dbp_results_per_page', 12 ),
] );
// Build GeoJSON for map.
$geojson = \DirectoriesBuilderPro\Modules\Maps\Services\Map_Service::build_geojson( $initial_results['items'] );
?>
<div class="dbp-archive-business dbp-container">
    <header class="dbp-archive-business__header">
        <h1 class="dbp-archive-business__title"><?php esc_html_e( 'Business Directory', 'directories-builder-pro' ); ?></h1>
    </header>
    <?php
    // Search bar.
    include DBP_PATH . 'modules/search/templates/search-bar.php';
    // Search results with map.
    include DBP_PATH . 'modules/search/templates/search-results.php';
    ?>
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