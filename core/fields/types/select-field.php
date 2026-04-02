<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Select_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Select_Field extends Field_Base {
    public function get_type(): string {
        return 'select';
    }
    public function sanitize( mixed $value ): mixed {
        return sanitize_text_field( (string) $value );
    }
    public function validate( array $field, mixed $value ): true|WP_Error {
        $parent = parent::validate( $field, $value );
        if ( $parent instanceof WP_Error ) {
            return $parent;
        }
        if ( $value !== '' && $value !== null && ! empty( $field['options'] ) ) {
            if ( ! array_key_exists( (string) $value, $field['options'] ) ) {
                return new WP_Error(
                    'field_invalid_option',
                    sprintf(
                        __( 'Invalid option selected for %s.', 'directories-builder-pro' ),
                        $field['label'] ?? ''
                    )
                );
            }
        }
        return true;
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        $current = (string) ( $value ?? $this->get_default( $field ) ?? '' );
        $options = $field['options'] ?? [];
        echo '<select id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ( ! empty( $field['required'] ) ? ' required' : '' )
            . '>';
        if ( ! empty( $field['placeholder'] ) ) {
            echo '<option value="">' . esc_html( $field['placeholder'] ) . '</option>';
        }
        foreach ( $options as $key => $label ) {
            echo '<option value="' . esc_attr( (string) $key ) . '"'
                . selected( $current, (string) $key, false )
                . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
