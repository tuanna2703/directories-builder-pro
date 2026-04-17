<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Badge
 *
 * Renders a styled badge/label component with type-based CSS modifiers.
 *
 * @slug     partials/badge
 * @version  1.0.0
 *
 * @args required: type (string: 'claimed'|'featured'|'new'|'elite'|'pending'|'spam')
 * @args optional: label (string) — override the default label for this type
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$type  = $args['type'] ?? '';
$label = $args['label'] ?? null;

// Map type to default labels.
$default_labels = [
    'claimed'  => __( 'Claimed', 'directories-builder-pro' ),
    'featured' => __( 'Featured', 'directories-builder-pro' ),
    'new'      => __( 'New', 'directories-builder-pro' ),
    'elite'    => __( 'Elite', 'directories-builder-pro' ),
    'pending'  => __( 'Pending', 'directories-builder-pro' ),
    'spam'     => __( 'Spam', 'directories-builder-pro' ),
];

// Determine visible label.
if ( $label === null ) {
    $label = $default_labels[ $type ] ?? ucfirst( $type );
}

// Only render for known types.
if ( $type === '' ) {
    return;
}
?>
<span class="dbp-badge dbp-badge--<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></span>
