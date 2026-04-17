<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Template\Loader;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template_Loader — Path resolution with per-request caching.
 *
 * Resolves template slugs to filesystem paths using a three-level search:
 *   Level 1: Child theme  → wp-content/themes/{child}/directories-builder-pro/{slug}.php
 *   Level 2: Parent theme → wp-content/themes/{parent}/directories-builder-pro/{slug}.php
 *   Level 3: Plugin       → wp-content/plugins/directories-builder-pro/templates/{slug}.php
 *
 * Admin slugs (starting with 'admin/') skip theme levels and resolve from plugin only.
 *
 * Caching strategy: A static array caches resolved paths for the duration of the
 * request. Transient/object caching is inappropriate because file paths can change
 * between deployments, theme switches, or plugin updates, and stale path cache
 * would cause missing template errors. A per-request cache avoids repeated
 * file_exists() calls within the same page load (which is the real hot path).
 *
 * @package DirectoriesBuilderPro\Modules\Template\Loader
 * @since   1.0.0
 */
class Template_Loader {

    /**
     * Per-request cache of resolved template paths.
     *
     * Key: slug, Value: resolved path or false (not found).
     *
     * @var array<string, string|false>
     */
    private static array $located_cache = [];

    /**
     * Theme subdirectory name for template overrides.
     *
     * @var string
     */
    private const THEME_DIR = 'directories-builder-pro';

    /**
     * Locate a template file by slug.
     *
     * @param string $slug Template slug (e.g., 'business/card', 'partials/badge').
     * @return string|null Absolute path to the template file, or null if not found.
     */
    public function locate( string $slug ): ?string {
        // Normalize: strip leading/trailing slashes and whitespace.
        $slug = trim( $slug, " \t\n\r\0\x0B/" );

        // Validate slug: reject directory traversal and invalid characters.
        if ( ! $this->is_valid_slug( $slug ) ) {
            trigger_error(
                sprintf( 'DBP Template: invalid slug "%s" — only [a-z0-9/_-] allowed, no directory traversal.', esc_html( $slug ) ),
                E_USER_WARNING
            );
            return null;
        }

        // Return cached result if available.
        if ( isset( self::$located_cache[ $slug ] ) ) {
            $cached = self::$located_cache[ $slug ];
            return $cached === false ? null : $cached;
        }

        // Build candidate filename.
        $candidate = $slug . '.php';

        // Build base search directories (filtered).
        $base_paths = $this->resolve_base_paths( $slug );

        /**
         * Filter the base search directories for a template slug.
         *
         * @param string[] $base_paths Ordered array of directory paths to search.
         * @param string   $slug       The template slug being resolved.
         */
        $base_paths = apply_filters( 'dbp/template/paths', $base_paths, $slug );

        // Build candidate list (allows plugins to add alternative filenames).
        $candidates = [ $candidate ];

        /**
         * Filter the candidate filenames for a template slug.
         *
         * @param string[] $candidates Array of candidate filenames (e.g., ['business/card.php']).
         * @param string   $slug       The template slug being resolved.
         */
        $candidates = apply_filters( 'dbp/template/candidates', $candidates, $slug );

        // Search: first match wins.
        $found_path = null;
        foreach ( $base_paths as $base ) {
            foreach ( $candidates as $file ) {
                $full_path = rtrim( $base, '/' ) . '/' . $file;
                if ( file_exists( $full_path ) ) {
                    $found_path = $full_path;
                    break 2;
                }
            }
        }

        /**
         * Filter the final resolved template path.
         *
         * Allows full override of which file is loaded for a given slug.
         * Return null to indicate "not found."
         *
         * @param string|null $found_path The resolved file path, or null.
         * @param string      $slug       The template slug.
         */
        $found_path = apply_filters( 'dbp/template/locate', $found_path, $slug );

        // Cache the result (including null as false to distinguish from "not yet looked up").
        self::$located_cache[ $slug ] = $found_path ?? false;

        return $found_path;
    }

    /**
     * Validate a template slug.
     *
     * Valid slugs contain only lowercase letters, digits, forward slashes,
     * hyphens, and underscores. Directory traversal sequences (..) are rejected.
     *
     * @param string $slug The slug to validate.
     * @return bool True if valid.
     */
    private function is_valid_slug( string $slug ): bool {
        if ( $slug === '' ) {
            return false;
        }

        // Reject directory traversal.
        if ( preg_match( '/\.\./', $slug ) ) {
            return false;
        }

        // Allow only safe characters.
        if ( ! preg_match( '/^[a-z0-9\/_-]+$/', $slug ) ) {
            return false;
        }

        return true;
    }

    /**
     * Resolve base search directories for a slug.
     *
     * Admin slugs (starting with 'admin/') are resolved from the plugin path only
     * to prevent themes from overriding admin output.
     *
     * @param string $slug The template slug.
     * @return string[] Ordered array of base directory paths.
     */
    private function resolve_base_paths( string $slug ): array {
        // Admin templates: plugin-only resolution (no theme override).
        if ( str_starts_with( $slug, 'admin/' ) ) {
            return [
                DBP_PATH . 'templates/',
            ];
        }

        // Frontend + partials: child theme → parent theme → plugin.
        $paths = [];

        // Level 1: Child theme (only if child theme is active).
        $child_dir = get_stylesheet_directory() . '/' . self::THEME_DIR . '/';
        $paths[]   = $child_dir;

        // Level 2: Parent theme (only added if different from child).
        $parent_dir = get_template_directory() . '/' . self::THEME_DIR . '/';
        if ( $parent_dir !== $child_dir ) {
            $paths[] = $parent_dir;
        }

        // Level 3: Plugin default.
        $paths[] = DBP_PATH . 'templates/';

        return $paths;
    }

    /**
     * Flush the per-request path cache.
     *
     * Called in unit tests and after plugin updates to ensure fresh lookups.
     *
     * @return void
     */
    public static function flush_cache(): void {
        self::$located_cache = [];
    }
}
