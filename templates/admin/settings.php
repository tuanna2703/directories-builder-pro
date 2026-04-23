<?php
/**
 * Admin Settings Page Template.
 *
 * @args required: form_html (string)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap dbp-admin-wrap">
    <h1><?php esc_html_e( 'Directories Builder Pro Settings', 'directories-builder-pro' ); ?></h1>
    
    <div class="dbp-admin-settings-container">
        <?php 
        // form_html contains the fully rendered WP settings form or custom form structure.
        // It should already be escaped by the caller.
        echo $args['form_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
    </div>
</div>
