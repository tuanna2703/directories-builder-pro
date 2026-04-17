<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Template: Review Moderation
 *
 * Renders the review moderation page with WP_List_Table and status tabs.
 *
 * @slug     admin/moderation
 * @version  1.0.0
 *
 * @args required: table_html (string) — pre-rendered WP_List_Table HTML
 *                 current_status (string) — current status filter
 *
 * @package DirectoriesBuilderPro\Templates\Admin
 */
$table_html     = $args['table_html'] ?? '';
$current_status = $args['current_status'] ?? '';
$updated        = ! empty( $_GET['updated'] );
?>
<div class="wrap dbp-admin-moderation">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Review Moderation', 'directories-builder-pro' ); ?></h1>

    <?php if ( $updated ) : ?>
        <?php dbp_template( 'partials/notice', [
            'type'        => 'success',
            'message'     => __( 'Reviews updated successfully.', 'directories-builder-pro' ),
            'dismissible' => true,
        ] ); ?>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="dbp_bulk_moderate">
        <?php wp_nonce_field( 'bulk-reviews' ); ?>
        <?php echo $table_html; // phpcs:ignore WordPress.Security.EscapeOutput — pre-rendered WP_List_Table. ?>
    </form>
</div>
