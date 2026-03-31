<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Business Card
 *
 * Reusable card component for search results and listings.
 *
 * @var array $business Business data array.
 *
 * @package DirectoriesBuilderPro\Public\Partials
 */
$business      = $business ?? $business_item ?? [];
$b_id          = (int) ( $business['id'] ?? 0 );
$post_id       = (int) ( $business['wp_post_id'] ?? 0 );
$name          = esc_html( $business['name'] ?? '' );
$permalink     = $business['permalink'] ?? dbp_get_business_permalink( $post_id );
$thumbnail     = $business['thumbnail_url'] ?? ( get_the_post_thumbnail_url( $post_id, 'medium' ) ?: dbp_get_placeholder_image_url() );
$avg_rating    = (float) ( $business['avg_rating'] ?? 0 );
$review_count  = (int) ( $business['review_count'] ?? 0 );
$price_level   = (int) ( $business['price_level'] ?? 1 );
$category      = esc_html( $business['category'] ?? '' );
$distance      = $business['distance'] ?? null;
$is_claimed    = $business['is_claimed'] ?? ! empty( $business['claimed_by'] );
$is_featured   = $business['is_featured'] ?? (bool) ( $business['featured'] ?? false );
$is_new        = isset( $business['created_at'] ) && strtotime( $business['created_at'] ) > strtotime( '-30 days' );
/**
 * Filter the business card HTML before rendering.
 *
 * @param string $html        The card HTML (empty to use default rendering).
 * @param int    $business_id Business ID.
 */
$filtered_html = apply_filters( 'dbp/business/card_html', '', $b_id );
if ( ! empty( $filtered_html ) ) {
    echo $filtered_html; // phpcs:ignore WordPress.Security.EscapeOutput
    return;
}
?>
<article class="dbp-business-card" data-id="<?php echo esc_attr( (string) $b_id ); ?>" id="dbp-card-<?php echo esc_attr( (string) $b_id ); ?>">
    <a href="<?php echo esc_url( $permalink ); ?>" class="dbp-business-card__link">
        <div class="dbp-business-card__image">
            <img src="<?php echo esc_url( $thumbnail ); ?>"
                 alt="<?php echo esc_attr( $name ); ?>"
                 class="dbp-business-card__img"
                 loading="lazy"
                 width="300" height="200">
            <?php if ( $is_featured ) : ?>
                <span class="dbp-badge dbp-badge--featured dbp-business-card__badge"><?php esc_html_e( 'Featured', 'directories-builder-pro' ); ?></span>
            <?php endif; ?>
        </div>
        <div class="dbp-business-card__content">
            <h3 class="dbp-business-card__name"><?php echo $name; ?></h3>
            <div class="dbp-business-card__meta">
                <?php echo dbp_get_star_html( $avg_rating ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                <span class="dbp-business-card__review-count">
                    <?php echo esc_html( sprintf( _n( '%d review', '%d reviews', $review_count, 'directories-builder-pro' ), $review_count ) ); ?>
                </span>
            </div>
            <div class="dbp-business-card__details">
                <span class="dbp-business-card__price"><?php echo esc_html( dbp_get_price_label( $price_level ) ); ?></span>
                <?php if ( $category ) : ?>
                    <span class="dbp-business-card__separator">·</span>
                    <span class="dbp-business-card__category"><?php echo $category; ?></span>
                <?php endif; ?>
                <?php if ( $distance !== null ) : ?>
                    <span class="dbp-business-card__separator">·</span>
                    <span class="dbp-business-card__distance"><?php echo esc_html( dbp_format_distance( (float) $distance * 1000 ) ); ?></span>
                <?php endif; ?>
            </div>
            <div class="dbp-business-card__badges">
                <?php if ( $is_claimed ) : ?>
                    <span class="dbp-badge dbp-badge--claimed dbp-badge--small">
                        <svg viewBox="0 0 24 24" width="12" height="12"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" fill="currentColor"/></svg>
                        <?php esc_html_e( 'Claimed', 'directories-builder-pro' ); ?>
                    </span>
                <?php endif; ?>
                <?php if ( $is_new ) : ?>
                    <span class="dbp-badge dbp-badge--new dbp-badge--small"><?php esc_html_e( 'New', 'directories-builder-pro' ); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</article>