<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Checkbox_Field class.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Checkbox_Field extends Field_Base {
    public function get_type(): string {
        return 'checkbox';
    }
    public function sanitize( mixed $value ): mixed {
        return (bool) $value;
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        echo '<div class="dbp-field__control dbp-field__control--inline">';
        $current = (bool) ( $value ?? $this->get_default( $field ) ?? false );
        echo '<label class="dbp-field__checkbox-label" for="dbp_' . esc_attr( $field['id'] ) . '">';
        echo '<input type="hidden" name="dbp_fields[' . esc_attr( $field['id'] ) . ']" value="0">';
        echo '<input type="checkbox" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="1"'
            . checked( $current, true, false )
            . '> ';
        echo esc_html( $field['label'] ?? '' );
        echo '</label>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
    protected function is_empty_value( mixed $value ): bool {
        return false; // Checkbox can be false, that's not "empty".
    }
}
