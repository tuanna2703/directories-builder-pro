<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Single Business Page
 *
 * Full detail page: header, about, photos, reviews, similar businesses.
 * This is a full-page template loaded via template_include filter.
 *
 * @slug     business/single
 * @version  1.0.0
 *
 * @args required: business (array) — full business data
 *                 reviews (array) — array of review data
 *                 similar_businesses (array) — array of similar business data
 * @args optional: review_form_visible (bool, default true)
 *
 * @package DirectoriesBuilderPro\Templates\Business
 */

// If loaded as a CPT template override, prepare $args from query.
if ( empty( $args ) ) {
    $post_id = get_the_ID();
    $service = new \DirectoriesBuilderPro\Services\Business_Service();
    $business = $service->get_business_by_post_id( (int) $post_id );

    if ( ! $business ) {
        get_header();
        echo '<div class="dbp-container"><p>' . esc_html__( 'Business not found.', 'directories-builder-pro' ) . '</p></div>';
        get_footer();
        return;
    }

    $business_id    = (int) $business['id'];
    $review_service = new \DirectoriesBuilderPro\Services\Review_Service();
    $reviews        = $review_service->get_reviews_for_business( $business_id, [ 'per_page' => 10 ] );
    $total_reviews  = $review_service->get_repository()->count( [ 'business_id' => $business_id, 'status' => 'approved' ] );

    // Enrich reviews with user data.
    foreach ( $reviews as &$review ) {
        $user = get_userdata( (int) $review['user_id'] );
        $review['author_name']   = $user ? $user->display_name : __( 'Anonymous', 'directories-builder-pro' );
        $review['author_avatar'] = get_avatar_url( (int) $review['user_id'], [ 'size' => 40 ] );
        $review['is_elite']      = (bool) get_user_meta( (int) $review['user_id'], 'dbp_elite', true );
        $review['time_ago']      = dbp_time_ago( $review['created_at'] );
    }
    unset( $review );

    $similar    = $service->get_similar_businesses( $business_id, 3 );
    $args = [
        'business'            => $business,
        'reviews'             => $reviews,
        'similar_businesses'  => $similar,
        'review_form_visible' => true,
        '_total_reviews'      => $total_reviews,
        '_post_id'            => $post_id,
    ];
}

$business            = $args['business'];
$reviews             = $args['reviews'] ?? [];
$similar_businesses  = $args['similar_businesses'] ?? [];
$review_form_visible = (bool) ( $args['review_form_visible'] ?? true );
$total_reviews       = $args['_total_reviews'] ?? count( $reviews );
$post_id             = $args['_post_id'] ?? (int) ( $business['wp_post_id'] ?? get_the_ID() );
$business_id         = (int) ( $business['id'] ?? 0 );

// JSON-LD structured data.
add_action( 'wp_head', static function () use ( $business ): void {
    $hours = $business['hours'] ?? '[]';
    if ( is_string( $hours ) ) {
        $hours = json_decode( $hours, true ) ?: [];
    }
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'LocalBusiness',
        'name'     => $business['name'],
        'url'      => dbp_get_business_permalink( (int) $business['wp_post_id'] ),
        'address'  => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $business['address'] ?? '',
            'addressLocality' => $business['city'] ?? '',
            'addressRegion'   => $business['state'] ?? '',
            'postalCode'      => $business['zip'] ?? '',
            'addressCountry'  => $business['country'] ?? 'US',
        ],
    ];
    if ( ! empty( $business['phone'] ) ) {
        $schema['telephone'] = $business['phone'];
    }
    if ( ! empty( $business['website'] ) ) {
        $schema['url'] = $business['website'];
    }
    if ( ! empty( $business['lat'] ) && ! empty( $business['lng'] ) ) {
        $schema['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) $business['lat'],
            'longitude' => (float) $business['lng'],
        ];
    }
    if ( (float) $business['avg_rating'] > 0 ) {
        $schema['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format( (float) $business['avg_rating'], 1 ),
            'reviewCount' => (int) $business['review_count'],
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}, 1 );

