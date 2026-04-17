<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Pagination
 *
 * Renders pagination links with previous/next and up to 7 page numbers with ellipsis.
 *
 * @slug     partials/pagination
 * @version  1.0.0
 *
 * @args required: total_pages (int) — total number of pages
 *                 current_page (int) — current active page (1-indexed)
 * @args optional: base_url (string) — base URL for page links
 *                 query_var (string, default 'paged') — query parameter name
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$total_pages  = (int) ( $args['total_pages'] ?? 1 );
$current_page = (int) ( $args['current_page'] ?? 1 );
$base_url     = $args['base_url'] ?? '';
$query_var    = $args['query_var'] ?? 'paged';

// Don't render if only one page.
if ( $total_pages <= 1 ) {
    return;
}

// Build page URL helper.
$build_url = static function ( int $page ) use ( $base_url, $query_var ): string {
    if ( $base_url === '' ) {
        return add_query_arg( $query_var, $page );
    }
    return add_query_arg( $query_var, $page, $base_url );
};

// Calculate page range (show up to 7 pages with ellipsis).
$range       = 2; // Pages to show on each side of current.
$show_start  = max( 1, $current_page - $range );
$show_end    = min( $total_pages, $current_page + $range );

// Ensure at least 5 pages shown when possible.
if ( $show_end - $show_start < 4 && $total_pages >= 5 ) {
    if ( $show_start === 1 ) {
        $show_end = min( 5, $total_pages );
    } elseif ( $show_end === $total_pages ) {
        $show_start = max( 1, $total_pages - 4 );
    }
}
?>
<nav class="dbp-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'directories-builder-pro' ); ?>">
    <?php // Previous button. ?>
    <?php if ( $current_page > 1 ) : ?>
        <a href="<?php echo esc_url( $build_url( $current_page - 1 ) ); ?>"
           class="dbp-pagination__link dbp-pagination__link--prev"
           aria-label="<?php esc_attr_e( 'Previous page', 'directories-builder-pro' ); ?>">
            &laquo; <?php esc_html_e( 'Previous', 'directories-builder-pro' ); ?>
        </a>
    <?php else : ?>
        <span class="dbp-pagination__link dbp-pagination__link--prev dbp-pagination__link--disabled" aria-disabled="true">
            &laquo; <?php esc_html_e( 'Previous', 'directories-builder-pro' ); ?>
        </span>
    <?php endif; ?>

    <div class="dbp-pagination__pages">
        <?php // First page + ellipsis. ?>
        <?php if ( $show_start > 1 ) : ?>
            <a href="<?php echo esc_url( $build_url( 1 ) ); ?>" class="dbp-pagination__link">1</a>
            <?php if ( $show_start > 2 ) : ?>
                <span class="dbp-pagination__ellipsis">&hellip;</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php // Page numbers. ?>
        <?php for ( $i = $show_start; $i <= $show_end; $i++ ) : ?>
            <?php if ( $i === $current_page ) : ?>
                <span class="dbp-pagination__current" aria-current="page"><?php echo esc_html( (string) $i ); ?></span>
            <?php else : ?>
                <a href="<?php echo esc_url( $build_url( $i ) ); ?>" class="dbp-pagination__link"><?php echo esc_html( (string) $i ); ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php // Last page + ellipsis. ?>
        <?php if ( $show_end < $total_pages ) : ?>
            <?php if ( $show_end < $total_pages - 1 ) : ?>
                <span class="dbp-pagination__ellipsis">&hellip;</span>
            <?php endif; ?>
            <a href="<?php echo esc_url( $build_url( $total_pages ) ); ?>" class="dbp-pagination__link"><?php echo esc_html( (string) $total_pages ); ?></a>
        <?php endif; ?>
    </div>

    <?php // Next button. ?>
    <?php if ( $current_page < $total_pages ) : ?>
        <a href="<?php echo esc_url( $build_url( $current_page + 1 ) ); ?>"
           class="dbp-pagination__link dbp-pagination__link--next"
           aria-label="<?php esc_attr_e( 'Next page', 'directories-builder-pro' ); ?>">
            <?php esc_html_e( 'Next', 'directories-builder-pro' ); ?> &raquo;
        </a>
    <?php else : ?>
        <span class="dbp-pagination__link dbp-pagination__link--next dbp-pagination__link--disabled" aria-disabled="true">
            <?php esc_html_e( 'Next', 'directories-builder-pro' ); ?> &raquo;
        </span>
    <?php endif; ?>
</nav>
