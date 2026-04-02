<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Hidden_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Hidden_Field extends Field_Base {
    public function get_type(): string {
        return 'hidden';
    }
    public function sanitize( mixed $value ): mixed {
        return sanitize_text_field( (string) $value );
    }
    public function render( array $field, mixed $value ): void {
        $current = (string) ( $value ?? $this->get_default( $field ) ?? '' );
        echo '<div class="dbp-field dbp-field--hidden" data-field-id="' . esc_attr( $field['id'] ?? '' ) . '">';
        echo '<input type="hidden" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="' . esc_attr( $current ) . '">';
        echo '</div>';
    }
}
