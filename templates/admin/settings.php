<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Template: Settings
 *
 * Wraps the Form Engine output with admin page chrome.
 *
 * @slug     admin/settings
 * @version  1.0.0
 *
 * @args required: form_html (string) — pre-rendered form HTML from Form Engine
 *
 * @package DirectoriesBuilderPro\Templates\Admin
 */
$form_html = $args['form_html'] ?? '';
?>
<div class="wrap dbp-admin-settings">
    <h1><?php esc_html_e( 'Directory Settings', 'directories-builder-pro' ); ?></h1>
    <?php if ( $form_html ) : ?>
        <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput — pre-rendered form HTML from Form Engine. ?>
    <?php else : ?>
        <p><?php esc_html_e( 'Settings form could not be loaded.', 'directories-builder-pro' ); ?></p>
    <?php endif; ?>
</div>
