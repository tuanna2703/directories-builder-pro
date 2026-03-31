<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Managers;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Asset_Manager class.
 *
 * Handles conditional enqueuing of CSS/JS for both frontend and admin.
 * Localizes JavaScript with plugin data via dbpData object.
 *
 * @package DirectoriesBuilderPro\Core\Managers
 */
class Asset_Manager {
    /**
     * Enqueue frontend assets.
     *
     * Hooked to wp_enqueue_scripts.
     *
     * @return void
     */
    public function enqueue_frontend(): void {
        // Frontend CSS.
        wp_enqueue_style(
            'dbp-frontend',
            DBP_URL . 'assets/css/frontend.css',
            [],
            DBP_VERSION
        );
        // Frontend JS.
        wp_enqueue_script(
            'dbp-frontend',
            DBP_URL . 'assets/js/frontend.js',
            [],
            DBP_VERSION,
            true
        );
        // Localize script data.
        wp_localize_script( 'dbp-frontend', 'dbpData', $this->get_localized_data() );
        // Conditionally enqueue Google Maps JS.
        if ( $this->should_enqueue_maps() ) {
            $this->enqueue_google_maps();
        }
    }
    /**
     * Enqueue admin assets.
     *
     * Hooked to admin_enqueue_scripts.
     *
     * @param string $hook_suffix The current admin page hook.
     * @return void
     */
    public function enqueue_admin( string $hook_suffix ): void {
        $screen = get_current_screen();
        // Only load on our plugin pages and the dbp_business post type.
        $is_plugin_page = (
            str_contains( $hook_suffix, 'dbp-' ) ||
            ( $screen && $screen->post_type === 'dbp_business' )
        );
        if ( ! $is_plugin_page ) {
            return;
        }
        // Admin CSS.
        wp_enqueue_style(
            'dbp-admin',
            DBP_URL . 'assets/css/admin.css',
            [],
            DBP_VERSION
        );
        // Admin JS.
        wp_enqueue_script(
            'dbp-admin',
            DBP_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            DBP_VERSION,
            true
        );
        // Localize admin script data.
        wp_localize_script( 'dbp-admin', 'dbpData', $this->get_localized_data() );
        // Enqueue Google Maps on business edit screens.
        if ( $screen && $screen->post_type === 'dbp_business' && in_array( $screen->base, [ 'post', 'post-new' ], true ) ) {
            $this->enqueue_google_maps();
        }
    }
    /**
     * Get the localized data array passed to JavaScript.
     *
     * @return array
     */
    private function get_localized_data(): array {
        return [
            'ajaxurl'      => admin_url( 'admin-ajax.php' ),
            'restUrl'      => rest_url( 'directories-builder-pro/v1/' ),
            'nonce'        => wp_create_nonce( 'dbp_nonce' ),
            'restNonce'    => wp_create_nonce( 'wp_rest' ),
            'pluginUrl'    => DBP_URL,
            'mapsKey'      => get_option( 'dbp_google_maps_key', '' ),
            'distanceUnit' => get_option( 'dbp_distance_unit', 'km' ),
            'minReviewLen' => (int) get_option( 'dbp_min_review_length', 25 ),
            'maxPhotos'    => (int) get_option( 'dbp_max_photos_per_review', 5 ),
            'resultsPerPage' => (int) get_option( 'dbp_results_per_page', 12 ),
            'i18n'         => [
                'error'          => __( 'An error occurred. Please try again.', 'directories-builder-pro' ),
                'confirmDelete'  => __( 'Are you sure you want to delete this?', 'directories-builder-pro' ),
                'loading'        => __( 'Loading...', 'directories-builder-pro' ),
                'noResults'      => __( 'No results found.', 'directories-builder-pro' ),
                'reviewSuccess'  => __( 'Your review has been submitted successfully!', 'directories-builder-pro' ),
                'voteRecorded'   => __( 'Your vote has been recorded.', 'directories-builder-pro' ),
                'flagSent'       => __( 'Thank you for your report.', 'directories-builder-pro' ),
                'fileTooLarge'   => __( 'File is too large. Maximum size is 5MB.', 'directories-builder-pro' ),
                'tooManyFiles'   => __( 'Maximum 5 photos allowed.', 'directories-builder-pro' ),
                'invalidFileType' => __( 'Only image files are allowed.', 'directories-builder-pro' ),
                'minChars'       => __( 'Minimum %d characters required.', 'directories-builder-pro' ),
                'selectRating'   => __( 'Please select a rating.', 'directories-builder-pro' ),
            ],
        ];
    }
    /**
     * Whether to enqueue Google Maps on the current page.
     *
     * @return bool
     */
    private function should_enqueue_maps(): bool {
        $maps_key = get_option( 'dbp_google_maps_key', '' );
        if ( empty( $maps_key ) ) {
            return false;
        }
        // Single business page.
        if ( is_singular( 'dbp_business' ) ) {
            return true;
        }
        // Business archive.
        if ( is_post_type_archive( 'dbp_business' ) ) {
            return true;
        }
        // Check if current page has search results shortcode.
        global $post;
        if ( $post && has_shortcode( $post->post_content ?? '', 'dbp_search_results' ) ) {
            return true;
        }
        return false;
    }
    /**
     * Enqueue Google Maps JavaScript API.
     *
     * @return void
     */
    private function enqueue_google_maps(): void {
        $maps_key = get_option( 'dbp_google_maps_key', '' );
        if ( empty( $maps_key ) ) {
            return;
        }
        wp_enqueue_script(
            'google-maps',
            'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $maps_key ) . '&libraries=places&callback=Function.prototype',
            [],
            null, // External script, no version.
            true
        );
        // Enqueue marker clusterer.
        wp_enqueue_script(
            'dbp-marker-clusterer',
            DBP_URL . 'assets/lib/marker-clusterer.js',
            [ 'google-maps' ],
            DBP_VERSION,
            true
        );
    }
}