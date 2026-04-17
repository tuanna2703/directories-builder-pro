<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Price Label
 *
 * Renders a price level indicator ($–$$$$) with appropriate aria-label.
 *
 * @slug     partials/price-label
 * @version  1.0.0
 *
 * @args required: level (int 1–4)
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$level = (int) ( $args['level'] ?? 0 );

// Only render for valid levels.
if ( $level < 1 || $level > 4 ) {
    return;
}

$labels = [
    1 => __( 'Inexpensive', 'directories-builder-pro' ),
    2 => __( 'Moderate', 'directories-builder-pro' ),
    3 => __( 'Pricey', 'directories-builder-pro' ),
    4 => __( 'Ultra High-End', 'directories-builder-pro' ),
];

$symbols = str_repeat( '$', $level );
?>
<span class="dbp-price dbp-price--<?php echo esc_attr( (string) $level ); ?>" aria-label="<?php echo esc_attr( $labels[ $level ] ); ?>"><?php echo esc_html( $symbols ); ?></span>
