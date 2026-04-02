<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Email_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Email_Field extends Field_Base {
    public function get_type(): string {
        return 'email';
    }
    public function sanitize( mixed $value ): mixed {
        return sanitize_email( (string) $value );
    }
    public function validate( array $field, mixed $value ): true|WP_Error {
        $parent = parent::validate( $field, $value );
        if ( $parent instanceof WP_Error ) {
            return $parent;
        }
        if ( ! empty( $value ) && ! is_email( (string) $value ) ) {
            return new WP_Error(
                'field_email_invalid',
                sprintf(
                    __( '%s must be a valid email address.', 'directories-builder-pro' ),
                    $field['label'] ?? ''
                )
            );
        }
        return true;
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        echo '<input type="email" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="' . esc_attr( (string) ( $value ?? $this->get_default( $field ) ?? '' ) ) . '"'
            . ' class="regular-text"'
            . ( ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '' )
            . ( ! empty( $field['required'] ) ? ' required' : '' )
            . '>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
