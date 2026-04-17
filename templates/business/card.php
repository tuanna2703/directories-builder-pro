<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Business Card
 *
 * Reusable card component for search results and business listings.
 * Uses shared partials for star rating, price label, and badges.
 *
 * @slug     business/card
 * @version  1.0.0
 *
 * @args required: business (array — keys: id, name, permalink, avg_rating,
 *                 review_count, price_level, category_name, thumbnail_url,
 *                 is_claimed, is_featured, created_at)
 * @args optional: distance (float in km/miles)
 *                 show_distance (bool, default false)
 *                 distance_unit (string, default 'km')
 *
 * @package DirectoriesBuilderPro\Templates\Business
 */
$business = $args['business'] ?? [];

$b_id          = (int) ( $business['id'] ?? 0 );
$post_id       = (int) ( $business['wp_post_id'] ?? 0 );
$name          = $business['name'] ?? '';
$permalink     = $business['permalink'] ?? dbp_get_business_permalink( $post_id );
$thumbnail     = $business['thumbnail_url'] ?? ( get_the_post_thumbnail_url( $post_id, 'medium' ) ?: dbp_get_placeholder_image_url() );
$avg_rating    = (float) ( $business['avg_rating'] ?? 0 );
$review_count  = (int) ( $business['review_count'] ?? 0 );
$price_level   = (int) ( $business['price_level'] ?? 1 );
$category      = $business['category_name'] ?? $business['category'] ?? '';
$is_claimed    = $business['is_claimed'] ?? ! empty( $business['claimed_by'] );
$is_featured   = $business['is_featured'] ?? (bool) ( $business['featured'] ?? false );
$is_new        = isset( $business['created_at'] ) && strtotime( $business['created_at'] ) > strtotime( '-30 days' );

$distance      = $args['distance'] ?? $business['distance'] ?? null;
$show_distance = (bool) ( $args['show_distance'] ?? ( $distance !== null ) );
$distance_unit = $args['distance_unit'] ?? 'km';

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
<article class="dbp-business-card" data-business-id="<?php echo esc_attr( (string) $b_id ); ?>" id="dbp-card-<?php echo esc_attr( (string) $b_id ); ?>">
    <a href="<?php echo esc_url( $permalink ); ?>" class="dbp-business-card__link">
        <div class="dbp-business-card__image">
            <img src="<?php echo esc_url( $thumbnail ); ?>"
                 alt="<?php echo esc_attr( $name ); ?>"
                 class="dbp-business-card__img"
                 loading="lazy"
                 width="300" height="200">
            <?php if ( $is_featured ) :
                dbp_template( 'partials/badge', [ 'type' => 'featured' ] );
            endif; ?>
        </div>
        <div class="dbp-business-card__content">
            <h3 class="dbp-business-card__name"><?php echo esc_html( $name ); ?></h3>
            <div class="dbp-business-card__meta">
                <?php dbp_template( 'partials/star-rating', [
                    'rating'      => $avg_rating,
                    'show_number' => true,
                    'count'       => $review_count,
                ] ); ?>
            </div>
            <div class="dbp-business-card__details">
                <?php dbp_template( 'partials/price-label', [ 'level' => $price_level ] ); ?>
                <?php if ( $category ) : ?>
                    <span class="dbp-business-card__separator">&middot;</span>
                    <span class="dbp-business-card__category"><?php echo esc_html( $category ); ?></span>
                <?php endif; ?>
                <?php if ( $show_distance && $distance !== null ) : ?>
                    <span class="dbp-business-card__separator">&middot;</span>
                    <span class="dbp-business-card__distance"><?php echo esc_html( dbp_format_distance( (float) $distance * 1000, $distance_unit ) ); ?></span>
                <?php endif; ?>
            </div>
            <div class="dbp-business-card__badges">
                <?php if ( $is_claimed ) :
                    dbp_template( 'partials/badge', [ 'type' => 'claimed' ] );
                endif; ?>
                <?php if ( $is_new ) :
                    dbp_template( 'partials/badge', [ 'type' => 'new' ] );
                endif; ?>
            </div>
        </div>
    </a>
</article>
