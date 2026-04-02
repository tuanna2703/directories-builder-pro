<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Forms;
use DirectoriesBuilderPro\Core\Base\Form_Base;
use DirectoriesBuilderPro\Core\Fields\Fields_Manager;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Plugin_Settings_Form class.
 *
 * Replaces the WordPress Settings API implementation in admin/pages/settings.php.
 *
 * @package DirectoriesBuilderPro\Modules\Form\Forms
 */
class Plugin_Settings_Form extends Form_Base {
    public function get_name(): string {
        return 'plugin_settings';
    }
    public function get_title(): string {
        return __( 'Plugin Settings', 'directories-builder-pro' );
    }
    public function get_storage_type(): string {
        return 'options';
    }
    protected function register_fields(): void {
        // ── Maps (General tab) ──
        $this->start_group( 'maps', [
            'label' => __( 'Maps', 'directories-builder-pro' ),
            'tab'   => 'general',
        ] );
        $this->add_field( 'maps_heading', [
            'type'        => Fields_Manager::HEADING,
            'label'       => __( 'Google Maps Configuration', 'directories-builder-pro' ),
            'description' => __( 'Configure the Google Maps integration for map display and geolocation features.', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'google_maps_key', [
            'type'        => Fields_Manager::TEXT,
            'label'       => __( 'API Key', 'directories-builder-pro' ),
            'description' => __( 'Required for map display, geolocation, and address autocomplete.', 'directories-builder-pro' ),
            'placeholder' => 'AIza...',
        ] );
        $this->end_group();
        // ── Reviews (General tab) ──
        $this->start_group( 'reviews', [
            'label' => __( 'Reviews', 'directories-builder-pro' ),
            'tab'   => 'general',
        ] );
        $this->add_field( 'moderation_mode', [
            'type'    => Fields_Manager::RADIO,
            'label'   => __( 'Moderation Mode', 'directories-builder-pro' ),
            'options' => [
                'auto_approve' => __( 'Auto-approve', 'directories-builder-pro' ),
                'manual'       => __( 'Manual approval', 'directories-builder-pro' ),
            ],
            'default' => 'manual',
        ] );
        $this->add_field( 'min_review_length', [
            'type'    => Fields_Manager::NUMBER,
            'label'   => __( 'Minimum review length (characters)', 'directories-builder-pro' ),
            'default' => 25,
            'min'     => 10,
            'max'     => 500,
        ] );
        $this->add_field( 'max_photos_per_review', [
            'type'    => Fields_Manager::NUMBER,
            'label'   => __( 'Max photos per review', 'directories-builder-pro' ),
            'default' => 5,
            'min'     => 1,
            'max'     => 20,
        ] );
        $this->end_group();
        // ── Search (General tab) ──
        $this->start_group( 'search', [
            'label' => __( 'Search', 'directories-builder-pro' ),
            'tab'   => 'general',
        ] );
        $this->add_field( 'default_radius_km', [
            'type'    => Fields_Manager::NUMBER,
            'label'   => __( 'Default search radius (km)', 'directories-builder-pro' ),
            'default' => 10,
            'min'     => 1,
            'max'     => 500,
        ] );
        $this->add_field( 'results_per_page', [
            'type'    => Fields_Manager::NUMBER,
            'label'   => __( 'Results per page', 'directories-builder-pro' ),
            'default' => 12,
            'min'     => 4,
            'max'     => 48,
        ] );
        $this->add_field( 'distance_unit', [
            'type'    => Fields_Manager::RADIO,
            'label'   => __( 'Distance unit', 'directories-builder-pro' ),
            'options' => [
                'km'    => __( 'Kilometres', 'directories-builder-pro' ),
                'miles' => __( 'Miles', 'directories-builder-pro' ),
            ],
            'default' => 'km',
        ] );
        $this->end_group();
        // ── Business Listings (Advanced tab) ──
        $this->start_group( 'business', [
            'label' => __( 'Business Listings', 'directories-builder-pro' ),
            'tab'   => 'advanced',
        ] );
        $this->add_field( 'allow_user_submissions', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Allow registered users to submit new business listings', 'directories-builder-pro' ),
            'default' => false,
        ] );
        $this->add_field( 'require_claim_verification', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Require verification documents for business claims', 'directories-builder-pro' ),
            'default' => true,
        ] );
        $this->add_field( 'default_business_status', [
            'type'      => Fields_Manager::SELECT,
            'label'     => __( 'Default status for new submissions', 'directories-builder-pro' ),
            'options'   => [
                'active'  => __( 'Active', 'directories-builder-pro' ),
                'pending' => __( 'Pending review', 'directories-builder-pro' ),
            ],
            'default'   => 'pending',
            'condition' => [ 'field' => 'allow_user_submissions', 'value' => true ],
        ] );
        $this->end_group();
        // ── User Accounts (Advanced tab) ──
        $this->start_group( 'users', [
            'label' => __( 'User Accounts', 'directories-builder-pro' ),
            'tab'   => 'advanced',
        ] );
        $this->add_field( 'max_reviews_per_hour', [
            'type'    => Fields_Manager::NUMBER,
            'label'   => __( 'Max reviews per user per hour (rate limit)', 'directories-builder-pro' ),
            'default' => 3,
            'min'     => 1,
            'max'     => 20,
        ] );
        $this->add_field( 'enable_elite_program', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Enable Elite reviewer badge system', 'directories-builder-pro' ),
            'default' => false,
        ] );
        $this->add_field( 'elite_review_threshold', [
            'type'      => Fields_Manager::NUMBER,
            'label'     => __( 'Minimum approved reviews for Elite eligibility', 'directories-builder-pro' ),
            'default'   => 25,
            'min'       => 5,
            'max'       => 200,
            'condition' => [ 'field' => 'enable_elite_program', 'value' => true ],
        ] );
        $this->end_group();
    }
    /**
     * Migrate legacy individual wp_options to consolidated format.
     *
     * @return array
     */
    protected function migrate_legacy_options(): array {
        $legacy_map = [
            'dbp_google_maps_key'       => 'google_maps_key',
            'dbp_moderation_mode'       => 'moderation_mode',
            'dbp_min_review_length'     => 'min_review_length',
            'dbp_max_photos_per_review' => 'max_photos_per_review',
            'dbp_default_radius_km'     => 'default_radius_km',
            'dbp_results_per_page'      => 'results_per_page',
            'dbp_distance_unit'         => 'distance_unit',
            'dbp_allow_user_submissions' => 'allow_user_submissions',
        ];
        $migrated = [];
        $found    = false;
        foreach ( $legacy_map as $old_key => $new_key ) {
            $value = get_option( $old_key, null );
            if ( $value !== null ) {
                $migrated[ $new_key ] = $value;
                $found = true;
            }
        }
        // If legacy values found, persist under new key and clean up.
        if ( $found && ! empty( $migrated ) ) {
            update_option( $this->get_storage_key(), $migrated );
            // Delete legacy keys.
            foreach ( array_keys( $legacy_map ) as $old_key ) {
                delete_option( $old_key );
            }
        }
        return $migrated;
    }
}
