<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Business About
 *
 * Description, attributes grid, opening hours table, embedded map.
 *
 * @slug     business/about
 * @version  1.0.0
 *
 * @args required: business (array — keys: description, hours, meta, lat, lng,
 *                 address, city, state, zip, name)
 *
 * @package DirectoriesBuilderPro\Templates\Business
 */
$business    = $args['business'] ?? [];
$description = wp_kses_post( $business['description'] ?? '' );
$meta        = $business['meta'] ?? [];
$hours       = $business['hours'] ?? '[]';
$lat         = (float) ( $business['lat'] ?? 0 );
$lng         = (float) ( $business['lng'] ?? 0 );
$address     = trim( implode( ', ', array_filter( [
    $business['address'] ?? '',
    $business['city'] ?? '',
    ( $business['state'] ?? '' ) . ' ' . ( $business['zip'] ?? '' ),
] ) ) );

if ( is_string( $hours ) ) {
    $hours = json_decode( $hours, true ) ?: [];
}

$attributes = [
    'wifi'          => [ 'label' => __( 'Wi-Fi', 'directories-builder-pro' ),          'icon' => 'M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z' ],
    'parking'       => [ 'label' => __( 'Parking', 'directories-builder-pro' ),        'icon' => 'M13 3H6v18h4v-6h3c3.31 0 6-2.69 6-6s-2.69-6-6-6zm.2 8H10V7h3.2c1.1 0 2 .9 2 2s-.9 2-2 2z' ],
    'outdoor'       => [ 'label' => __( 'Outdoor Seating', 'directories-builder-pro' ), 'icon' => 'M21 10c-1.5 0-2.27.59-3.13 1.25C16.77 12.09 15.61 13 13 13c-2.61 0-3.77-.91-4.87-1.75-.86-.66-1.63-1.25-3.13-1.25-1.5 0-2.27.59-3.13 1.25l1.26 1.5C3.99 12.09 4.62 11.5 5 11.5c.73 0 1.27.41 2.13 1.07C8.35 13.57 9.88 15 13 15s4.65-1.43 5.87-2.43c.86-.66 1.4-1.07 2.13-1.07v-1.5z' ],
    'delivery'      => [ 'label' => __( 'Delivery', 'directories-builder-pro' ),       'icon' => 'M19 7c0-1.1-.9-2-2-2h-3v2h3v2.65L13.52 14H10V9H6c-2.21 0-4 1.79-4 4v3h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4.48L19 10.35V7zM7 17c-.55 0-1-.45-1-1h2c0 .55-.45 1-1 1z' ],
    'takeout'       => [ 'label' => __( 'Takeout', 'directories-builder-pro' ),        'icon' => 'M18 6V4l-2-2H8L6 4v2H2v12h20V6h-4zm-8-2h4v2h-4V4zm10 14H4V8h16v10zM12 10l-4 4h3v4h2v-4h3l-4-4z' ],
    'reservations'  => [ 'label' => __( 'Reservations', 'directories-builder-pro' ),   'icon' => 'M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8 4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z' ],
    'accessibility' => [ 'label' => __( 'Accessible', 'directories-builder-pro' ),     'icon' => 'M12 2c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z' ],
];

$days_of_week = [
    'monday'    => __( 'Monday', 'directories-builder-pro' ),
    'tuesday'   => __( 'Tuesday', 'directories-builder-pro' ),
    'wednesday' => __( 'Wednesday', 'directories-builder-pro' ),
    'thursday'  => __( 'Thursday', 'directories-builder-pro' ),
    'friday'    => __( 'Friday', 'directories-builder-pro' ),
    'saturday'  => __( 'Saturday', 'directories-builder-pro' ),
    'sunday'    => __( 'Sunday', 'directories-builder-pro' ),
];

$wp_timezone = wp_timezone();
$today       = strtolower( ( new \DateTime( 'now', $wp_timezone ) )->format( 'l' ) );

// Check if any attributes are set.
$has_attributes = false;
foreach ( $attributes as $key => $info ) {
    if ( ! empty( $meta[ $key ] ) ) {
        $has_attributes = true;
        break;
    }
}
?>
<section class="dbp-business-about">
    <?php if ( $description ) : ?>
        <div class="dbp-business-about__description">
            <h2 class="dbp-section-title"><?php esc_html_e( 'About', 'directories-builder-pro' ); ?></h2>
            <div class="dbp-business-about__text"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput — already wp_kses_post. ?></div>
        </div>
    <?php endif; ?>

    <?php if ( $has_attributes ) : ?>
        <div class="dbp-business-about__attributes">
            <h3 class="dbp-section-subtitle"><?php esc_html_e( 'Amenities & More', 'directories-builder-pro' ); ?></h3>
            <div class="dbp-attributes-grid">
                <?php foreach ( $attributes as $key => $info ) : ?>
                    <?php if ( ! empty( $meta[ $key ] ) ) : ?>
                        <div class="dbp-attribute-item">
                            <svg viewBox="0 0 24 24" width="20" height="20" class="dbp-attribute-item__icon">
                                <path d="<?php echo esc_attr( $info['icon'] ); ?>" fill="currentColor"/>
                            </svg>
                            <span class="dbp-attribute-item__label"><?php echo esc_html( $info['label'] ); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $hours ) ) : ?>
        <div class="dbp-business-about__hours">
            <h3 class="dbp-section-subtitle"><?php esc_html_e( 'Hours', 'directories-builder-pro' ); ?></h3>
            <table class="dbp-hours-table">
                <tbody>
                <?php foreach ( $days_of_week as $day_key => $day_label ) :
                    $day_data  = $hours[ $day_key ] ?? null;
                    $is_today  = ( $day_key === $today );
                    $is_closed = ( ! $day_data || ( isset( $day_data['closed'] ) && $day_data['closed'] ) );
                    ?>
                    <tr class="dbp-hours-table__row <?php echo $is_today ? 'dbp-hours-table__row--today' : ''; ?>">
                        <td class="dbp-hours-table__day">
                            <?php echo esc_html( $day_label ); ?>
                            <?php if ( $is_today ) : ?>
                                <span class="dbp-hours-table__today-badge"><?php esc_html_e( 'Today', 'directories-builder-pro' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="dbp-hours-table__time">
                            <?php if ( $is_closed ) : ?>
                                <span class="dbp-hours-table__closed"><?php esc_html_e( 'Closed', 'directories-builder-pro' ); ?></span>
                            <?php else : ?>
                                <?php echo esc_html( $day_data['open'] ?? '' ); ?> &ndash; <?php echo esc_html( $day_data['close'] ?? '' ); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ( $lat && $lng ) : ?>
        <div class="dbp-business-about__map">
            <h3 class="dbp-section-subtitle"><?php esc_html_e( 'Location', 'directories-builder-pro' ); ?></h3>
            <?php if ( $address ) : ?>
                <p class="dbp-business-about__address"><?php echo esc_html( $address ); ?></p>
            <?php endif; ?>
            <div class="dbp-map-embed" id="dbp-business-map"
                 data-lat="<?php echo esc_attr( (string) $lat ); ?>"
                 data-lng="<?php echo esc_attr( (string) $lng ); ?>"
                 data-name="<?php echo esc_attr( $business['name'] ?? '' ); ?>"
                 style="min-height:300px;">
            </div>
        </div>
    <?php endif; ?>
</section>
