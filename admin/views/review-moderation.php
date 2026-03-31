<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin View: Review Moderation
 *
 * Extends WP_List_Table for review management.
 *
 * @package DirectoriesBuilderPro\Admin\Views
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * DBP_Review_List_Table class.
 */
class DBP_Review_List_Table extends WP_List_Table {
    private \DirectoriesBuilderPro\Repositories\Review_Repository $repository;
    private \DirectoriesBuilderPro\Services\Review_Service $service;
    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Review', 'directories-builder-pro' ),
            'plural'   => __( 'Reviews', 'directories-builder-pro' ),
            'ajax'     => false,
        ] );
        $this->repository = new \DirectoriesBuilderPro\Repositories\Review_Repository();
        $this->service    = new \DirectoriesBuilderPro\Services\Review_Service();
    }
    public function get_columns(): array {
        return [
            'cb'       => '<input type="checkbox" />',
            'reviewer' => __( 'Reviewer', 'directories-builder-pro' ),
            'business' => __( 'Business', 'directories-builder-pro' ),
            'rating'   => __( 'Rating', 'directories-builder-pro' ),
            'excerpt'  => __( 'Review Excerpt', 'directories-builder-pro' ),
            'date'     => __( 'Date', 'directories-builder-pro' ),
            'status'   => __( 'Status', 'directories-builder-pro' ),
        ];
    }
    public function get_sortable_columns(): array {
        return [
            'rating' => [ 'rating', false ],
            'date'   => [ 'created_at', true ],
        ];
    }
    public function prepare_items(): void {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
        $per_page = 20;
        $page     = $this->get_pagenum();
        $status   = sanitize_text_field( $_GET['status'] ?? '' );
        $where = [];
        if ( $status && in_array( $status, [ 'pending', 'approved', 'rejected', 'spam' ], true ) ) {
            $where['status'] = $status;
        }
        $orderby = sanitize_text_field( $_GET['orderby'] ?? 'created_at' );
        $order   = sanitize_text_field( $_GET['order'] ?? 'DESC' );
        $this->items = $this->repository->find( $where, $per_page, ( $page - 1 ) * $per_page, $orderby, $order );
        $total       = $this->repository->count( $where );
        $this->set_pagination_args( [
            'total_items' => $total,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil( $total / $per_page ),
        ] );
    }
    public function column_cb( $item ): string {
        return '<input type="checkbox" name="review_ids[]" value="' . absint( $item['id'] ) . '" />';
    }
    public function column_reviewer( $item ): string {
        $user = get_userdata( (int) $item['user_id'] );
        $name = $user ? esc_html( $user->display_name ) : __( 'Unknown', 'directories-builder-pro' );
        $actions = [
            'approve' => sprintf( '<a href="%s">%s</a>',
                wp_nonce_url( admin_url( "admin-post.php?action=dbp_moderate_review&review_id={$item['id']}&do=approve" ), 'dbp_moderate_' . $item['id'] ),
                __( 'Approve', 'directories-builder-pro' )
            ),
            'reject'  => sprintf( '<a href="%s" class="dbp-reject-link" data-id="%d">%s</a>',
                wp_nonce_url( admin_url( "admin-post.php?action=dbp_moderate_review&review_id={$item['id']}&do=reject" ), 'dbp_moderate_' . $item['id'] ),
                absint( $item['id'] ),
                __( 'Reject', 'directories-builder-pro' )
            ),
            'spam'    => sprintf( '<a href="%s" style="color:#a00;">%s</a>',
                wp_nonce_url( admin_url( "admin-post.php?action=dbp_moderate_review&review_id={$item['id']}&do=spam" ), 'dbp_moderate_' . $item['id'] ),
                __( 'Mark as Spam', 'directories-builder-pro' )
            ),
            'delete'  => sprintf( '<a href="%s" style="color:#a00;" onclick="return confirm(\'%s\');">%s</a>',
                wp_nonce_url( admin_url( "admin-post.php?action=dbp_moderate_review&review_id={$item['id']}&do=delete" ), 'dbp_moderate_' . $item['id'] ),
                esc_js( __( 'Are you sure?', 'directories-builder-pro' ) ),
                __( 'Delete', 'directories-builder-pro' )
            ),
        ];
        return $name . $this->row_actions( $actions );
    }
    public function column_business( $item ): string {
        global $wpdb;
        $table = $wpdb->prefix . 'dbp_businesses';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $biz = $wpdb->get_row( $wpdb->prepare( "SELECT name, wp_post_id FROM {$table} WHERE id = %d", absint( $item['business_id'] ) ), ARRAY_A );
        return $biz ? '<a href="' . esc_url( get_edit_post_link( (int) $biz['wp_post_id'] ) ) . '">' . esc_html( $biz['name'] ) . '</a>' : '—';
    }
    public function column_rating( $item ): string {
        return esc_html( str_repeat( '★', (int) $item['rating'] ) . str_repeat( '☆', 5 - (int) $item['rating'] ) );
    }
    public function column_excerpt( $item ): string {
        return esc_html( mb_substr( $item['content'], 0, 120 ) ) . ( mb_strlen( $item['content'] ) > 120 ? '…' : '' );
    }
    public function column_date( $item ): string {
        return esc_html( dbp_time_ago( $item['created_at'] ) );
    }
    public function column_status( $item ): string {
        $class = 'dbp-status-badge--' . esc_attr( $item['status'] );
        return '<span class="dbp-status-badge ' . $class . '">' . esc_html( ucfirst( $item['status'] ) ) . '</span>';
    }
    protected function get_bulk_actions(): array {
        return [
            'bulk_approve' => __( 'Approve Selected', 'directories-builder-pro' ),
            'bulk_reject'  => __( 'Reject Selected', 'directories-builder-pro' ),
            'bulk_spam'    => __( 'Mark as Spam', 'directories-builder-pro' ),
        ];
    }
    protected function extra_tablenav( $which ): void {
        if ( $which !== 'top' ) return;
        $counts  = $this->repository->get_status_counts();
        $current = sanitize_text_field( $_GET['status'] ?? '' );
        $page    = 'dbp-review-moderation';
        $tabs = [
            ''         => sprintf( __( 'All (%d)', 'directories-builder-pro' ), $counts['all'] ),
            'pending'  => sprintf( __( 'Pending (%d)', 'directories-builder-pro' ), $counts['pending'] ),
            'approved' => sprintf( __( 'Approved (%d)', 'directories-builder-pro' ), $counts['approved'] ),
            'rejected' => sprintf( __( 'Rejected (%d)', 'directories-builder-pro' ), $counts['rejected'] ),
            'spam'     => sprintf( __( 'Spam (%d)', 'directories-builder-pro' ), $counts['spam'] ),
        ];
        echo '<div class="dbp-filter-tabs">';
        foreach ( $tabs as $status => $label ) {
            $class = ( $current === $status ) ? 'current' : '';
            $url   = admin_url( "admin.php?page={$page}" . ( $status ? "&status={$status}" : '' ) );
            echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a> ';
        }
        echo '</div>';
    }
}
// Handle moderation actions.
add_action( 'admin_post_dbp_moderate_review', static function (): void {
    $review_id = absint( $_GET['review_id'] ?? 0 );
    $action    = sanitize_text_field( $_GET['do'] ?? '' );
    check_admin_referer( 'dbp_moderate_' . $review_id );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Permission denied.', 'directories-builder-pro' ) );
    }
    $service = new \DirectoriesBuilderPro\Services\Review_Service();
    match ( $action ) {
        'approve' => $service->approve_review( $review_id ),
        'reject'  => $service->reject_review( $review_id, sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) ) ),
        'spam'    => $service->mark_spam( $review_id ),
        'delete'  => $service->get_repository()->delete( $review_id ),
        default   => null,
    };
    wp_safe_redirect( admin_url( 'admin.php?page=dbp-review-moderation&updated=1' ) );
    exit;
} );
// Handle bulk actions.
add_action( 'admin_post_dbp_bulk_moderate', static function (): void {
    check_admin_referer( 'bulk-reviews' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Permission denied.', 'directories-builder-pro' ) );
    }
    $action = sanitize_text_field( $_POST['action'] ?? $_POST['action2'] ?? '' );
    $ids    = array_map( 'absint', (array) ( $_POST['review_ids'] ?? [] ) );
    $service = new \DirectoriesBuilderPro\Services\Review_Service();
    foreach ( $ids as $id ) {
        match ( $action ) {
            'bulk_approve' => $service->approve_review( $id ),
            'bulk_reject'  => $service->reject_review( $id ),
            'bulk_spam'    => $service->mark_spam( $id ),
            default        => null,
        };
    }
    wp_safe_redirect( admin_url( 'admin.php?page=dbp-review-moderation&updated=1' ) );
    exit;
} );
// Render the page.
$table = new DBP_Review_List_Table();
$table->prepare_items();
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
        <?php $table->display(); ?>
    </form>
</div>
