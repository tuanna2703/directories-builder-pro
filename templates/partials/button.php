<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Button
 *
 * Renders a styled CTA button/link with variant and optional icon.
 *
 * @slug     partials/button
 * @version  1.0.0
 *
 * @args required: label (string) — button text
 *                 url (string) — link href
 * @args optional: variant (string: 'primary'|'secondary'|'ghost', default 'primary')
 *                 icon (string) — CSS class for an icon span
 *                 target (string: '_blank'|'_self', default '_self')
 *                 extra_classes (string) — additional CSS classes
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$label         = $args['label'] ?? '';
$url           = $args['url'] ?? '#';
$variant       = $args['variant'] ?? 'primary';
$icon          = $args['icon'] ?? '';
$target        = $args['target'] ?? '_self';
$extra_classes = $args['extra_classes'] ?? '';

// Validate variant.
$allowed_variants = [ 'primary', 'secondary', 'ghost' ];
if ( ! in_array( $variant, $allowed_variants, true ) ) {
    $variant = 'primary';
}

$classes = trim( "dbp-button dbp-button--{$variant} {$extra_classes}" );
$rel     = ( $target === '_blank' ) ? 'noopener noreferrer' : '';
?>
<a class="<?php echo esc_attr( $classes ); ?>"
   href="<?php echo esc_url( $url ); ?>"
   target="<?php echo esc_attr( $target ); ?>"
   <?php if ( $rel ) : ?>rel="<?php echo esc_attr( $rel ); ?>"<?php endif; ?>>
    <?php if ( $icon ) : ?>
        <span class="dbp-button__icon <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
    <?php endif; ?>
    <span class="dbp-button__label"><?php echo esc_html( $label ); ?></span>
</a>
