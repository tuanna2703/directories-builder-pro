<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Multi_Select_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Multi_Select_Field extends Field_Base {
    public function get_type(): string {
        return 'multi_select';
    }
    public function sanitize( mixed $value ): mixed {
        if ( ! is_array( $value ) ) {
            $value = $value !== '' && $value !== null ? [ (string) $value ] : [];
        }
        return array_map( 'sanitize_text_field', array_values( $value ) );
    }
    public function validate( array $field, mixed $value ): true|WP_Error {
        $parent = parent::validate( $field, $value );
        if ( $parent instanceof WP_Error ) {
            return $parent;
        }
        if ( ! empty( $value ) && ! empty( $field['options'] ) ) {
            $allowed = array_keys( $field['options'] );
            foreach ( (array) $value as $v ) {
                if ( ! in_array( (string) $v, array_map( 'strval', $allowed ), true ) ) {
                    return new WP_Error(
                        'field_invalid_option',
                        sprintf(
                            __( 'Invalid option selected for %s.', 'directories-builder-pro' ),
                            $field['label'] ?? ''
                        )
                    );
                }
            }
        }
        return true;
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        $current = (array) ( $value ?? $this->get_default( $field ) ?? [] );
        $options = $field['options'] ?? [];
        echo '<select id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . '][]"'
            . ' multiple="multiple"'
            . ' class="dbp-multi-select"'
            . ' size="' . esc_attr( (string) min( count( $options ), 8 ) ) . '"'
            . '>';
        foreach ( $options as $key => $label ) {
            $sel = in_array( (string) $key, array_map( 'strval', $current ), true ) ? ' selected' : '';
            echo '<option value="' . esc_attr( (string) $key ) . '"' . $sel . '>'
                . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
