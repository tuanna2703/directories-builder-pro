<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Forms;
use DirectoriesBuilderPro\Core\Base\Form_Base;
use DirectoriesBuilderPro\Core\Fields\Fields_Manager;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Business_Settings_Form class.
 *
 * Replaces the business-edit.php meta boxes with a unified form engine.
 *
 * @package DirectoriesBuilderPro\Modules\Form\Forms
 */
class Business_Settings_Form extends Form_Base {
    public function get_name(): string {
        return 'business_settings';
    }
    public function get_title(): string {
        return __( 'Business Settings', 'directories-builder-pro' );
    }
    public function get_storage_type(): string {
        return 'post_meta';
    }
    protected function register_fields(): void {
        // ── Location ──
        $this->start_group( 'location', [
            'label' => __( 'Location', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'address', [
            'type'     => Fields_Manager::TEXT,
            'label'    => __( 'Street address', 'directories-builder-pro' ),
            'required' => true,
        ] );
        $this->add_field( 'city', [
            'type'     => Fields_Manager::TEXT,
            'label'    => __( 'City', 'directories-builder-pro' ),
            'required' => true,
        ] );
        $this->add_field( 'state', [
            'type'  => Fields_Manager::TEXT,
            'label' => __( 'State / Province', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'zip', [
            'type'  => Fields_Manager::TEXT,
            'label' => __( 'Postcode / ZIP', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'country', [
            'type'    => Fields_Manager::SELECT,
            'label'   => __( 'Country', 'directories-builder-pro' ),
            'options' => [
                'US' => __( 'United States', 'directories-builder-pro' ),
                'GB' => __( 'United Kingdom', 'directories-builder-pro' ),
                'AU' => __( 'Australia', 'directories-builder-pro' ),
                'CA' => __( 'Canada', 'directories-builder-pro' ),
                'DE' => __( 'Germany', 'directories-builder-pro' ),
                'FR' => __( 'France', 'directories-builder-pro' ),
                'JP' => __( 'Japan', 'directories-builder-pro' ),
                'KR' => __( 'South Korea', 'directories-builder-pro' ),
                'SG' => __( 'Singapore', 'directories-builder-pro' ),
                'NZ' => __( 'New Zealand', 'directories-builder-pro' ),
                'IE' => __( 'Ireland', 'directories-builder-pro' ),
                'NL' => __( 'Netherlands', 'directories-builder-pro' ),
                'SE' => __( 'Sweden', 'directories-builder-pro' ),
                'NO' => __( 'Norway', 'directories-builder-pro' ),
                'DK' => __( 'Denmark', 'directories-builder-pro' ),
                'IT' => __( 'Italy', 'directories-builder-pro' ),
                'ES' => __( 'Spain', 'directories-builder-pro' ),
                'BR' => __( 'Brazil', 'directories-builder-pro' ),
                'MX' => __( 'Mexico', 'directories-builder-pro' ),
                'IN' => __( 'India', 'directories-builder-pro' ),
                'VN' => __( 'Vietnam', 'directories-builder-pro' ),
            ],
            'default' => 'US',
        ] );
        $this->add_field( 'lat', [
            'type'  => Fields_Manager::HIDDEN,
            'label' => __( 'Latitude', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'lng', [
            'type'  => Fields_Manager::HIDDEN,
            'label' => __( 'Longitude', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'map_note', [
            'type'        => Fields_Manager::HEADING,
            'label'       => '',
            'description' => __( 'Click the map to set the pin location.', 'directories-builder-pro' ),
        ] );
        $this->end_group();
        // ── Contact ──
        $this->start_group( 'contact', [
            'label' => __( 'Contact', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'phone', [
            'type'  => Fields_Manager::TEXT,
            'label' => __( 'Phone number', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'website', [
            'type'  => Fields_Manager::URL,
            'label' => __( 'Website URL', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'email', [
            'type'  => Fields_Manager::EMAIL,
            'label' => __( 'Business email', 'directories-builder-pro' ),
        ] );
        $this->end_group();
        // ── Business Details ──
        $this->start_group( 'details', [
            'label' => __( 'Business Details', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'price_level', [
            'type'    => Fields_Manager::RADIO,
            'label'   => __( 'Price level', 'directories-builder-pro' ),
            'options' => [
                '1' => '$',
                '2' => '$$',
                '3' => '$$$',
                '4' => '$$$$',
            ],
            'default' => '1',
        ] );
        $this->add_field( 'description', [
            'type'     => Fields_Manager::TEXTAREA,
            'label'    => __( 'Business description', 'directories-builder-pro' ),
            'required' => true,
        ] );
        $this->add_field( 'hours', [
            'type'   => Fields_Manager::REPEATER,
            'label'  => __( 'Opening hours', 'directories-builder-pro' ),
            'fields' => [
                [
                    'id'      => 'day',
                    'type'    => 'select',
                    'label'   => __( 'Day', 'directories-builder-pro' ),
                    'options' => [
                        'Mon' => __( 'Monday', 'directories-builder-pro' ),
                        'Tue' => __( 'Tuesday', 'directories-builder-pro' ),
                        'Wed' => __( 'Wednesday', 'directories-builder-pro' ),
                        'Thu' => __( 'Thursday', 'directories-builder-pro' ),
                        'Fri' => __( 'Friday', 'directories-builder-pro' ),
                        'Sat' => __( 'Saturday', 'directories-builder-pro' ),
                        'Sun' => __( 'Sunday', 'directories-builder-pro' ),
                    ],
                ],
                [
                    'id'          => 'open',
                    'type'        => 'text',
                    'label'       => __( 'Opens', 'directories-builder-pro' ),
                    'placeholder' => '09:00',
                ],
                [
                    'id'          => 'close',
                    'type'        => 'text',
                    'label'       => __( 'Closes', 'directories-builder-pro' ),
                    'placeholder' => '17:00',
                ],
                [
                    'id'    => 'closed',
                    'type'  => 'toggle',
                    'label' => __( 'Closed all day', 'directories-builder-pro' ),
                ],
            ],
        ] );
        $this->end_group();
        // ── Attributes & Amenities ──
        $this->start_group( 'attributes', [
            'label' => __( 'Attributes & Amenities', 'directories-builder-pro' ),
        ] );
        $toggles = [
            'wifi'            => __( 'Free Wi-Fi', 'directories-builder-pro' ),
            'parking'         => __( 'Free parking', 'directories-builder-pro' ),
            'outdoor_seating' => __( 'Outdoor seating', 'directories-builder-pro' ),
            'delivery'        => __( 'Delivery available', 'directories-builder-pro' ),
            'takeout'         => __( 'Takeout available', 'directories-builder-pro' ),
            'reservations'    => __( 'Accepts reservations', 'directories-builder-pro' ),
            'wheelchair'      => __( 'Wheelchair accessible', 'directories-builder-pro' ),
        ];
        foreach ( $toggles as $id => $label ) {
            $this->add_field( $id, [
                'type'    => Fields_Manager::TOGGLE,
                'label'   => $label,
                'default' => false,
            ] );
        }
        $this->end_group();
        // ── Photos ──
        $this->start_group( 'media', [
            'label' => __( 'Photos', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'featured_image', [
            'type'  => Fields_Manager::MEDIA,
            'label' => __( 'Featured image', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'gallery', [
            'type'   => Fields_Manager::REPEATER,
            'label'  => __( 'Photo gallery', 'directories-builder-pro' ),
            'fields' => [
                [
                    'id'    => 'image',
                    'type'  => 'media',
                    'label' => __( 'Photo', 'directories-builder-pro' ),
                ],
                [
                    'id'    => 'caption',
                    'type'  => 'text',
                    'label' => __( 'Caption', 'directories-builder-pro' ),
                ],
            ],
        ] );
        $this->end_group();
        // ── Listing Status ──
        $this->start_group( 'status', [
            'label' => __( 'Listing Status', 'directories-builder-pro' ),
        ] );
        $this->add_field( 'status', [
            'type'    => Fields_Manager::SELECT,
            'label'   => __( 'Status', 'directories-builder-pro' ),
            'options' => [
                'active'   => __( 'Active', 'directories-builder-pro' ),
                'inactive' => __( 'Inactive', 'directories-builder-pro' ),
                'pending'  => __( 'Pending', 'directories-builder-pro' ),
            ],
            'default' => 'active',
        ] );
        $this->add_field( 'featured', [
            'type'    => Fields_Manager::TOGGLE,
            'label'   => __( 'Mark as featured listing', 'directories-builder-pro' ),
            'default' => false,
        ] );
        $this->end_group();
    }
}
