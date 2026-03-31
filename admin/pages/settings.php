<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin Page: Settings
 *
 * Plugin configuration using WordPress Settings API.
 *
 * @package DirectoriesBuilderPro\Admin\Pages
 */
// Register settings on admin_init.
add_action( 'admin_init', static function (): void {
    // ── Maps Section ──
    add_settings_section( 'dbp_maps_section', __( 'Maps', 'directories-builder-pro' ), static function (): void {
        echo '<p>' . esc_html__( 'Configure Google Maps integration.', 'directories-builder-pro' ) . '</p>';
    }, 'dbp-settings' );
    register_setting( 'dbp_settings', 'dbp_google_maps_key', [
        'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '',
    ] );
    add_settings_field( 'dbp_google_maps_key', __( 'Google Maps API Key', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_google_maps_key', '' );
        echo '<input type="text" name="dbp_google_maps_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Required for map features and geolocation.', 'directories-builder-pro' ) . '</p>';
    }, 'dbp-settings', 'dbp_maps_section' );
    // ── Reviews Section ──
    add_settings_section( 'dbp_reviews_section', __( 'Reviews', 'directories-builder-pro' ), static function (): void {
        echo '<p>' . esc_html__( 'Review submission and moderation settings.', 'directories-builder-pro' ) . '</p>';
    }, 'dbp-settings' );
    register_setting( 'dbp_settings', 'dbp_moderation_mode', [
        'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'manual',
    ] );
    add_settings_field( 'dbp_moderation_mode', __( 'Moderation Mode', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_moderation_mode', 'manual' );
        echo '<label><input type="radio" name="dbp_moderation_mode" value="auto_approve" ' . checked( $value, 'auto_approve', false ) . ' /> ' . esc_html__( 'Auto-approve (reviews go live immediately)', 'directories-builder-pro' ) . '</label><br>';
        echo '<label><input type="radio" name="dbp_moderation_mode" value="manual" ' . checked( $value, 'manual', false ) . ' /> ' . esc_html__( 'Manual (reviews require admin approval)', 'directories-builder-pro' ) . '</label>';
    }, 'dbp-settings', 'dbp_reviews_section' );
    register_setting( 'dbp_settings', 'dbp_min_review_length', [
        'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 25,
    ] );
    add_settings_field( 'dbp_min_review_length', __( 'Minimum Review Length', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_min_review_length', 25 );
        echo '<input type="number" name="dbp_min_review_length" value="' . esc_attr( (string) $value ) . '" min="10" max="500" class="small-text" /> ';
        echo esc_html__( 'characters', 'directories-builder-pro' );
    }, 'dbp-settings', 'dbp_reviews_section' );
    register_setting( 'dbp_settings', 'dbp_max_photos_per_review', [
        'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 5,
    ] );
    add_settings_field( 'dbp_max_photos_per_review', __( 'Max Photos Per Review', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_max_photos_per_review', 5 );
        echo '<input type="number" name="dbp_max_photos_per_review" value="' . esc_attr( (string) $value ) . '" min="0" max="20" class="small-text" />';
    }, 'dbp-settings', 'dbp_reviews_section' );
    // ── Search Section ──
    add_settings_section( 'dbp_search_section', __( 'Search', 'directories-builder-pro' ), static function (): void {
        echo '<p>' . esc_html__( 'Default search parameters.', 'directories-builder-pro' ) . '</p>';
    }, 'dbp-settings' );
    register_setting( 'dbp_settings', 'dbp_default_radius_km', [
        'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 10,
    ] );
    add_settings_field( 'dbp_default_radius_km', __( 'Default Search Radius', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_default_radius_km', 10 );
        echo '<input type="number" name="dbp_default_radius_km" value="' . esc_attr( (string) $value ) . '" min="1" max="100" class="small-text" /> km';
    }, 'dbp-settings', 'dbp_search_section' );
    register_setting( 'dbp_settings', 'dbp_results_per_page', [
        'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 12,
    ] );
    add_settings_field( 'dbp_results_per_page', __( 'Results Per Page', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_results_per_page', 12 );
        echo '<input type="number" name="dbp_results_per_page" value="' . esc_attr( (string) $value ) . '" min="4" max="50" class="small-text" />';
    }, 'dbp-settings', 'dbp_search_section' );
    register_setting( 'dbp_settings', 'dbp_distance_unit', [
        'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'km',
    ] );
    add_settings_field( 'dbp_distance_unit', __( 'Distance Unit', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_distance_unit', 'km' );
        echo '<label><input type="radio" name="dbp_distance_unit" value="km" ' . checked( $value, 'km', false ) . ' /> ' . esc_html__( 'Kilometers', 'directories-builder-pro' ) . '</label> ';
        echo '<label><input type="radio" name="dbp_distance_unit" value="miles" ' . checked( $value, 'miles', false ) . ' /> ' . esc_html__( 'Miles', 'directories-builder-pro' ) . '</label>';
    }, 'dbp-settings', 'dbp_search_section' );
    // ── Business Section ──
    add_settings_section( 'dbp_business_section', __( 'Business', 'directories-builder-pro' ), null, 'dbp-settings' );
    register_setting( 'dbp_settings', 'dbp_allow_user_submissions', [
        'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false,
    ] );
    add_settings_field( 'dbp_allow_user_submissions', __( 'Allow User Submissions', 'directories-builder-pro' ), static function (): void {
        $value = get_option( 'dbp_allow_user_submissions', false );
        echo '<label><input type="checkbox" name="dbp_allow_user_submissions" value="1" ' . checked( $value, true, false ) . ' /> ';
        echo esc_html__( 'Allow registered users to submit new business listings.', 'directories-builder-pro' ) . '</label>';
    }, 'dbp-settings', 'dbp_business_section' );
} );
?>
<div class="wrap dbp-admin-settings">
    <h1><?php esc_html_e( 'Directory Settings', 'directories-builder-pro' ); ?></h1>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'dbp_settings' );
        do_settings_sections( 'dbp-settings' );
        submit_button();
        ?>
    </form>
</div>
