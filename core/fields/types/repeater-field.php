<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields\Types;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Repeater_Field class.
 *
 * Renders a table with add/remove row buttons. Each row contains sub-fields
 * that are individually sanitized on save.
 *
 * @package DirectoriesBuilderPro\Core\Fields\Types
 */
class Repeater_Field extends Field_Base {
    public function get_type(): string {
        return 'repeater';
    }
    public function sanitize( mixed $value ): mixed {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $sub_fields = $this->current_sub_fields;
        $sanitized  = [];
        foreach ( $value as $row_index => $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $clean_row = [];
            foreach ( $sub_fields as $sub ) {
                $sub_id   = $sub['id'] ?? '';
                $sub_type = $sub['type'] ?? 'text';
                $raw      = $row[ $sub_id ] ?? null;
                $field_instance = $this->resolve_field_type( $sub_type );
                $clean_row[ $sub_id ] = $field_instance ? $field_instance->sanitize( $raw ) : sanitize_text_field( (string) $raw );
            }
            $sanitized[] = $clean_row;
        }
        return $sanitized;
    }
    /**
     * Temporarily stored sub-field definitions during sanitize cycle.
     *
     * @var array
     */
    private array $current_sub_fields = [];
    /**
     * Set sub-field definitions for sanitization context.
     *
     * Called by Form_Base before invoking sanitize().
     *
     * @param array $sub_fields Sub-field definitions.
     * @return void
     */
    public function set_sub_fields( array $sub_fields ): void {
        $this->current_sub_fields = $sub_fields;
    }
    public function render( array $field, mixed $value ): void {
        $this->render_wrapper_open( $field );
        echo '<div class="dbp-field__control dbp-field__control--full">';
        if ( ! empty( $field['label'] ) ) {
            echo '<h4 class="dbp-repeater__title">' . esc_html( $field['label'] ) . '</h4>';
        }
        $sub_fields = $field['fields'] ?? [];
        $rows       = is_array( $value ) && ! empty( $value ) ? $value : [ [] ];
        echo '<div class="dbp-repeater" data-field-id="' . esc_attr( $field['id'] ) . '">';
        echo '<table class="dbp-repeater__table widefat">';
        // Header.
        echo '<thead><tr>';
        foreach ( $sub_fields as $sub ) {
            echo '<th>' . esc_html( $sub['label'] ?? '' ) . '</th>';
        }
        echo '<th class="dbp-repeater__actions-col"></th>';
        echo '</tr></thead>';
        // Body.
        echo '<tbody class="dbp-repeater__body">';
        foreach ( $rows as $row_index => $row ) {
            $this->render_row( $field['id'], $sub_fields, $row_index, $row );
        }
        echo '</tbody>';
        echo '</table>';
        echo '<button type="button" class="button dbp-repeater__add">'
            . esc_html__( 'Add Row', 'directories-builder-pro' ) . '</button>';
        echo '</div>';
        $this->render_description( $field );
        echo '</div>';
        $this->render_wrapper_close();
    }
    /**
     * Render a single repeater row.
     *
     * @param string $parent_id  Parent field ID.
     * @param array  $sub_fields Sub-field definitions.
     * @param int    $index      Row index.
     * @param array  $row_data   Row values.
     * @return void
     */
    private function render_row( string $parent_id, array $sub_fields, int $index, array $row_data ): void {
        echo '<tr class="dbp-repeater__row" data-index="' . esc_attr( (string) $index ) . '">';
        foreach ( $sub_fields as $sub ) {
            $sub_id    = $sub['id'] ?? '';
            $sub_type  = $sub['type'] ?? 'text';
            $sub_value = $row_data[ $sub_id ] ?? ( $sub['default'] ?? '' );
            $name      = 'dbp_fields[' . esc_attr( $parent_id ) . '][' . $index . '][' . esc_attr( $sub_id ) . ']';
            $input_id  = 'dbp_' . $parent_id . '_' . $index . '_' . $sub_id;
            echo '<td>';
            switch ( $sub_type ) {
                case 'select':
                    echo '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $input_id ) . '">';
                    foreach ( ( $sub['options'] ?? [] ) as $key => $label ) {
                        echo '<option value="' . esc_attr( (string) $key ) . '"'
                            . selected( (string) $sub_value, (string) $key, false )
                            . '>' . esc_html( $label ) . '</option>';
                    }
                    echo '</select>';
                    break;
                case 'toggle':
                case 'checkbox':
                    echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="0">';
                    echo '<label class="dbp-toggle">';
                    echo '<input type="checkbox" name="' . esc_attr( $name ) . '"'
                        . ' id="' . esc_attr( $input_id ) . '"'
                        . ' value="1" class="dbp-toggle__input"'
                        . checked( (bool) $sub_value, true, false ) . '>';
                    echo '<span class="dbp-toggle__slider"></span>';
                    echo '</label>';
                    break;
                case 'media':
                    $thumb = '';
                    $att_id = absint( $sub_value );
                    if ( $att_id > 0 ) {
                        $thumb = wp_get_attachment_image_url( $att_id, 'thumbnail' ) ?: '';
                    }
                    echo '<div class="dbp-media-preview"' . ( ! $thumb ? ' style="display:none"' : '' ) . '>';
                    echo '<img src="' . esc_url( $thumb ) . '" alt="" class="dbp-media-preview__img">';
                    echo '</div>';
                    echo '<input type="hidden" name="' . esc_attr( $name ) . '"'
                        . ' id="' . esc_attr( $input_id ) . '"'
                        . ' value="' . esc_attr( (string) $att_id ) . '" class="dbp-media-input">';
                    echo '<button type="button" class="button button-small dbp-media-select">'
                        . esc_html__( 'Select', 'directories-builder-pro' ) . '</button> ';
                    echo '<button type="button" class="button button-small dbp-media-remove"'
                        . ( ! $thumb ? ' style="display:none"' : '' ) . '>'
                        . esc_html__( 'Remove', 'directories-builder-pro' ) . '</button>';
                    break;
                case 'textarea':
                    echo '<textarea name="' . esc_attr( $name ) . '" id="' . esc_attr( $input_id ) . '"'
                        . ' rows="2" class="large-text"'
                        . ( ! empty( $sub['placeholder'] ) ? ' placeholder="' . esc_attr( $sub['placeholder'] ) . '"' : '' )
                        . '>' . esc_textarea( (string) $sub_value ) . '</textarea>';
                    break;
                default: // text, number, email, url, etc.
                    $input_type = in_array( $sub_type, [ 'number', 'email', 'url' ], true ) ? $sub_type : 'text';
                    echo '<input type="' . esc_attr( $input_type ) . '"'
                        . ' name="' . esc_attr( $name ) . '"'
                        . ' id="' . esc_attr( $input_id ) . '"'
                        . ' value="' . esc_attr( (string) $sub_value ) . '"'
                        . ' class="regular-text"'
                        . ( ! empty( $sub['placeholder'] ) ? ' placeholder="' . esc_attr( $sub['placeholder'] ) . '"' : '' )
                        . '>';
                    break;
            }
            echo '</td>';
        }
        echo '<td class="dbp-repeater__actions">';
        echo '<button type="button" class="button button-small dbp-repeater__remove" title="'
            . esc_attr__( 'Remove row', 'directories-builder-pro' ) . '">&times;</button>';
        echo '</td>';
        echo '</tr>';
    }
    /**
     * Resolve a Field_Base instance for a sub-field type.
     *
     * @param string $type Field type slug.
     * @return Field_Base|null
     */
    private function resolve_field_type( string $type ): ?Field_Base {
        $map = [
            'text'     => Text_Field::class,
            'textarea' => Textarea_Field::class,
            'number'   => Number_Field::class,
            'email'    => Email_Field::class,
            'url'      => Url_Field::class,
            'select'   => Select_Field::class,
            'toggle'   => Toggle_Field::class,
            'checkbox' => Checkbox_Field::class,
            'media'    => Media_Field::class,
            'hidden'   => Hidden_Field::class,
        ];
        if ( isset( $map[ $type ] ) ) {
            return new $map[ $type ]();
        }
        return null;
    }
}
