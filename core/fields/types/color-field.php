<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Color_Field class.
 *
 * Renders a text input initialized by wp-color-picker via JS.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Color_Field extends Field_Base {
    public function get_type(): string {
        return 'color';
    }
    public function sanitize( mixed $value ): mixed {
        $value = trim( (string) $value );
        if ( $value === '' ) {
            return '';
        }
        // Try hex first.
        $hex = sanitize_hex_color( $value );
        if ( $hex ) {
            return $hex;
        }
        // Allow rgba/rgb values.
        if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+))?\s*\)$/', $value ) ) {
            return $value;
        }
        return '';
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        $current = (string) ( $value ?? $this->get_default( $field ) ?? '' );
        echo '<input type="text" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="' . esc_attr( $current ) . '"'
            . ' data-colorpicker="true"'
            . ' data-default-color="' . esc_attr( (string) ( $this->get_default( $field ) ?? '' ) ) . '"'
            . '>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
