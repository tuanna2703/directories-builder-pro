<?php
declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PSR-4-style autoloader for Directories Builder Pro.
 *
 * Maps the DirectoriesBuilderPro namespace to plugin directories:
 *   DirectoriesBuilderPro\Core\     → /core/
 *   DirectoriesBuilderPro\Modules\  → /modules/
 *   DirectoriesBuilderPro\          → /includes/
 *
 * @package DirectoriesBuilderPro
 */
spl_autoload_register( static function ( string $class ): void {
    // Only handle our namespace.
    $namespace_prefix = 'DirectoriesBuilderPro\\';

    if ( ! str_starts_with( $class, $namespace_prefix ) ) {
        return;
    }

    // Strip the namespace prefix.
    $relative_class = substr( $class, strlen( $namespace_prefix ) );

    // Namespace-to-directory mapping.
    $namespace_map = [
        'Core\\'    => 'core/',
        'Modules\\' => 'modules/',
    ];

    $base_dir = DBP_PATH;
    $mapped   = false;

    foreach ( $namespace_map as $ns => $dir ) {
        if ( str_starts_with( $relative_class, $ns ) ) {
            $relative_class = substr( $relative_class, strlen( $ns ) );
            $base_dir      .= $dir;
            $mapped         = true;
            break;
        }
    }

    // Default: map to /includes/.
    if ( ! $mapped ) {
        $base_dir .= 'includes/';
    }

    // Convert namespace separators to directory separators.
    $path = str_replace( '\\', '/', $relative_class );

    // Split into directory parts and class name.
    $parts     = explode( '/', $path );
    $class_name = array_pop( $parts );

    // Convert CamelCase class name to kebab-case filename.
    // e.g., ModuleManager → module-manager, Business_Service → business-service.
    $filename = strtolower( preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $class_name ) );
    $filename = str_replace( '_', '-', $filename );

    // Directory parts: convert CamelCase to kebab-case lowercase.
    $dir_parts = array_map( static function ( string $part ): string {
        $converted = strtolower( preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $part ) );
        return str_replace( '_', '-', $converted );
    }, $parts );

    // Build full path.
    $dir_path = ! empty( $dir_parts ) ? implode( '/', $dir_parts ) . '/' : '';
    $file     = $base_dir . $dir_path . $filename . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

// Load global helper functions (not autoloaded since they are not in a namespace class).
require_once DBP_PATH . 'core/helpers/functions.php';
