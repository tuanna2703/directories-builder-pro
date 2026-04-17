<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Form Field
 *
 * Wrapper for a single form field: delegates actual control rendering to
 * the Fields_Manager type instance.
 *
 * @slug     forms/field
 * @version  1.0.0
 *
 * @args required: field (array — keys: id, type, label, description, etc.)
 *                 value (mixed) — current field value
 * @args optional: fields_manager (Fields_Manager instance)
 *
 * @package DirectoriesBuilderPro\Templates\Forms
 */
$field = $args['field'] ?? [];
$value = $args['value'] ?? null;
$fm    = $args['fields_manager'] ?? \DirectoriesBuilderPro\Core\Fields\Fields_Manager::instance();

$field_id = $field['id'] ?? '';
$type     = $field['type'] ?? 'text';

// Get the type instance from Fields_Manager.
$type_instance = $fm->get_type( $type );
if ( $type_instance === null ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        echo '<!-- DBP: unknown field type "' . esc_html( $type ) . '" for field "' . esc_html( $field_id ) . '" -->';
    }
    return;
}

// Delegate rendering to the field type instance.
// The type instance handles its own label, control, description, and error markup.
$type_instance->render( $field, $value );
