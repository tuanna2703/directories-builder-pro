<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Password_Field class.
 *
 * Note: This stores the value as sanitized text. Do NOT use for storing
 * actual user passwords — use WordPress auth APIs instead. Intended for
 * API keys and tokens that need masked input.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Password_Field extends Field_Base {
    public function get_type(): string {
        return 'password';
    }
    public function sanitize( mixed $value ): mixed {
        return sanitize_text_field( (string) $value );
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        echo '<input type="password" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="' . esc_attr( (string) ( $value ?? $this->get_default( $field ) ?? '' ) ) . '"'
            . ' class="regular-text"'
            . ' autocomplete="off"'
            . ( ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '' )
            . ( ! empty( $field['required'] ) ? ' required' : '' )
            . '>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
