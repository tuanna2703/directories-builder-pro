<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Form Group
 *
 * Renders a single field group: header (label + description) and its fields.
 *
 * @slug     forms/group
 * @version  1.0.0
 *
 * @args required: group (array — keys: tab, label, description, fields)
 *                 values (array) — field values keyed by field ID
 * @args optional: has_tabs (bool)
 *                 first_tab (string)
 *                 fields_manager (Fields_Manager instance)
 *                 field_definitions (array) — field definitions from form
 *
 * @package DirectoriesBuilderPro\Templates\Forms
 */
$group      = $args['group'] ?? [];
$values     = $args['values'] ?? [];
$has_tabs   = (bool) ( $args['has_tabs'] ?? false );
$first_tab  = $args['first_tab'] ?? 'general';
$fm         = $args['fields_manager'] ?? null;
$field_defs = $args['field_definitions'] ?? [];

$tab    = $group['tab'] ?? 'general';
$hidden = $has_tabs && $tab !== $first_tab ? ' style="display:none"' : '';
?>
<div class="dbp-form__group" data-tab="<?php echo esc_attr( $tab ); ?>"<?php echo $hidden; ?>>
    <?php if ( ! empty( $group['label'] ) ) : ?>
        <h2 class="dbp-form__group-title"><?php echo esc_html( $group['label'] ); ?></h2>
    <?php endif; ?>
    <?php if ( ! empty( $group['description'] ) ) : ?>
        <p class="dbp-form__group-desc"><?php echo esc_html( $group['description'] ); ?></p>
    <?php endif; ?>

    <?php
    // Render fields.
    if ( ! empty( $group['fields'] ) ) :
        foreach ( $group['fields'] as $field_schema ) :
            $field_id = $field_schema['id'] ?? '';
            $field_def = $field_defs[ $field_id ] ?? null;
            if ( $field_def === null ) {
                continue;
            }
            $current_value = $values[ $field_id ] ?? null;
            dbp_template( 'forms/field', [
                'field'          => $field_def,
                'value'          => $current_value,
                'fields_manager' => $fm,
            ] );
        endforeach;
    endif;
    ?>
</div>
