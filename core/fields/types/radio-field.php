<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Radio_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Radio_Field extends Field_Base {
    public function get_type(): string {
        return 'radio';
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
        echo '<fieldset class="dbp-field__radio-group">';
        foreach ( $options as $key => $label ) {
            $key_str = (string) $key;
            $uid     = 'dbp_' . esc_attr( $field['id'] ) . '_' . esc_attr( $key_str );
            echo '<label class="dbp-field__radio-item" for="' . $uid . '">';
            echo '<input type="radio" id="' . $uid . '"'
                . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
                . ' value="' . esc_attr( $key_str ) . '"'
                . checked( $current, $key_str, false )
                . '> ';
            echo esc_html( $label );
            echo '</label>';
        }
        echo '</fieldset>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
