<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Business Header
 *
 * Hero section with photo carousel, business name, rating, and CTA buttons.
 *
 * @slug     business/header
 * @version  1.0.0
 *
 * @args required: business (array — keys: id, wp_post_id, name, avg_rating,
 *                 review_count, price_level, phone, website, address, city,
 *                 state, zip, country, lat, lng, claimed_by, featured, hours)
 * @args optional: show_claim_button (bool, default true)
 *
 * @package DirectoriesBuilderPro\Templates\Business
 */
$business         = $args['business'] ?? [];
$show_claim_button = (bool) ( $args['show_claim_button'] ?? true );

$post_id      = (int) ( $business['wp_post_id'] ?? get_the_ID() );
$name         = $business['name'] ?? get_the_title( $post_id );
$avg_rating   = (float) ( $business['avg_rating'] ?? 0 );
$review_count = (int) ( $business['review_count'] ?? 0 );
$price_level  = (int) ( $business['price_level'] ?? 1 );
$phone        = $business['phone'] ?? '';
$website      = $business['website'] ?? '';
$address      = trim( implode( ', ', array_filter( [
    $business['address'] ?? '',
    $business['city'] ?? '',
    ( $business['state'] ?? '' ) . ' ' . ( $business['zip'] ?? '' ),
] ) ) );
$is_claimed   = ! empty( $business['claimed_by'] );
$is_featured  = (bool) ( $business['featured'] ?? false );

// Get photos.
$photos     = get_attached_media( 'image', $post_id );
$has_photos = ! empty( $photos );

// Category.
$category_terms = wp_get_post_terms( $post_id, 'dbp_category', [ 'fields' => 'names' ] );
$category_name  = ( ! is_wp_error( $category_terms ) && ! empty( $category_terms ) ) ? $category_terms[0] : '';
?>
<section class="dbp-business-header">
    <!-- Photo Carousel -->
    <div class="dbp-business-header__photos">
        <?php if ( $has_photos ) : ?>
            <div class="dbp-photo-carousel" id="dbp-photo-carousel" data-carousel>
                <?php foreach ( $photos as $photo ) : ?>
                    <div class="dbp-photo-carousel__slide">
                        <?php echo wp_get_attachment_image( $photo->ID, 'large', false, [
                            'class'   => 'dbp-photo-carousel__img',
                            'loading' => 'lazy',
                        ] ); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="dbp-photo-carousel__placeholder">
                <img src="<?php echo esc_url( dbp_get_placeholder_image_url() ); ?>"
                     alt="<?php echo esc_attr( $name ); ?>"
                     class="dbp-photo-carousel__img">
            </div>
        <?php endif; ?>
    </div>

    <!-- Business Info -->
    <div class="dbp-business-header__info">
        <div class="dbp-business-header__badges">
            <?php if ( $is_featured ) :
                dbp_template( 'partials/badge', [ 'type' => 'featured' ] );
            endif; ?>
            <?php if ( $is_claimed ) :
                dbp_template( 'partials/badge', [ 'type' => 'claimed' ] );
            endif; ?>
        </div>

        <h1 class="dbp-business-header__name"><?php echo esc_html( $name ); ?></h1>

        <div class="dbp-business-header__meta">
            <?php dbp_template( 'partials/star-rating', [
                'rating'      => $avg_rating,
                'show_number' => true,
                'count'       => $review_count,
            ] ); ?>
            <?php dbp_template( 'partials/price-label', [ 'level' => $price_level ] ); ?>
            <?php if ( $category_name ) : ?>
                <span class="dbp-business-header__category"><?php echo esc_html( $category_name ); ?></span>
            <?php endif; ?>
        </div>

        <?php if ( $address ) : ?>
            <p class="dbp-business-header__address"><?php echo esc_html( $address ); ?></p>
        <?php endif; ?>

        <!-- CTA Buttons -->
        <div class="dbp-business-header__actions">
            <?php if ( $phone ) : ?>
                <a href="tel:<?php echo esc_attr( $phone ); ?>" class="dbp-btn dbp-btn--primary dbp-btn--icon">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Call', 'directories-builder-pro' ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $address ) : ?>
                <a href="<?php echo esc_url( \DirectoriesBuilderPro\Modules\Maps\Services\Map_Service::get_directions_url( $address ) ); ?>"
                   class="dbp-btn dbp-btn--outline dbp-btn--icon" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M21.71 11.29l-9-9c-.39-.39-1.02-.39-1.41 0l-9 9c-.39.39-.39 1.02 0 1.41l9 9c.39.39 1.02.39 1.41 0l9-9c.39-.38.39-1.01 0-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Directions', 'directories-builder-pro' ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $website ) : ?>
                <a href="<?php echo esc_url( $website ); ?>" class="dbp-btn dbp-btn--outline dbp-btn--icon" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2s.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.9-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2s.07-1.35.16-2h4.68c.09.65.16 1.32.16 2s-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2s-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Website', 'directories-builder-pro' ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $show_claim_button && ! $is_claimed && is_user_logged_in() ) : ?>
                <button type="button" class="dbp-btn dbp-btn--outline dbp-btn--icon" id="dbp-claim-btn"
                        data-business-id="<?php echo esc_attr( (string) ( $business['id'] ?? 0 ) ); ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Claim', 'directories-builder-pro' ); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>