get_header();
?>
<div class="dbp-single-business dbp-container">
    <?php // Section 1: Header. ?>
    <?php dbp_template( 'business/header', [ 'business' => $business ] ); ?>

    <?php // Section 2: About. ?>
    <?php dbp_template( 'business/about', [ 'business' => $business ] ); ?>

    <?php // Section 3: Photos Grid. ?>
    <?php
    $photos = get_attached_media( 'image', $post_id );
    if ( ! empty( $photos ) ) :
        $photo_count    = count( $photos );
        $display_photos = array_slice( $photos, 0, 12 );
        ?>
        <section class="dbp-photos-section">
            <h2 class="dbp-section-title">
                <?php
                /* translators: %d: number of photos */
                echo esc_html( sprintf( __( 'Photos (%d)', 'directories-builder-pro' ), $photo_count ) );
                ?>
            </h2>
            <div class="dbp-photos-grid">
                <?php foreach ( $display_photos as $photo ) : ?>
                    <a href="<?php echo esc_url( wp_get_attachment_url( $photo->ID ) ); ?>"
                       class="dbp-photos-grid__item dbp-lightbox-trigger"
                       data-full="<?php echo esc_url( wp_get_attachment_url( $photo->ID ) ); ?>">
                        <?php echo wp_get_attachment_image( $photo->ID, 'medium', false, [
                            'class'   => 'dbp-photos-grid__img',
                            'loading' => 'lazy',
                        ] ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ( $photo_count > 12 ) : ?>
                <a href="#" class="dbp-photos-section__see-all">
                    <?php
                    /* translators: %d: total photo count */
                    echo esc_html( sprintf( __( 'See all %d photos', 'directories-builder-pro' ), $photo_count ) );
                    ?>
                </a>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php // Section 4: Reviews. ?>
    <?php dbp_template( 'reviews/list', [
        'reviews'     => $reviews,
        'business_id' => $business_id,
        'total'       => $total_reviews,
    ] ); ?>
    <?php if ( $review_form_visible ) :
        dbp_template( 'reviews/form', [ 'business_id' => $business_id ] );
    endif; ?>

    <?php // Section 5: Similar Businesses. ?>
    <?php if ( ! empty( $similar_businesses ) ) : ?>
        <section class="dbp-similar-section">
            <h2 class="dbp-section-title"><?php esc_html_e( 'Similar Businesses', 'directories-builder-pro' ); ?></h2>
            <div class="dbp-business-grid dbp-business-grid--similar">
                <?php foreach ( $similar_businesses as $biz ) :
                    $biz['thumbnail_url'] = get_the_post_thumbnail_url( (int) $biz['wp_post_id'], 'medium' ) ?: dbp_get_placeholder_image_url();
                    $terms = wp_get_post_terms( (int) $biz['wp_post_id'], 'dbp_category', [ 'fields' => 'names' ] );
                    $biz['category_name'] = is_array( $terms ) && ! empty( $terms ) ? $terms[0] : '';
                    $biz['permalink']     = dbp_get_business_permalink( (int) $biz['wp_post_id'] );
                    $biz['is_claimed']    = ! empty( $biz['claimed_by'] );
                    $biz['is_featured']   = (bool) ( $biz['featured'] ?? false );
                    dbp_template( 'business/card', [ 'business' => $biz ] );
                endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<!-- Lightbox Container -->
<div class="dbp-lightbox" id="dbp-lightbox" style="display:none;">
    <button class="dbp-lightbox__close" id="dbp-lightbox-close" aria-label="<?php esc_attr_e( 'Close', 'directories-builder-pro' ); ?>">&times;</button>
    <img class="dbp-lightbox__img" id="dbp-lightbox-img" src="" alt="">
</div>
<?php get_footer(); ?>
