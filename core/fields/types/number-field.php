<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Number_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Number_Field extends Field_Base {
    public function get_type(): string {
        return 'number';
    }
    public function sanitize( mixed $value ): mixed {
        if ( $value === '' || $value === null ) {
            return null;
        }
        return is_float( $value ) || str_contains( (string) $value, '.' )
            ? floatval( $value )
            : intval( $value );
    }
    public function validate( array $field, mixed $value ): true|WP_Error {
        $parent = parent::validate( $field, $value );
        if ( $parent instanceof WP_Error ) {
            return $parent;
        }
        if ( $value === null || $value === '' ) {
            return true;
        }
        $num = is_float( $value ) ? $value : (float) $value;
        if ( isset( $field['min'] ) && $num < (float) $field['min'] ) {
            return new WP_Error(
                'field_min',
                sprintf(
                    __( '%s must be at least %s.', 'directories-builder-pro' ),
                    $field['label'] ?? '',
                    (string) $field['min']
                )
            );
        }
        if ( isset( $field['max'] ) && $num > (float) $field['max'] ) {
            return new WP_Error(
                'field_max',
                sprintf(
                    __( '%s must be at most %s.', 'directories-builder-pro' ),
                    $field['label'] ?? '',
                    (string) $field['max']
                )
            );
        }
        return true;
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        $current = $value ?? $this->get_default( $field ) ?? '';
        echo '<input type="number" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="' . esc_attr( (string) $current ) . '"'
            . ' class="small-text"'
            . ( isset( $field['min'] ) ? ' min="' . esc_attr( (string) $field['min'] ) . '"' : '' )
            . ( isset( $field['max'] ) ? ' max="' . esc_attr( (string) $field['max'] ) . '"' : '' )
            . ( isset( $field['step'] ) ? ' step="' . esc_attr( (string) $field['step'] ) . '"' : '' )
            . ( ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '' )
            . ( ! empty( $field['required'] ) ? ' required' : '' )
            . '>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
