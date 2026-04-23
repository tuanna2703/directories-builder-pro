<?php
/**
 * Admin User Profile Template.
 *
 * @args required: form_html (string), user_id (int)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap dbp-admin-wrap">
    <h2><?php esc_html_e( 'Directory Profile Settings', 'directories-builder-pro' ); ?></h2>
    
    <div class="dbp-user-profile-settings">
        <?php 
        // form_html contains the fully rendered fields.
        // It should already be escaped by the caller.
        echo $args['form_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
    </div>
</div>
