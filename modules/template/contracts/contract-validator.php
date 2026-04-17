<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Template\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Contract_Validator — Dev-mode template argument validation.
 *
 * Maintains a static registry of expected $args keys per template slug.
 * When WP_DEBUG is enabled, validates that required keys are present before
 * a template is included. Triggers E_USER_NOTICE for missing required args.
 *
 * In production (WP_DEBUG = false), check() is never called — zero cost.
 *
 * @package DirectoriesBuilderPro\Modules\Template\Contracts
 * @since   1.0.0
 */
class Contract_Validator {

    /**
     * Registered contracts.
     *
     * @var array<string, array{required: string[], optional: string[]}>
     */
    private static array $contracts = [];

    /**
     * Register a data contract for a template slug.
     *
     * @param string $slug     Template slug.
     * @param array  $contract Contract definition with 'required' and 'optional' keys.
     * @return void
     */
    public static function register( string $slug, array $contract ): void {
        self::$contracts[ $slug ] = [
            'required' => $contract['required'] ?? [],
            'optional' => $contract['optional'] ?? [],
        ];
    }

    /**
     * Validate $args against the registered contract for a slug.
     *
     * Only runs when WP_DEBUG is true. Triggers E_USER_NOTICE for each
     * missing required key.
     *
     * @param string $slug Template slug.
     * @param array  $args Template arguments to validate.
     * @return void
     */
    public static function check( string $slug, array $args ): void {
        if ( ! isset( self::$contracts[ $slug ] ) ) {
            return;
        }

        $contract = self::$contracts[ $slug ];

        foreach ( $contract['required'] as $key ) {
            if ( ! array_key_exists( $key, $args ) ) {
                trigger_error(
                    sprintf(
                        'DBP Template "%s" missing required arg: "%s"',
                        esc_html( $slug ),
                        esc_html( $key )
                    ),
                    E_USER_NOTICE
                );
            }
        }
    }

    /**
     * Register all built-in template contracts.
     *
     * Called from Template_Module::init().
     *
     * @return void
     */
    public static function register_all(): void {
        // ── Partials ──────────────────────────────────────────────────────
        self::register( 'partials/star-rating', [
            'required' => [ 'rating' ],
            'optional' => [ 'show_number', 'count' ],
        ] );

        self::register( 'partials/badge', [
            'required' => [ 'type' ],
            'optional' => [ 'label' ],
        ] );

        self::register( 'partials/price-label', [
            'required' => [ 'level' ],
            'optional' => [],
        ] );

        self::register( 'partials/avatar', [
            'required' => [ 'user_id' ],
            'optional' => [ 'size', 'alt' ],
        ] );

        self::register( 'partials/button', [
            'required' => [ 'label', 'url' ],
            'optional' => [ 'variant', 'icon', 'target', 'extra_classes' ],
        ] );

        self::register( 'partials/notice', [
            'required' => [ 'message', 'type' ],
            'optional' => [ 'dismissible' ],
        ] );

        self::register( 'partials/pagination', [
            'required' => [ 'total_pages', 'current_page' ],
            'optional' => [ 'base_url', 'query_var' ],
        ] );

        self::register( 'partials/empty-state', [
            'required' => [ 'title' ],
            'optional' => [ 'message', 'icon_class', 'action_label', 'action_url' ],
        ] );

        self::register( 'partials/loading-skeleton', [
            'required' => [],
            'optional' => [ 'count', 'type' ],
        ] );

        // ── Business ──────────────────────────────────────────────────────
        self::register( 'business/card', [
            'required' => [ 'business' ],
            'optional' => [ 'distance', 'show_distance', 'distance_unit' ],
        ] );

        self::register( 'business/header', [
            'required' => [ 'business' ],
            'optional' => [ 'show_claim_button' ],
        ] );

        self::register( 'business/about', [
            'required' => [ 'business' ],
            'optional' => [],
        ] );

        self::register( 'business/single', [
            'required' => [ 'business', 'reviews', 'similar_businesses' ],
            'optional' => [ 'review_form_visible' ],
        ] );

        self::register( 'business/archive', [
            'required' => [],
            'optional' => [ 'initial_results', 'search_args' ],
        ] );

        // ── Reviews ───────────────────────────────────────────────────────
        self::register( 'reviews/item', [
            'required' => [ 'review' ],
            'optional' => [ 'current_user_has_voted', 'is_business_owner' ],
        ] );

        self::register( 'reviews/list', [
            'required' => [ 'reviews', 'business_id' ],
            'optional' => [ 'total', 'current_page', 'orderby' ],
        ] );

        self::register( 'reviews/form', [
            'required' => [ 'business_id' ],
            'optional' => [ 'existing_review' ],
        ] );

        // ── Search ────────────────────────────────────────────────────────
        self::register( 'search/bar', [
            'required' => [],
            'optional' => [ 'default_query', 'default_location' ],
        ] );

        self::register( 'search/results', [
            'required' => [],
            'optional' => [ 'businesses', 'total', 'current_page', 'search_args' ],
        ] );

        // ── Forms ─────────────────────────────────────────────────────────
        self::register( 'forms/form', [
            'required' => [ 'form_name', 'form_title', 'groups' ],
            'optional' => [ 'object_id', 'tabs', 'has_tabs' ],
        ] );

        self::register( 'forms/group', [
            'required' => [ 'group', 'values' ],
            'optional' => [],
        ] );

        self::register( 'forms/field', [
            'required' => [ 'field', 'value' ],
            'optional' => [],
        ] );

        // ── Admin ─────────────────────────────────────────────────────────
        self::register( 'admin/dashboard', [
            'required' => [ 'stats', 'recent_activity' ],
            'optional' => [],
        ] );

        self::register( 'admin/settings', [
            'required' => [ 'form_html' ],
            'optional' => [],
        ] );

        self::register( 'admin/moderation', [
            'required' => [ 'table_html', 'current_status' ],
            'optional' => [],
        ] );

        self::register( 'admin/business-edit', [
            'required' => [ 'form_html', 'post_id' ],
            'optional' => [],
        ] );

        self::register( 'admin/user-profile', [
            'required' => [ 'form_html', 'user_id' ],
            'optional' => [],
        ] );
    }
}
