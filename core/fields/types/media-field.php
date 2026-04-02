<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Media_Field class.
 *
 * Stores an attachment ID. Renders thumbnail preview + WP Media Library buttons.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Media_Field extends Field_Base {
    public function get_type(): string {
        return 'media';
    }
    public function sanitize( mixed $value ): mixed {
        return absint( $value );
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        $this->render_label( $field );
        echo '<div class="dbp-field__control">';
        $attachment_id = absint( $value ?? $this->get_default( $field ) ?? 0 );
        $thumb_url     = '';
        $has_image     = false;
        if ( $attachment_id > 0 ) {
            $img = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
            if ( $img ) {
                $thumb_url = $img;
                $has_image = true;
            }
        }
        echo '<div class="dbp-media-preview"' . ( ! $has_image ? ' style="display:none"' : '' ) . '>';
        echo '<img src="' . esc_url( $thumb_url ) . '" alt="" class="dbp-media-preview__img">';
        echo '</div>';
        echo '<input type="hidden" id="dbp_' . esc_attr( $field['id'] ) . '"'
            . ' name="dbp_fields[' . esc_attr( $field['id'] ) . ']"'
            . ' value="' . esc_attr( (string) $attachment_id ) . '"'
            . ' class="dbp-media-input">';
        echo '<div class="dbp-media-buttons">';
        echo '<button type="button" class="button dbp-media-select">'
            . esc_html__( 'Select Image', 'directories-builder-pro' ) . '</button> ';
        echo '<button type="button" class="button dbp-media-remove"'
            . ( ! $has_image ? ' style="display:none"' : '' ) . '>'
            . esc_html__( 'Remove', 'directories-builder-pro' ) . '</button>';
        echo '</div>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
}
