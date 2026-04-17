<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Template: User Profile
 *
 * Renders the user profile settings page wrapping Form Engine output.
 *
 * @slug     admin/user-profile
 * @version  1.0.0
 *
 * @args required: form_html (string) — pre-rendered form HTML from Form Engine
 *                 user_id (int) — the user ID being edited
 * @args optional: display_name (string) — user display name for heading
 *
 * @package DirectoriesBuilderPro\Templates\Admin
 */
$form_html    = $args['form_html'] ?? '';
$user_id      = (int) ( $args['user_id'] ?? 0 );
$display_name = $args['display_name'] ?? '';

if ( $display_name === '' && $user_id > 0 ) {
    $user = get_userdata( $user_id );
    $display_name = $user ? $user->display_name : __( 'User', 'directories-builder-pro' );
}
?>
<div class="wrap dbp-admin-user-profile">
    <h1>
        <?php
        printf(
            /* translators: %s: user display name */
            esc_html__( 'Profile Settings — %s', 'directories-builder-pro' ),
            esc_html( $display_name )
        );
        ?>
    </h1>
    <?php if ( $form_html ) : ?>
        <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput — pre-rendered form HTML from Form Engine. ?>
    <?php else : ?>
        <p><?php esc_html_e( 'Profile form could not be loaded.', 'directories-builder-pro' ); ?></p>
    <?php endif; ?>
</div>
