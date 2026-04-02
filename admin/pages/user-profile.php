<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Page: User Profile Settings
 *
 * Renders the user profile form using the Form Engine.
 * Allows users to manage their DBP profile settings.
 *
 * @package DirectoriesBuilderPro\Admin\Pages
 */
$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id();

// Permission check.
if ( $user_id !== get_current_user_id() && ! current_user_can( 'edit_user', $user_id ) ) {
    wp_die( esc_html__( 'You do not have permission to edit this user.', 'directories-builder-pro' ) );
}

$user = get_userdata( $user_id );
if ( ! $user ) {
    wp_die( esc_html__( 'User not found.', 'directories-builder-pro' ) );
}

$form = \DirectoriesBuilderPro\Core\Managers\Form_Manager::get_instance()
        ->get( 'user_profile' );
?>
<div class="wrap dbp-admin-user-profile">
    <h1>
        <?php
        printf(
            /* translators: %s: user display name */
            esc_html__( 'Profile Settings — %s', 'directories-builder-pro' ),
            esc_html( $user->display_name )
        );
        ?>
    </h1>
    <?php
    if ( $form ) {
        $form->render_form( $user_id );
    } else {
        echo '<p>' . esc_html__( 'Profile form could not be loaded.', 'directories-builder-pro' ) . '</p>';
    }
    ?>
</div>
