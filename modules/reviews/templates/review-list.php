<?php
declare(strict_types=1);
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Template: Review List
 *
 * Displays paginated reviews for a business with sort controls and load more.
 *
 * @var int   $business_id The business ID.
 * @var array $reviews     Array of review data.
 * @var int   $total       Total review count.
 *
 * @package DirectoriesBuilderPro\Modules\Reviews\Templates
 */
// Get data from template variables or defaults.
$business_id = $business_id ?? 0;
$reviews     = $reviews ?? [];
$total       = $total ?? 0;
$current_sort = sanitize_text_field( $_GET['sort'] ?? 'relevance' );
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
            <?php
            $sort_options = [
                'relevance' => __( 'Most Relevant', 'directories-builder-pro' ),
                'newest'    => __( 'Newest', 'directories-builder-pro' ),
                'highest'   => __( 'Highest Rated', 'directories-builder-pro' ),
                'lowest'    => __( 'Lowest Rated', 'directories-builder-pro' ),
            ];
            foreach ( $sort_options as $value => $label ) :
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
        <?php if ( empty( $reviews ) ) : ?>
            <div class="dbp-reviews__empty">
                <svg viewBox="0 0 24 24" width="48" height="48" class="dbp-reviews__empty-icon">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor" opacity="0.3"/>
                    <path d="M12 6l1.45 3.5L17 10.18l-2.5 2.41.59 3.41L12 14.27 8.91 16l.59-3.41L7 10.18l3.55-.68z" fill="currentColor" opacity="0.5"/>
                </svg>
                <p class="dbp-reviews__empty-text">
                    <?php esc_html_e( 'No reviews yet. Be the first to share your experience!', 'directories-builder-pro' ); ?>
                </p>
            </div>
        <?php else : ?>
            <?php
            foreach ( $reviews as $review ) {
                include DBP_PATH . 'public/partials/review-item.php';
            }
            ?>
        <?php endif; ?>
    </div>
    <?php if ( count( $reviews ) < $total ) : ?>
        <div class="dbp-reviews__load-more">
            <button type="button"
                    class="dbp-btn dbp-btn--outline dbp-reviews__load-more-btn"
                    id="dbp-load-more-reviews"
                    data-page="1"
                    data-business-id="<?php echo esc_attr( (string) $business_id ); ?>"
                    data-total="<?php echo esc_attr( (string) $total ); ?>">
                <?php esc_html_e( 'Load More Reviews', 'directories-builder-pro' ); ?>
                <span class="dbp-spinner" style="display:none;"></span>
            </button>
        </div>
    <?php endif; ?>
    <!-- Loading skeleton (shown while fetching) -->
    <div class="dbp-reviews__skeleton" style="display:none;" id="dbp-reviews-skeleton">
        <?php for ( $i = 0; $i < 3; $i++ ) : ?>
            <div class="dbp-skeleton-item">
                <div class="dbp-skeleton-item__avatar"></div>
                <div class="dbp-skeleton-item__content">
                    <div class="dbp-skeleton-item__line dbp-skeleton-item__line--short"></div>
                    <div class="dbp-skeleton-item__line dbp-skeleton-item__line--stars"></div>
                    <div class="dbp-skeleton-item__line"></div>
                    <div class="dbp-skeleton-item__line"></div>
                    <div class="dbp-skeleton-item__line dbp-skeleton-item__line--short"></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>