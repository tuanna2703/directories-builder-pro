<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Template;

use DirectoriesBuilderPro\Core\Base\Module_Base;
use DirectoriesBuilderPro\Modules\Template\Loader\Template_Loader;
use DirectoriesBuilderPro\Modules\Template\Renderer\Template_Renderer;
use DirectoriesBuilderPro\Modules\Template\Contracts\Contract_Validator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ARCHITECTURE DOCUMENT — Template Module
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 * 1. TEMPLATE TYPE TAXONOMY
 *
 *    a) Frontend Templates (business/, reviews/, search/)
 *       - Theme-aware: inherit typography and colors from the active theme.
 *       - Override-able by child theme and parent theme.
 *       - Strict $args data contract per template — all data injected, no DB queries.
 *       - All dynamic output escaped in context (esc_html, esc_attr, esc_url).
 *
 *    b) Admin Templates (admin/)
 *       - Follow core WordPress admin UI patterns (WP_List_Table, metaboxes, notices).
 *       - NOT override-able by themes — admin output must stay stable and consistent.
 *       - Resolved from plugin path only (Level 3).
 *       - Modular and reusable across plugin admin pages.
 *
 *    c) Partial Templates (partials/)
 *       - Narrow, well-defined inputs ($label, $url, $variant, etc.).
 *       - Shared by both frontend and admin contexts.
 *       - Override-able when used in frontend context; admin templates should use
 *         them only for internal consistency (theme overrides still apply).
 *       - Must never contain heavy plugin logic — purely presentational.
 *
 * 2. RESOLUTION STRATEGY
 *
 *    Three-level search order for frontend and partial slugs:
 *      Level 1: wp-content/themes/{child-theme}/directories-builder-pro/{slug}.php
 *               → Allows child themes to customize plugin output without modifying
 *                 the parent theme or plugin files.
 *      Level 2: wp-content/themes/{parent-theme}/directories-builder-pro/{slug}.php
 *               → Allows theme authors to ship pre-designed plugin templates that
 *                 match their theme's design language.
 *      Level 3: wp-content/plugins/directories-builder-pro/templates/{slug}.php
 *               → The plugin's built-in default templates. Always the final fallback.
 *
 *    Admin templates (slug starts with 'admin/'): Skip Levels 1–2, resolve Level 3 only.
 *    This prevents themes from breaking or spoofing admin interfaces.
 *
 * 3. CACHING STRATEGY
 *
 *    A static per-request cache: private static array $located_cache = []
 *    stored in Template_Loader. Key = slug, Value = path or false.
 *
 *    Transient/object caching is NOT appropriate because:
 *      - File paths can change between deployments (updates, theme switches).
 *      - Stale cached paths would cause silent template-not-found errors.
 *      - The cost being avoided is repeated file_exists() within a single request,
 *        which is already fast on modern filesystems.
 *      - A per-request cache eliminates 95%+ of redundant lookups (e.g., business/card
 *        rendered 12 times in search results) without any staleness risk.
 *
 *    Cache must be bypassed/flushed:
 *      - In unit tests: call Template_Loader::flush_cache() in setUp().
 *      - On plugin update: flush is automatic (new request = empty cache).
 *
 * 4. DATA CONTRACT PATTERN
 *
 *    Each template declares its expected $args in a docblock at the top of the file:
 *      @args required: business (array)
 *      @args optional: show_distance (bool, default true)
 *
 *    Contract_Validator maintains a static registry mapping slug → {required, optional}.
 *    In dev mode (WP_DEBUG = true), the renderer calls Contract_Validator::check()
 *    before include, which triggers E_USER_NOTICE for each missing required key.
 *    In production, check() is never called — zero performance cost.
 *
 * 5. HOOKS SPECIFICATION (fired in order for a single render() call)
 *
 *    Filter:  dbp/template/paths           — alter base search directories
 *    Filter:  dbp/template/candidates      — alter candidate filenames per slug
 *    Filter:  dbp/template/locate          — override the final resolved path
 *    Filter:  dbp/template/args            — modify $args before template include
 *    Action:  dbp/template/before          — fires before include (slug, path, args)
 *    Action:  dbp/template/before/{slug}   — slug-specific before hook
 *    Action:  dbp/template/after           — fires after include (slug, path, args)
 *    Action:  dbp/template/after/{slug}    — slug-specific after hook
 *    Action:  dbp/template/missing         — fires when no file is found (slug, args)
 *
 * 6. MIGRATION PLAN
 *
 *    File                                          | Template Slug(s)         | $args
 *    ----------------------------------------------|--------------------------|-------------------------
 *    public/templates/single-business.php          | business/single          | business, reviews, similar_businesses
 *    public/templates/archive-business.php         | business/archive         | initial_results, search_args
 *    public/partials/business-card.php             | business/card            | business
 *    public/partials/review-item.php               | reviews/item             | review
 *    modules/reviews/templates/review-list.php     | reviews/list             | reviews, business_id, total
 *    modules/reviews/templates/review-form.php     | reviews/form             | business_id
 *    modules/search/templates/search-bar.php       | search/bar               | default_query, default_location
 *    modules/search/templates/search-results.php   | search/results           | businesses, total, current_page
 *    modules/business/templates/business-header.php| business/header          | business
 *    modules/business/templates/business-about.php | business/about           | business
 *    admin/pages/dashboard.php                     | admin/dashboard          | stats, recent_activity
 *    admin/pages/settings.php                      | admin/settings           | form_html
 *    admin/views/business-edit.php                 | admin/business-edit      | form_html, post_id
 *    admin/views/review-moderation.php             | admin/moderation         | table_html, current_status
 *    admin/pages/user-profile.php                  | admin/user-profile       | form_html, user_id
 *
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */

