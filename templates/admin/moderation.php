<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Moderation Template
 *
 * @var array $args Contains 'table_html' and 'current_status'.
 */
?>
<div class="wrap dbp-admin-moderation">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Review Moderation', 'directories-builder-pro' ); ?></h1>
    <?php if ( ! empty( $_GET['updated'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Reviews updated successfully.', 'directories-builder-pro' ); ?></p>
        </div>
    <?php endif; ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="dbp_bulk_moderate">
        <?php wp_nonce_field( 'bulk-reviews' ); ?>
        <?php echo $args['table_html']; // This is pre-rendered via WP_List_Table, so we can't escape here easily without breaking it ?>
    </form>
</div>
