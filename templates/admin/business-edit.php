<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Template: Business Edit
 *
 * Renders the business editor page wrapping Form Engine output.
 *
 * @slug     admin/business-edit
 * @version  1.0.0
 *
 * @args required: form_html (string) — pre-rendered form HTML from Form Engine
 *                 post_id (int) — the post ID being edited
 *
 * @package DirectoriesBuilderPro\Templates\Admin
 */
$form_html = $args['form_html'] ?? '';
$post_id   = (int) ( $args['post_id'] ?? 0 );
?>
<div class="dbp-admin-business-edit" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
    <?php if ( $form_html ) : ?>
        <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput — pre-rendered form HTML from Form Engine. ?>
    <?php else : ?>
        <p><?php esc_html_e( 'Business form could not be loaded.', 'directories-builder-pro' ); ?></p>
    <?php endif; ?>
</div>
