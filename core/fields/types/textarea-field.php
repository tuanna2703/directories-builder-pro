<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Textarea_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Textarea_Field extends Field_Base {
    public function get_type(): string {
        return 'textarea';
    }
    public function sanitize( mixed $value ): mixed {
        return sanitize_textarea_field( (string) $value );
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        $rows = $field['rows'] ?? 5;
        echo '<textarea id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' rows="' . esc_attr( (string) $rows ) . '"'
            . ' class="large-text"'
            . ( ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '' )
            . ( ! empty( $field['required'] ) ? ' required' : '' )
            . '>' . esc_textarea( (string) ( $value ?? $this->get_default( $field ) ?? '' ) ) . '</textarea>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
