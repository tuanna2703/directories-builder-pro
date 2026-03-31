<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Core\Database;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Migrations class.
 *
 * Runs dbDelta() on all schema definitions and tracks the database version.
 *
 * @package DirectoriesBuilderPro\Core\Database
 */
class Migrations {

    /**
     * Run all database migrations via dbDelta.
     *
     * @return void
     */
    public static function run(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $tables = Schema::get_tables();

        foreach ( $tables as $sql ) {
            dbDelta( $sql );
        }

        update_option( 'dbp_db_version', DBP_VERSION );
    }

    /**
     * Check if a database upgrade is needed.
     *
     * @return bool
     */
    public static function needs_upgrade(): bool {
        $current = get_option( 'dbp_db_version', '0.0.0' );
        return version_compare( $current, DBP_VERSION, '<' );
    }

    /**
     * Placeholder for uninstall hook registration.
     * Actual uninstall logic is in uninstall.php.
     *
     * @return void
     */
    public static function uninstall_placeholder(): void {
        // The uninstall.php file handles cleanup.
        // This method exists solely for register_uninstall_hook compatibility.
    }
}
