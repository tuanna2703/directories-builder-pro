<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Form Shell
 *
 * Renders the outer form structure: tab bar, groups container, save button, and status area.
 * Used by the Form Engine (Form_Renderer) to produce consistent form markup.
 *
 * @slug     forms/form
 * @version  1.0.0
 *
 * @args required: form_name (string) — unique form identifier
 *                 form_title (string) — visible form heading
 *                 groups (array) — array of group data from schema
 * @args optional: object_id (int|null) — object ID for post_meta/user_meta forms
 *                 tabs (array) — tab_key => label map
 *                 has_tabs (bool, default based on tabs count)
 *                 values (array) — field values keyed by field ID
 *                 fields_manager (Fields_Manager instance)
 *                 field_definitions (array) — field definitions from form
 *
 * @package DirectoriesBuilderPro\Templates\Forms
 */
$form_name   = esc_attr( $args['form_name'] ?? '' );
$form_title  = $args['form_title'] ?? '';
$groups      = $args['groups'] ?? [];
$object_id   = isset( $args['object_id'] ) ? (int) $args['object_id'] : null;
$tabs        = $args['tabs'] ?? [];
$has_tabs    = $args['has_tabs'] ?? ( count( $tabs ) > 1 );
$values      = $args['values'] ?? [];
$fm          = $args['fields_manager'] ?? null;
$field_defs  = $args['field_definitions'] ?? [];
$obj_id_attr = $object_id !== null ? esc_attr( (string) $object_id ) : '';
?>
<div class="dbp-form" data-form-name="<?php echo $form_name; ?>"<?php echo $obj_id_attr !== '' ? ' data-object-id="' . $obj_id_attr . '"' : ''; ?>>
    <?php // Tab navigation (only if multiple tabs). ?>
    <?php if ( $has_tabs ) : ?>
        <ul class="dbp-form__tabs" role="tablist">
            <?php $first = true;
            foreach ( $tabs as $tab_key => $tab_label ) : ?>
                <li class="dbp-form__tab<?php echo $first ? ' dbp-form__tab--active' : ''; ?>"
                    data-tab="<?php echo esc_attr( $tab_key ); ?>"
                    role="tab"
                    aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
                    tabindex="<?php echo $first ? '0' : '-1'; ?>">
                    <?php echo esc_html( $tab_label ); ?>
                </li>
            <?php $first = false;
            endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php
    // Render each group.
    $first_tab = ! empty( $tabs ) ? array_key_first( $tabs ) : 'general';
    foreach ( $groups as $group ) :
        dbp_template( 'forms/group', [
            'group'             => $group,
            'values'            => $values,
            'has_tabs'          => $has_tabs,
            'first_tab'         => $first_tab,
            'fields_manager'    => $fm,
            'field_definitions' => $field_defs,
        ] );
    endforeach;
    ?>

    <!-- Save button + status area -->
    <div class="dbp-form__footer">
        <button type="button" class="dbp-form__save button button-primary"
                data-form-name="<?php echo $form_name; ?>">
            <?php esc_html_e( 'Save Changes', 'directories-builder-pro' ); ?>
            <span class="spinner"></span>
        </button>
        <div class="dbp-form__status" aria-live="polite"></div>
    </div>
</div>
<?php
/**
 * Action: fires after form HTML is rendered via template.
 *
 * @param string   $form_name The form name.
 * @param int|null $object_id The object ID.
 */
do_action( 'dbp/form/after_render', $args['form_name'] ?? '', $object_id );
?>
