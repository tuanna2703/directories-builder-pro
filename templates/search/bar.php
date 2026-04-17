<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Search Bar
 *
 * Dual-field search with autosuggest, geolocation button, and search action.
 *
 * @slug     search/bar
 * @version  1.0.0
 *
 * @args optional: default_query (string) — pre-fill the What field
 *                 default_location (string) — pre-fill the Where field
 *
 * @package DirectoriesBuilderPro\Templates\Search
 */
$default_query    = $args['default_query'] ?? sanitize_text_field( $_GET['q'] ?? '' );
$default_location = $args['default_location'] ?? sanitize_text_field( $_GET['location'] ?? '' );
?>
<div class="dbp-search-bar" id="dbp-search-bar" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dbp_nonce' ) ); ?>">
    <div class="dbp-search-bar__inner">
        <!-- What? field -->
        <div class="dbp-search-bar__field dbp-search-bar__field--what">
            <label for="dbp-search-what" class="dbp-search-bar__label">
                <?php esc_html_e( 'What?', 'directories-builder-pro' ); ?>
            </label>
            <input type="text"
                   id="dbp-search-what"
                   class="dbp-search-bar__input"
                   placeholder="<?php esc_attr_e( 'Restaurants, plumbers, dentists…', 'directories-builder-pro' ); ?>"
                   autocomplete="off"
                   aria-autocomplete="list"
                   aria-controls="dbp-suggest-what"
                   value="<?php echo esc_attr( $default_query ); ?>">
            <div class="dbp-suggest" id="dbp-suggest-what" role="listbox" style="display:none;"></div>
        </div>

        <!-- Where? field -->
        <div class="dbp-search-bar__field dbp-search-bar__field--where">
            <label for="dbp-search-where" class="dbp-search-bar__label">
                <?php esc_html_e( 'Where?', 'directories-builder-pro' ); ?>
            </label>
            <div class="dbp-search-bar__where-wrapper">
                <input type="text"
                       id="dbp-search-where"
                       class="dbp-search-bar__input"
                       placeholder="<?php esc_attr_e( 'City, neighborhood, or zip', 'directories-builder-pro' ); ?>"
                       autocomplete="off"
                       value="<?php echo esc_attr( $default_location ); ?>">
                <button type="button"
                        class="dbp-search-bar__geo-btn"
                        id="dbp-geo-btn"
                        title="<?php esc_attr_e( 'Use my location', 'directories-builder-pro' ); ?>"
                        aria-label="<?php esc_attr_e( 'Use my current location', 'directories-builder-pro' ); ?>">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
            <div class="dbp-suggest" id="dbp-suggest-where" role="listbox" style="display:none;"></div>
        </div>

        <!-- Search button -->
        <button type="button" class="dbp-btn dbp-btn--primary dbp-search-bar__submit" id="dbp-search-submit">
            <svg viewBox="0 0 24 24" width="20" height="20">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" fill="currentColor"/>
            </svg>
            <span class="dbp-search-bar__submit-text"><?php esc_html_e( 'Search', 'directories-builder-pro' ); ?></span>
        </button>
    </div>

    <!-- Hidden lat/lng for geolocation -->
    <input type="hidden" id="dbp-search-lat" value="">
    <input type="hidden" id="dbp-search-lng" value="">
</div>
