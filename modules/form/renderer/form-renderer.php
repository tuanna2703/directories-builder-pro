<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Renderer;
use DirectoriesBuilderPro\Core\Base\Form_Base;
use DirectoriesBuilderPro\Core\Fields\Fields_Manager;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Form_Renderer class.
 *
 * Renders a complete admin form from a Form_Base schema.
 *
 * @package DirectoriesBuilderPro\Modules\Form\Renderer
 */
class Form_Renderer {
    private Form_Base $form;
    public function __construct( Form_Base $form ) {
        $this->form = $form;
    }
    /**
     * Render the full form HTML.
     *
     * @param int|null $object_id Object ID for post_meta/user_meta forms.
     * @return void
     */
    public function render( ?int $object_id = null ): void {
        $schema = $this->form->get_schema();
        $values = $this->form->get_values( $object_id );
        $fm     = Fields_Manager::instance();
        $name   = esc_attr( $this->form->get_name() );
        $obj_id = $object_id !== null ? esc_attr( (string) $object_id ) : '';
        // Collect unique tabs.
        $tabs = [];
        foreach ( $schema['groups'] as $group ) {
            $tab = $group['tab'] ?? 'general';
            if ( ! isset( $tabs[ $tab ] ) ) {
                $tabs[ $tab ] = ucfirst( $tab );
            }
        }
        echo '<div class="dbp-form" data-form-name="' . $name . '"'
            . ( $obj_id !== '' ? ' data-object-id="' . $obj_id . '"' : '' ) . '>';
        // Tab navigation (only if multiple tabs).
        if ( count( $tabs ) > 1 ) {
            echo '<ul class="dbp-form__tabs">';
            $first = true;
            foreach ( $tabs as $tab_key => $tab_label ) {
                $active = $first ? ' dbp-form__tab--active' : '';
                echo '<li class="dbp-form__tab' . $active . '"'
                    . ' data-tab="' . esc_attr( $tab_key ) . '">'
                    . esc_html( $tab_label ) . '</li>';
                $first = false;
            }
            echo '</ul>';
        }
        // Groups.
        $first_tab = array_key_first( $tabs );
        foreach ( $schema['groups'] as $group ) {
            $tab     = $group['tab'] ?? 'general';
            $hidden  = count( $tabs ) > 1 && $tab !== $first_tab ? ' style="display:none"' : '';
            echo '<div class="dbp-form__group" data-tab="' . esc_attr( $tab ) . '"' . $hidden . '>';
            if ( ! empty( $group['label'] ) ) {
                echo '<h2 class="dbp-form__group-title">' . esc_html( $group['label'] ) . '</h2>';
            }
            if ( ! empty( $group['description'] ) ) {
                echo '<p class="dbp-form__group-desc">' . esc_html( $group['description'] ) . '</p>';
            }
            // Fields.
            foreach ( $group['fields'] as $field_schema ) {
                $field_id  = $field_schema['id'] ?? '';
                $field_def = $this->form->get_fields()[ $field_id ] ?? null;
                if ( $field_def === null ) {
                    continue;
                }
                $type_instance = $fm->get_type( $field_def['type'] ?? 'text' );
                if ( $type_instance === null ) {
                    continue;
                }
                $current_value = $values[ $field_id ] ?? null;
                $type_instance->render( $field_def, $current_value );
            }
            echo '</div>';
        }
        // Save button + status area.
        echo '<div class="dbp-form__footer">';
        echo '<button type="button" class="dbp-form__save button button-primary"'
            . ' data-form-name="' . $name . '">';
        echo esc_html__( 'Save Changes', 'directories-builder-pro' );
        echo ' <span class="spinner"></span>';
        echo '</button>';
        echo '<div class="dbp-form__status" aria-live="polite"></div>';
        echo '</div>';
        echo '</div>';
        /**
         * Action: fires after form HTML is rendered.
         *
         * @param string   $form_name The form name.
         * @param int|null $object_id The object ID.
         */
        do_action( 'dbp/form/after_render', $this->form->get_name(), $object_id );
    }
}
