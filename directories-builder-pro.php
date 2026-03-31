<?php
declare(strict_types=1);
/**
 * Plugin Name: Directories Builder Pro
 * Plugin URI:  https://example.com/directories-builder-pro
 * Description: A Yelp-like local business discovery and review platform built with an Elementor-inspired modular architecture.
 * Version:     1.0.0
 * Author:      Directories Builder Pro Team
 * Author URI:  https://example.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: directories-builder-pro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'DBP_VERSION', '1.0.0' );
define( 'DBP_PATH', plugin_dir_path( __FILE__ ) );
define( 'DBP_URL', plugin_dir_url( __FILE__ ) );
define( 'DBP_BASENAME', plugin_basename( __FILE__ ) );

// Load autoloader.
require_once DBP_PATH . 'includes/autoloader.php';

// Boot plugin on plugins_loaded.
add_action( 'plugins_loaded', static function (): void {
    \DirectoriesBuilderPro\Plugin::instance();
} );

// Activation: run database migrations.
register_activation_hook( __FILE__, static function (): void {
    require_once DBP_PATH . 'includes/autoloader.php';
    \DirectoriesBuilderPro\Core\Database\Migrations::run();
    flush_rewrite_rules();
} );

// Deactivation: flush rewrite rules.
register_deactivation_hook( __FILE__, static function (): void {
    flush_rewrite_rules();
} );

// Uninstall: delegate to uninstall.php (handled by WordPress automatically).
register_uninstall_hook( __FILE__, [ 'DirectoriesBuilderPro\\Core\\Database\\Migrations', 'uninstall_placeholder' ] );
