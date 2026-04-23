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
        $name   = $this->form->get_name();

        // Collect unique tabs.
        $tabs = [];
        foreach ( $schema['groups'] as $group ) {
            $tab = $group['tab'] ?? 'general';
            if ( ! isset( $tabs[ $tab ] ) ) {
                $tabs[ $tab ] = ucfirst( $tab );
            }
        }

        \DirectoriesBuilderPro\Modules\Template\Template_Module::render( 'forms/form', [
            'form_name'         => $name,
            'form_title'        => $schema['title'] ?? '',
            'groups'            => $schema['groups'],
            'object_id'         => $object_id,
            'tabs'              => $tabs,
            'has_tabs'          => count( $tabs ) > 1,
            'values'            => $values,
            'fields_manager'    => $fm,
            'field_definitions' => $this->form->get_fields(),
        ] );
    }
}
