<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Toggle_Field class.
 *
 * Renders as a CSS-styled toggle switch (checkbox hidden, styled span).
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Toggle_Field extends Field_Base {
    public function get_type(): string {
        return 'toggle';
    }
    public function sanitize( mixed $value ): mixed {
        return (bool) $value;
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        $current = (bool) ( $value ?? $this->get_default( $field ) ?? false );
        echo '<label class="dbp-toggle" for="dbp_' . esc_attr( $field['id'] ) . '">';
        echo '<input type="hidden" name="dbp_fields[' . esc_attr( $field['id'] ) . ']" value="0">';
        echo '<input type="checkbox" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="1"'
            . ' class="dbp-toggle__input"'
            . checked( $current, true, false )
            . '>';
        echo '<span class="dbp-toggle__slider"></span>';
        echo '</label>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
    protected function is_empty_value( mixed $value ): bool {
        return false;
    }
}
