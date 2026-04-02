<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Page: Settings
 *
 * Renders the plugin settings form using the Form Engine.
 * Replaces the previous WordPress Settings API implementation.
 *
 * @package DirectoriesBuilderPro\Admin\Pages
 */
$form = \DirectoriesBuilderPro\Core\Managers\Form_Manager::get_instance()
        ->get( 'plugin_settings' );
?>
<div class="wrap dbp-admin-settings">
    <h1><?php esc_html_e( 'Directory Settings', 'directories-builder-pro' ); ?></h1>
    <?php
    if ( $form ) {
        $form->render_form();
    } else {
        echo '<p>' . esc_html__( 'Settings form could not be loaded.', 'directories-builder-pro' ) . '</p>';
    }
    ?>
</div>
