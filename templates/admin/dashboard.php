<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Dashboard Template
 *
 * @var array $args Contains 'stats' and 'recent_activity'.
 */
$stats          = $args['stats'] ?? [];
$recent_reviews = $args['recent_activity'] ?? [];
?>
<div class="wrap dbp-admin-dashboard">
    <h1 class="dbp-admin-title"><?php esc_html_e( 'Directories Builder Pro', 'directories-builder-pro' ); ?></h1>
    <!-- Stats Cards -->
    <div class="dbp-stats-grid">
        <div class="dbp-stat-card">
            <div class="dbp-stat-card__icon dbp-stat-card__icon--businesses">
                <svg viewBox="0 0 24 24" width="28" height="28"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z" fill="currentColor"/></svg>
            </div>
            <div class="dbp-stat-card__data">
                <span class="dbp-stat-card__number"><?php echo esc_html( (string) ( $stats['total_businesses'] ?? 0 ) ); ?></span>
                <span class="dbp-stat-card__label"><?php esc_html_e( 'Total Businesses', 'directories-builder-pro' ); ?></span>
            </div>
        </div>
        <div class="dbp-stat-card">
            <div class="dbp-stat-card__icon dbp-stat-card__icon--reviews">
                <svg viewBox="0 0 24 24" width="28" height="28"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="currentColor"/></svg>
            </div>
            <div class="dbp-stat-card__data">
                <span class="dbp-stat-card__number"><?php echo esc_html( (string) ( $stats['reviews_this_week'] ?? 0 ) ); ?></span>
                <span class="dbp-stat-card__label"><?php esc_html_e( 'Reviews This Week', 'directories-builder-pro' ); ?></span>
            </div>
        </div>
        <div class="dbp-stat-card">
            <div class="dbp-stat-card__icon dbp-stat-card__icon--claims">
                <svg viewBox="0 0 24 24" width="28" height="28"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z" fill="currentColor"/></svg>
            </div>
            <div class="dbp-stat-card__data">
                <span class="dbp-stat-card__number"><?php echo esc_html( (string) ( $stats['pending_claims'] ?? 0 ) ); ?></span>
                <span class="dbp-stat-card__label"><?php esc_html_e( 'Pending Claims', 'directories-builder-pro' ); ?></span>
            </div>
        </div>
        <div class="dbp-stat-card">
            <div class="dbp-stat-card__icon dbp-stat-card__icon--pending">
                <svg viewBox="0 0 24 24" width="28" height="28"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/></svg>
            </div>
            <div class="dbp-stat-card__data">
                <span class="dbp-stat-card__number"><?php echo esc_html( (string) ( $stats['pending_reviews'] ?? 0 ) ); ?></span>
                <span class="dbp-stat-card__label"><?php esc_html_e( 'Pending Reviews', 'directories-builder-pro' ); ?></span>
            </div>
        </div>
    </div>
    <!-- Quick Links -->
    <div class="dbp-quick-links">
        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=dbp_business' ) ); ?>" class="dbp-quick-link">
            <?php esc_html_e( '+ Add Business', 'directories-builder-pro' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=dbp-review-moderation' ) ); ?>" class="dbp-quick-link">
            <?php esc_html_e( 'Moderate Reviews', 'directories-builder-pro' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=dbp-settings' ) ); ?>" class="dbp-quick-link">
            <?php esc_html_e( 'Settings', 'directories-builder-pro' ); ?>
        </a>
    </div>
    <!-- Recent Activity -->
    <div class="dbp-recent-activity">
        <h2><?php esc_html_e( 'Recent Activity', 'directories-builder-pro' ); ?></h2>
        <?php if ( empty( $recent_reviews ) ) : ?>
            <p class="dbp-empty-state"><?php esc_html_e( 'No recent activity.', 'directories-builder-pro' ); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Reviewer', 'directories-builder-pro' ); ?></th>
                        <th><?php esc_html_e( 'Business', 'directories-builder-pro' ); ?></th>
                        <th><?php esc_html_e( 'Rating', 'directories-builder-pro' ); ?></th>
                        <th><?php esc_html_e( 'Date', 'directories-builder-pro' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'directories-builder-pro' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $recent_reviews as $review ) :
                        $reviewer = get_userdata( (int) $review['user_id'] );
                        $status_class = 'dbp-status-badge--' . esc_attr( $review['status'] );
                        ?>
                        <tr>
                            <td><?php echo esc_html( $reviewer ? $reviewer->display_name : __( 'Unknown', 'directories-builder-pro' ) ); ?></td>
                            <td><?php echo esc_html( $review['business_name'] ?? '' ); ?></td>
                            <td><?php echo esc_html( str_repeat( '★', (int) $review['rating'] ) ); ?></td>
                            <td><?php echo esc_html( dbp_time_ago( $review['created_at'] ) ); ?></td>
                            <td><span class="dbp-status-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( ucfirst( $review['status'] ) ); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
