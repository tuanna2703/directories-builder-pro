<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Review List
 *
 * Paginated reviews with sort controls, load more, and loading skeleton.
 *
 * @slug     reviews/list
 * @version  1.0.0
 *
 * @args required: reviews (array) — array of review data
 *                 business_id (int)
 * @args optional: total (int) — total review count
 *                 current_page (int, default 1)
 *                 orderby (string, default 'relevance')
 *
 * @package DirectoriesBuilderPro\Templates\Reviews
 */
$reviews      = $args['reviews'] ?? [];
$business_id  = (int) ( $args['business_id'] ?? 0 );
$total        = (int) ( $args['total'] ?? count( $reviews ) );
$current_page = (int) ( $args['current_page'] ?? 1 );
$current_sort = $args['orderby'] ?? sanitize_text_field( $_GET['sort'] ?? 'relevance' );

$sort_options = [
    'relevance' => __( 'Most Relevant', 'directories-builder-pro' ),
    'newest'    => __( 'Newest', 'directories-builder-pro' ),
    'highest'   => __( 'Highest Rated', 'directories-builder-pro' ),
    'lowest'    => __( 'Lowest Rated', 'directories-builder-pro' ),
];
?>
<div class="dbp-reviews" id="dbp-reviews" data-business-id="<?php echo esc_attr( (string) $business_id ); ?>">
    <div class="dbp-reviews__header">
        <h3 class="dbp-reviews__title">
            <?php
            /* translators: %d: number of reviews */
            echo esc_html( sprintf( _n( '%d Review', '%d Reviews', $total, 'directories-builder-pro' ), $total ) );
            ?>
        </h3>
        <div class="dbp-reviews__sort" role="tablist" aria-label="<?php esc_attr_e( 'Sort reviews', 'directories-builder-pro' ); ?>">
            <?php foreach ( $sort_options as $value => $label ) :
                $is_active = ( $current_sort === $value );
                ?>
                <button type="button"
                        class="dbp-reviews__sort-btn <?php echo $is_active ? 'dbp-reviews__sort-btn--active' : ''; ?>"
                        data-sort="<?php echo esc_attr( $value ); ?>"
                        role="tab"
                        aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                    <?php echo esc_html( $label ); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dbp-reviews__list" id="dbp-reviews-list">
        <?php if ( empty( $reviews ) ) :
            dbp_template( 'partials/empty-state', [
                'title'   => __( 'No reviews yet', 'directories-builder-pro' ),
                'message' => __( 'Be the first to share your experience!', 'directories-builder-pro' ),
            ] );
        else :
            foreach ( $reviews as $review ) {
                dbp_template( 'reviews/item', [ 'review' => $review ] );
            }
        endif; ?>
    </div>

    <?php if ( count( $reviews ) < $total ) : ?>
        <div class="dbp-reviews__load-more">
            <button type="button"
                    class="dbp-btn dbp-btn--outline dbp-reviews__load-more-btn"
                    id="dbp-load-more-reviews"
                    data-page="<?php echo esc_attr( (string) $current_page ); ?>"
                    data-business-id="<?php echo esc_attr( (string) $business_id ); ?>"
                    data-total="<?php echo esc_attr( (string) $total ); ?>">
                <?php esc_html_e( 'Load More Reviews', 'directories-builder-pro' ); ?>
                <span class="dbp-spinner" style="display:none;"></span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Loading skeleton (shown while fetching) -->
    <div class="dbp-reviews__skeleton" style="display:none;" id="dbp-reviews-skeleton">
        <?php dbp_template( 'partials/loading-skeleton', [ 'count' => 3, 'type' => 'list' ] ); ?>
    </div>
</div>
