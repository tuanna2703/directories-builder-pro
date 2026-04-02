<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Abstract Field_Base class.
 *
 * Every concrete field type must extend this class and implement
 * get_type(), sanitize(), and render(). Shared validation and schema
 * generation are provided by the base.
 *
 * @package DirectoriesBuilderPro\Core\Fields
 */
abstract class Field_Base {
    /**
     * Get the field type slug (e.g. 'text', 'select', 'repeater').
     *
     * @return string
     */
    abstract public function get_type(): string;
    /**
     * Sanitize a raw value for this field type.
     *
     * @param mixed $value Raw value from user input.
     * @return mixed Sanitized value.
     */
    abstract public function sanitize( mixed $value ): mixed;
    /**
     * Render the admin HTML for this field.
     *
     * @param array $field Field definition array.
     * @param mixed $value Current stored value.
     * @return void
     */
    abstract public function render( array $field, mixed $value ): void;
    /**
     * Get the default value for a field definition.
     *
     * @param array $field Field definition array.
     * @return mixed
     */
    public function get_default( array $field ): mixed {
        return $field['default'] ?? null;
    }
    /**
     * Validate a value against field constraints.
     *
     * @param array $field Field definition array.
     * @param mixed $value Value to validate.
     * @return true|WP_Error
     */
    public function validate( array $field, mixed $value ): true|WP_Error {
        if ( ! empty( $field['required'] ) && $this->is_empty_value( $value ) ) {
            return new WP_Error(
                'field_required',
                sprintf(
                    /* translators: %s: field label */
                    __( '%s is required.', 'directories-builder-pro' ),
                    $field['label'] ?? $field['id'] ?? ''
                )
            );
        }
        return true;
    }
    /**
     * Get a JSON-safe schema representation of this field.
     *
     * @param array $field Field definition array.
     * @return array
     */
    public function get_schema( array $field ): array {
        $schema = [
            'id'          => $field['id'] ?? '',
            'type'        => $this->get_type(),
            'label'       => $field['label'] ?? '',
            'default'     => $this->get_default( $field ),
            'description' => $field['description'] ?? '',
            'placeholder' => $field['placeholder'] ?? '',
            'required'    => ! empty( $field['required'] ),
        ];
        if ( isset( $field['condition'] ) ) {
            $schema['condition'] = $field['condition'];
        }
        if ( isset( $field['options'] ) ) {
            $schema['options'] = $field['options'];
        }
        if ( isset( $field['fields'] ) ) {
            $schema['fields'] = $field['fields'];
        }
        if ( isset( $field['min'] ) ) {
            $schema['min'] = $field['min'];
        }
        if ( isset( $field['max'] ) ) {
            $schema['max'] = $field['max'];
        }
        return $schema;
    }
    /**
     * Check whether a value is considered empty.
     *
     * @param mixed $value Value to check.
     * @return bool
     */
    protected function is_empty_value( mixed $value ): bool {
        if ( is_null( $value ) ) {
            return true;
        }
        if ( is_string( $value ) && trim( $value ) === '' ) {
            return true;
        }
        if ( is_array( $value ) && empty( $value ) ) {
            return true;
        }
        return false;
    }
    /**
     * Render common field wrapper opening HTML.
     *
     * @param array $field Field definition array.
     * @return void
     */
    protected function render_wrapper_open( array $field ): void {
        $type       = esc_attr( $this->get_type() );
        $id         = esc_attr( $field['id'] ?? '' );
        $required   = ! empty( $field['required'] ) ? ' data-required="true"' : '';
        $condition  = '';
        $cond_style = '';
        if ( ! empty( $field['condition'] ) ) {
            $condition  = " data-condition='" . esc_attr( wp_json_encode( $field['condition'] ) ) . "'";
            $cond_style = ' style="display:none"';
        }
        $min_attr = isset( $field['min'] ) ? ' data-min="' . esc_attr( (string) $field['min'] ) . '"' : '';
        $max_attr = isset( $field['max'] ) ? ' data-max="' . esc_attr( (string) $field['max'] ) . '"' : '';
        echo '<div class="dbp-field dbp-field--' . $type . '" data-field-id="' . $id . '"'
            . $required . $condition . $min_attr . $max_attr . $cond_style . '>';
    }
    /**
     * Render the field label.
     *
     * @param array $field Field definition array.
     * @return void
     */
    protected function render_label( array $field ): void {
        if ( empty( $field['label'] ) ) {
            return;
        }
        $id   = esc_attr( $field['id'] ?? '' );
        $star = ! empty( $field['required'] ) ? ' <span class="dbp-field__required">*</span>' : '';
        echo '<label class="dbp-field__label" for="dbp_' . $id . '">'
            . esc_html( $field['label'] ) . $star . '</label>';
    }
    /**
     * Render the field description.
     *
     * @param array $field Field definition array.
     * @return void
     */
    protected function render_description( array $field ): void {
        if ( empty( $field['description'] ) ) {
            return;
        }
        echo '<p class="dbp-field__description">' . esc_html( $field['description'] ) . '</p>';
    }
    /**
     * Render field wrapper closing HTML.
     *
     * @return void
     */
    protected function render_wrapper_close(): void {
        echo '<div class="dbp-field__error" aria-live="polite"></div>';
        echo '</div>';
    }
}
