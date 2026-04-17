<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Empty State
 *
 * Renders a centered "no results" / "nothing here" block with optional CTA button.
 *
 * @slug     partials/empty-state
 * @version  1.0.0
 *
 * @args required: title (string) — heading text
 * @args optional: message (string) — descriptive paragraph
 *                 icon_class (string) — CSS class for icon element
 *                 action_label (string) — CTA button text
 *                 action_url (string) — CTA button URL
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$title        = $args['title'] ?? '';
$message      = $args['message'] ?? '';
$icon_class   = $args['icon_class'] ?? '';
$action_label = $args['action_label'] ?? '';
$action_url   = $args['action_url'] ?? '';

if ( $title === '' ) {
    return;
}
?>
<div class="dbp-empty-state">
    <?php if ( $icon_class ) : ?>
        <div class="dbp-empty-state__icon">
            <span class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></span>
        </div>
    <?php else : ?>
        <div class="dbp-empty-state__icon">
            <svg viewBox="0 0 24 24" width="64" height="64" aria-hidden="true">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor" opacity="0.2"/>
            </svg>
        </div>
    <?php endif; ?>
    <h3 class="dbp-empty-state__title"><?php echo esc_html( $title ); ?></h3>
    <?php if ( $message ) : ?>
        <p class="dbp-empty-state__message"><?php echo esc_html( $message ); ?></p>
    <?php endif; ?>
    <?php if ( $action_label && $action_url ) :
        dbp_template( 'partials/button', [
            'label'   => $action_label,
            'url'     => $action_url,
            'variant' => 'primary',
        ] );
    endif; ?>
</div>
