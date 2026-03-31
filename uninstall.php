<?php
declare(strict_types=1);
/**
 * Uninstall script for Directories Builder Pro.
 *
 * Drops all custom tables and deletes all plugin options.
 * This file is called by WordPress when the plugin is deleted
 * through the admin interface.
 *
 * @package DirectoriesBuilderPro
 */

// Prevent direct access.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop all custom tables.
$tables = [
    'dbp_businesses',
    'dbp_business_meta',
    'dbp_reviews',
    'dbp_review_votes',
    'dbp_claims',
    'dbp_checkins',
];

foreach ( $tables as $table ) {
    $table_name = $wpdb->prefix . $table;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}

// Delete all plugin options.
delete_option( 'dbp_db_version' );
delete_option( 'dbp_google_maps_key' );
delete_option( 'dbp_moderation_mode' );
delete_option( 'dbp_min_review_length' );
delete_option( 'dbp_max_photos_per_review' );
delete_option( 'dbp_default_radius_km' );
delete_option( 'dbp_results_per_page' );
delete_option( 'dbp_distance_unit' );
delete_option( 'dbp_allow_user_submissions' );

// Flush rewrite rules.
flush_rewrite_rules();
