<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Search Results
 *
 * Results grid with filter chips, sort, list/map toggle, pagination, and loading skeleton.
 *
 * @slug     search/results
 * @version  1.0.0
 *
 * @args optional: businesses (array) — array of business data
 *                 total (int) — total result count
 *                 current_page (int, default 1)
 *                 search_args (array) — current search parameters
 *
 * @package DirectoriesBuilderPro\Templates\Search
 */
$businesses   = $args['businesses'] ?? [];
$total        = (int) ( $args['total'] ?? 0 );
$current_page = (int) ( $args['current_page'] ?? 1 );

$q        = sanitize_text_field( $_GET['q'] ?? '' );
$location = sanitize_text_field( $_GET['location'] ?? '' );
?>
<div class="dbp-search-results" id="dbp-search-results">
    <!-- Results Summary -->
    <div class="dbp-search-results__summary" id="dbp-results-summary">
        <?php if ( $q || $location ) : ?>
            <p>
                <?php
                echo esc_html( sprintf(
                    /* translators: %1$s: search query, %2$s: location */
                    __( 'Results for "%1$s" near "%2$s"', 'directories-builder-pro' ),
                    $q ?: __( 'All', 'directories-builder-pro' ),
                    $location ?: __( 'Anywhere', 'directories-builder-pro' )
                ) );
                ?>
                — <span id="dbp-result-count"><?php echo esc_html( (string) $total ); ?></span> <?php esc_html_e( 'results', 'directories-builder-pro' ); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Filter Chips -->
    <div class="dbp-filter-chips" id="dbp-filter-chips" role="group">
        <div class="dbp-filter-chips__scroll">
            <button type="button" class="dbp-chip" data-filter="open_now" data-value="1">
                <?php esc_html_e( 'Open Now', 'directories-builder-pro' ); ?>
            </button>
            <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                <button type="button" class="dbp-chip" data-filter="price" data-value="<?php echo esc_attr( (string) $i ); ?>">
                    <?php echo esc_html( dbp_get_price_label( $i ) ); ?>
                </button>
            <?php endfor; ?>
            <button type="button" class="dbp-chip" data-filter="min_rating" data-value="4">
                <?php esc_html_e( '4★+', 'directories-builder-pro' ); ?>
            </button>
            <button type="button" class="dbp-chip" data-filter="radius_km" data-value="5">
                <?php esc_html_e( '5km', 'directories-builder-pro' ); ?>
            </button>
            <button type="button" class="dbp-chip" data-filter="radius_km" data-value="10">
                <?php esc_html_e( '10km', 'directories-builder-pro' ); ?>
            </button>
            <button type="button" class="dbp-chip" data-filter="radius_km" data-value="25">
                <?php esc_html_e( '25km', 'directories-builder-pro' ); ?>
            </button>
        </div>
        <button type="button" class="dbp-chip dbp-chip--clear" id="dbp-clear-filters" style="display:none;">
            <?php esc_html_e( 'Clear All', 'directories-builder-pro' ); ?>
        </button>
    </div>

    <!-- Sort + View Toggle -->
    <div class="dbp-search-results__controls">
        <div class="dbp-search-results__sort">
            <label for="dbp-sort-select" class="screen-reader-text"><?php esc_html_e( 'Sort by', 'directories-builder-pro' ); ?></label>
            <select id="dbp-sort-select" class="dbp-select">
                <option value="relevance"><?php esc_html_e( 'Relevance', 'directories-builder-pro' ); ?></option>
                <option value="distance"><?php esc_html_e( 'Distance', 'directories-builder-pro' ); ?></option>
                <option value="highest_rated"><?php esc_html_e( 'Highest Rated', 'directories-builder-pro' ); ?></option>
                <option value="most_reviewed"><?php esc_html_e( 'Most Reviewed', 'directories-builder-pro' ); ?></option>
                <option value="newest"><?php esc_html_e( 'Newest', 'directories-builder-pro' ); ?></option>
            </select>
        </div>
        <div class="dbp-search-results__view-toggle">
            <button type="button" class="dbp-view-btn dbp-view-btn--active" data-view="list"
                    aria-label="<?php esc_attr_e( 'List view', 'directories-builder-pro' ); ?>">
                <svg viewBox="0 0 24 24" width="20" height="20"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z" fill="currentColor"/></svg>
            </button>
            <button type="button" class="dbp-view-btn" data-view="map"
                    aria-label="<?php esc_attr_e( 'Map view', 'directories-builder-pro' ); ?>">
                <svg viewBox="0 0 24 24" width="20" height="20"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z" fill="currentColor"/></svg>
            </button>
        </div>
    </div>

    <!-- Split View Container -->
    <div class="dbp-split-view" id="dbp-split-view">
        <!-- List Pane -->
        <div class="dbp-split-view__list" id="dbp-results-list">
            <!-- Loading Skeleton -->
            <div class="dbp-skeleton-grid" id="dbp-results-skeleton">
                <?php dbp_template( 'partials/loading-skeleton', [ 'count' => 6, 'type' => 'card' ] ); ?>
            </div>
            <!-- Results Grid -->
            <div class="dbp-business-grid" id="dbp-business-grid" style="display:none;">
                <?php if ( ! empty( $businesses ) ) :
                    foreach ( $businesses as $biz ) {
                        dbp_template( 'business/card', [ 'business' => $biz ] );
                    }
                endif; ?>
            </div>
            <!-- Empty State -->
            <div class="dbp-search-results__empty" id="dbp-results-empty" style="display:none;">
                <?php dbp_template( 'partials/empty-state', [
                    'title'   => __( 'No results found', 'directories-builder-pro' ),
                    'message' => __( 'Try adjusting your search filters or broadening your location.', 'directories-builder-pro' ),
                ] ); ?>
            </div>
            <!-- Infinite Scroll Sentinel -->
            <div class="dbp-scroll-sentinel" id="dbp-scroll-sentinel"></div>
        </div>
        <!-- Map Pane -->
        <div class="dbp-split-view__map" id="dbp-map-pane">
            <div class="dbp-map" id="dbp-map" data-geojson=""></div>
        </div>
    </div>

    <!-- Pagination -->
    <div id="dbp-pagination-container" style="display:none;">
        <?php
        $per_page    = (int) get_option( 'dbp_results_per_page', 12 );
        $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
        dbp_template( 'partials/pagination', [
            'total_pages'  => $total_pages,
            'current_page' => $current_page,
        ] );
        ?>
    </div>
</div>
