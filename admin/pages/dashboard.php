<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Page: Dashboard
 *
 * Stats cards, recent activity, quick links.
 *
 * @package DirectoriesBuilderPro\Admin\Pages
 */
global $wpdb;
$businesses_table = $wpdb->prefix . 'dbp_businesses';
$reviews_table    = $wpdb->prefix . 'dbp_reviews';
$claims_table     = $wpdb->prefix . 'dbp_claims';
// Stats.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$total_businesses = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$businesses_table} WHERE status = 'active'" );
$reviews_this_week = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM {$reviews_table} WHERE status = 'approved' AND created_at >= %s",
    gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
) );
$pending_claims = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$claims_table} WHERE status = 'pending'" );
$pending_reviews = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reviews_table} WHERE status = 'pending'" );
// Recent activity.
$recent_reviews = $wpdb->get_results(
    "SELECT r.*, b.name as business_name
     FROM {$reviews_table} r
     LEFT JOIN {$businesses_table} b ON r.business_id = b.id
     ORDER BY r.created_at DESC
     LIMIT 10",
    ARRAY_A
);
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$stats = [
    'total_businesses'  => $total_businesses,
    'reviews_this_week' => $reviews_this_week,
    'pending_claims'    => $pending_claims,
    'pending_reviews'   => $pending_reviews,
];

dbp_template( 'admin/dashboard', [
    'stats'           => $stats,
    'recent_activity' => $recent_reviews,
] );
