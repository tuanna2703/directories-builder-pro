<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Url_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Url_Field extends Field_Base {
    public function get_type(): string {
        return 'url';
    }
    public function sanitize( mixed $value ): mixed {
        $url = trim( (string) $value );
        return $url !== '' ? esc_url_raw( $url ) : '';
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        echo '<input type="url" id="dbp_' . esc_attr( $field['id'] ) . '"'
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
