<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Forms;
use DirectoriesBuilderPro\Core\Base\Form_Base;
use DirectoriesBuilderPro\Core\Fields\Fields_Manager;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * User_Profile_Form class.
 *
 * Replaces ad-hoc user meta fields with a declarative form definition.
 *
 * @package DirectoriesBuilderPro\Modules\Form\Forms
 */
class User_Profile_Form extends Form_Base {
    public function get_name(): string {
        return 'user_profile';
    }
    public function get_title(): string {
        return __( 'Profile Settings', 'directories-builder-pro' );
    }
    public function get_storage_type(): string {
        return 'user_meta';
    }
    protected function register_fields(): void {
        // ── Public Profile ──
        $this->start_group( 'profile', [
            'label' => __( 'Public Profile', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'display_name', [
            'type'     => Fields_Manager::TEXT,
            'label'    => __( 'Display name', 'directories-builder-pro' ),
            'required' => true,
        ] );
        $this->add_field( 'bio', [
            'type'        => Fields_Manager::TEXTAREA,
            'label'       => __( 'Short bio', 'directories-builder-pro' ),
            'description' => __( 'Shown on your public profile.', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'avatar', [
            'type'  => Fields_Manager::MEDIA,
            'label' => __( 'Profile photo', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'location', [
            'type'  => Fields_Manager::TEXT,
            'label' => __( 'Your city / location', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'website', [
            'type'  => Fields_Manager::URL,
            'label' => __( 'Personal website', 'directories-builder-pro' ),
        ] );
        $this->end_group();
        // ── Preferences ──
        $this->start_group( 'preferences', [
            'label' => __( 'Preferences', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'distance_unit', [
            'type'    => Fields_Manager::SELECT,
            'label'   => __( 'Distance unit preference', 'directories-builder-pro' ),
            'options' => [
                'km'    => __( 'Kilometres', 'directories-builder-pro' ),
                'miles' => __( 'Miles', 'directories-builder-pro' ),
            ],
            'default' => 'km',
        ] );
        $this->add_field( 'email_on_response', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Email me when a business responds to my review', 'directories-builder-pro' ),
            'default' => true,
        ] );
        $this->add_field( 'email_on_helpful', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Email me when someone marks my review as helpful', 'directories-builder-pro' ),
            'default' => false,
        ] );
        $this->end_group();
        // ── Privacy ──
        $this->start_group( 'privacy', [
            'label' => __( 'Privacy', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'show_profile_public', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Make my profile publicly visible', 'directories-builder-pro' ),
            'default' => true,
        ] );
        $this->add_field( 'show_review_count', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Show my review count on my profile', 'directories-builder-pro' ),
            'default' => true,
        ] );
        $this->end_group();
    }
}