/**
 * Template_Module — Entry point for the Template rendering system.
 *
 * Provides a centralized, theme-overridable template rendering layer for the
 * entire plugin. Replaces scattered inline HTML with a clean API:
 *   dbp_template('business/card', $args)
 *
 * @package DirectoriesBuilderPro\Modules\Template
 * @since   1.0.0
 */
class Template_Module extends Module_Base {

    /**
     * Singleton-like reference for the static render() facade.
     *
     * @var Template_Renderer|null
     */
    private static ?Template_Renderer $renderer_instance = null;

    /**
     * Get the unique module name.
     *
     * @return string
     */
    public function get_name(): string {
        return 'template';
    }

    /**
     * Initialize the Template Module.
     *
     * @return void
     */
    protected function init(): void {
        $manager = \DirectoriesBuilderPro\Plugin::instance()->get_template_manager();
        
        // Store renderer for static facade.
        self::$renderer_instance = $manager->get_renderer();

        // Register shortcodes.
        add_shortcode( 'dbp_search_bar', [ $this, 'shortcode_search_bar' ] );
        add_shortcode( 'dbp_search_results', [ $this, 'shortcode_search_results' ] );
        add_shortcode( 'dbp_review_form', [ $this, 'shortcode_review_form' ] );

        // Register the global helper function.
        $this->register_global_function();
    }

    /**
     * Shortcode: [dbp_search_bar]
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered HTML.
     */
    public function shortcode_search_bar( $atts ): string {
        $atts = shortcode_atts( [
            'query'    => '',
            'location' => '',
        ], $atts, 'dbp_search_bar' );

        return self::render( 'search/bar', [
            'default_query'    => sanitize_text_field( $atts['query'] ),
            'default_location' => sanitize_text_field( $atts['location'] ),
        ], false );
    }

    /**
     * Shortcode: [dbp_search_results]
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered HTML.
     */
    public function shortcode_search_results( $atts ): string {
        $atts = shortcode_atts( [], $atts, 'dbp_search_results' );

        return self::render( 'search/results', [], false );
    }

    /**
     * Shortcode: [dbp_review_form id="X"]
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered HTML.
     */
    public function shortcode_review_form( $atts ): string {
        $atts = shortcode_atts( [
            'id' => 0,
        ], $atts, 'dbp_review_form' );

        $business_id = absint( $atts['id'] );
        if ( $business_id === 0 ) {
            return '';
        }

        return self::render( 'reviews/form', [
            'business_id' => $business_id,
        ], false );
    }

    /**
     * Static facade for template rendering.
     *
     * Mirrors the WooCommerce wc_get_template() pattern for convenience.
     * Can be called as Template_Module::render('business/card', $args).
     *
     * @param string $slug Template slug.
     * @param array  $args Template arguments.
     * @param bool   $echo Whether to echo (true) or return (false).
     * @return string Rendered HTML.
     */
    public static function render( string $slug, array $args = [], bool $echo = true ): string {
        if ( self::$renderer_instance === null ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                trigger_error(
                    'DBP Template_Module::render() called before module initialization.',
                    E_USER_WARNING
                );
            }
            return '';
        }

        return self::$renderer_instance->render( $slug, $args, $echo );
    }

    /**
     * Register the global dbp_template() helper function.
     *
     * @return void
     */
    private function register_global_function(): void {
        if ( ! function_exists( 'dbp_template' ) ) {
            /**
             * Render a plugin template by slug.
             *
             * Global convenience function that delegates to Template_Module::render().
             * Can be called from any file in the plugin:
             *   dbp_template('partials/badge', ['type' => 'claimed']);
             *
             * @param string $slug Template slug (e.g., 'business/card', 'partials/star-rating').
             * @param array  $args Data to pass to the template.
             * @param bool   $echo Whether to echo (true) or return (false) the output.
             * @return string The rendered HTML.
             */
            function dbp_template( string $slug, array $args = [], bool $echo = true ): string {
                return Template_Module::render( $slug, $args, $echo );
            }
        }
    }

}
