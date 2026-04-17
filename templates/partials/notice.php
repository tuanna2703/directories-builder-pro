<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Notice
 *
 * Renders a notice/alert component. Uses WP admin notice classes in admin context,
 * custom DBP classes on the frontend.
 *
 * @slug     partials/notice
 * @version  1.0.0
 *
 * @args required: message (string) — notice text
 *                 type (string: 'success'|'error'|'warning'|'info')
 * @args optional: dismissible (bool, default false)
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$message     = $args['message'] ?? '';
$type        = $args['type'] ?? 'info';
$dismissible = (bool) ( $args['dismissible'] ?? false );

// Validate type.
$allowed_types = [ 'success', 'error', 'warning', 'info' ];
if ( ! in_array( $type, $allowed_types, true ) ) {
    $type = 'info';
}

if ( $message === '' ) {
    return;
}

if ( is_admin() ) :
    // WordPress admin notice pattern.
    $admin_classes = "notice notice-{$type}";
    if ( $dismissible ) {
        $admin_classes .= ' is-dismissible';
    }
    ?>
    <div class="<?php echo esc_attr( $admin_classes ); ?>">
        <p><?php echo esc_html( $message ); ?></p>
    </div>
<?php else :
    // Frontend notice pattern.
    $frontend_classes = "dbp-notice dbp-notice--{$type}";
    if ( $dismissible ) {
        $frontend_classes .= ' dbp-notice--dismissible';
    }
    ?>
    <div class="<?php echo esc_attr( $frontend_classes ); ?>" role="alert">
        <p class="dbp-notice__message"><?php echo esc_html( $message ); ?></p>
        <?php if ( $dismissible ) : ?>
            <button type="button" class="dbp-notice__dismiss" aria-label="<?php esc_attr_e( 'Dismiss notice', 'directories-builder-pro' ); ?>">
                &times;
            </button>
        <?php endif; ?>
    </div>
<?php endif; ?>
