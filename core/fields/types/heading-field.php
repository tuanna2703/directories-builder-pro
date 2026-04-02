<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Heading_Field class.
 *
 * Display-only section divider. Never stored.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Heading_Field extends Field_Base {
    public function get_type(): string {
        return 'heading';
    }
    public function sanitize( mixed $value ): mixed {
        return null; // Display-only, never stored.
    }
    public function render( array $field, mixed $value ): void {
        echo '<div class="dbp-field dbp-field--heading" data-field-id="' . esc_attr( $field['id'] ?? '' ) . '">';
        echo '<div class="dbp-field-heading">';
        if ( ! empty( $field['label'] ) ) {
            echo '<h3>' . esc_html( $field['label'] ) . '</h3>';
        }
        if ( ! empty( $field['description'] ) ) {
            echo '<p>' . esc_html( $field['description'] ) . '</p>';
        }
        echo '</div>';
        echo '</div>';
    }
}
