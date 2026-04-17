<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Avatar
 *
 * Renders a user avatar image with fallback to a placeholder.
 *
 * @slug     partials/avatar
 * @version  1.0.0
 *
 * @args required: user_id (int)
 * @args optional: size (int, default 40) — avatar dimensions in pixels
 *                 alt (string) — alt text, defaults to user display name
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$user_id = (int) ( $args['user_id'] ?? 0 );
$size    = (int) ( $args['size'] ?? 40 );
$alt     = $args['alt'] ?? '';

// Build alt text from user data if not provided.
if ( $alt === '' && $user_id > 0 ) {
    $user = get_userdata( $user_id );
    $alt  = $user ? $user->display_name : __( 'User avatar', 'directories-builder-pro' );
}

// Get avatar URL.
$avatar_url = '';
if ( $user_id > 0 ) {
    $avatar_url = get_avatar_url( $user_id, [ 'size' => $size ] );
}

// Fallback to placeholder if no avatar or default mystery person.
if ( empty( $avatar_url ) ) {
    $avatar_url = DBP_URL . 'assets/images/avatar-placeholder.png';
}
?>
<img class="dbp-avatar"
     src="<?php echo esc_url( $avatar_url ); ?>"
     alt="<?php echo esc_attr( $alt ); ?>"
     width="<?php echo esc_attr( (string) $size ); ?>"
     height="<?php echo esc_attr( (string) $size ); ?>"
     loading="lazy">
