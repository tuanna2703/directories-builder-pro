<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Base;
use DirectoriesBuilderPro\Core\Fields\Fields_Manager;
use DirectoriesBuilderPro\Core\Fields\Field_Base;
use DirectoriesBuilderPro\Core\Fields\Types\Repeater_Field;
use DirectoriesBuilderPro\Modules\Form\Storage\Storage_Interface;
use DirectoriesBuilderPro\Modules\Form\Storage\Options_Storage;
use DirectoriesBuilderPro\Modules\Form\Storage\Post_Meta_Storage;
use DirectoriesBuilderPro\Modules\Form\Storage\User_Meta_Storage;
use DirectoriesBuilderPro\Modules\Form\Renderer\Form_Renderer;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Abstract Form_Base class.
 *
 * Equivalent to Elementor's Widget_Base. Every Form Definition extends this
 * class and implements register_fields() to declare its schema.
 *
 * @package DirectoriesBuilderPro\Core\Base
 */
abstract class Form_Base {
    /**
     * Ordered list of field groups.
     *
     * @var array
     */
    protected array $groups = [];
    /**
     * Flat map of field_id => field definition.
     *
     * @var array
     */
    protected array $fields = [];
    /**
     * Current group being built (used during register_fields).
     *
     * @var string|null
     */
    private ?string $current_group = null;
    /**
     * Whether register_fields() has been called.
     *
     * @var bool
     */
    private bool $fields_registered = false;
    /**
     * Get the unique form slug.
     *
     * @return string
     */
    abstract public function get_name(): string;
    /**
     * Get human-readable form title.
     *
     * @return string
     */
    abstract public function get_title(): string;
    /**
     * Get the storage type: 'options', 'post_meta', or 'user_meta'.
     *
     * @return string
     */
    abstract public function get_storage_type(): string;
    /**
     * Declare all field groups and fields.
     *
     * @return void
     */
    abstract protected function register_fields(): void;
    /**
     * Start a field group (section).
     *
     * @param string $id   Group ID.
     * @param array  $args Group args: label (required), description, tab.
     * @return void
     */
    public function start_group( string $id, array $args ): void {
        $this->current_group = $id;
        $this->groups[ $id ] = [
            'id'          => $id,
            'label'       => $args['label'] ?? '',
            'description' => $args['description'] ?? '',
            'tab'         => $args['tab'] ?? 'general',
            'fields'      => [],
        ];
    }
    /**
     * End the current field group.
     *
     * @return void
     */
    public function end_group(): void {
        $this->current_group = null;
    }
    /**
     * Add a field to the current group.
     *
     * @param string $id   Unique field ID.
     * @param array  $args Field definition: type (required), label, default,
     *                     description, placeholder, required, condition,
     *                     options, fields (for repeater), min, max, step,
     *                     sanitize (callable override).
     * @return void
     */
    public function add_field( string $id, array $args ): void {
        $args['id'] = $id;
        $this->fields[ $id ] = $args;
        if ( $this->current_group !== null && isset( $this->groups[ $this->current_group ] ) ) {
            $this->groups[ $this->current_group ]['fields'][] = $id;
        }
    }
    /**
     * Ensure fields are registered.
     *
     * @return void
     */
    public function ensure_fields(): void {
        if ( ! $this->fields_registered ) {
            $this->register_fields();
            $this->fields_registered = true;
        }
    }
    /**
     * Get the full JSON-safe schema: groups + fields + defaults.
     *
     * @return array
     */
    public function get_schema(): array {
        $this->ensure_fields();
        $fm     = Fields_Manager::instance();
        $schema = [
            'name'   => $this->get_name(),
            'title'  => $this->get_title(),
            'groups' => [],
        ];
        foreach ( $this->groups as $group_id => $group ) {
            $group_schema = [
                'id'          => $group_id,
                'label'       => $group['label'],
                'description' => $group['description'],
                'tab'         => $group['tab'],
                'fields'      => [],
            ];
            foreach ( $group['fields'] as $field_id ) {
                $field_def = $this->fields[ $field_id ] ?? null;
                if ( $field_def === null ) {
                    continue;
                }
                $type_instance = $fm->get_type( $field_def['type'] ?? 'text' );
                if ( $type_instance ) {
                    $group_schema['fields'][] = $type_instance->get_schema( $field_def );
                }
            }
            $schema['groups'][] = $group_schema;
        }
        /**
         * Filter: modify form schema before render/REST response.
         *
         * @param array $schema The form schema.
         */
        return apply_filters( 'dbp/form/schema/' . $this->get_name(), $schema );
    }
    /**
     * Get stored values merged with defaults.
     *
     * @param int|null $object_id Object ID for post_meta/user_meta forms.
     * @return array
     */
    public function get_values( ?int $object_id = null ): array {
        $this->ensure_fields();
        $storage = $this->get_storage_adapter();
        $key     = $this->get_storage_key();
        $stored  = $storage->get( $key, $object_id );
        if ( ! is_array( $stored ) ) {
            $stored = [];
        }
        // Migration: for options storage, check legacy individual keys.
        if ( $this->get_storage_type() === 'options' && empty( $stored ) ) {
            $stored = $this->migrate_legacy_options();
        }
        // Merge with defaults.
        $fm     = Fields_Manager::instance();
        $values = [];
        foreach ( $this->fields as $field_id => $field_def ) {
            $type_instance = $fm->get_type( $field_def['type'] ?? 'text' );
            $default       = $type_instance ? $type_instance->get_default( $field_def ) : null;
            $values[ $field_id ] = $stored[ $field_id ] ?? $default;
        }
        /**
         * Filter: modify values after retrieval.
         *
         * @param array    $values    The merged values.
         * @param int|null $object_id The object ID.
         */
        return apply_filters( 'dbp/form/values/' . $this->get_name(), $values, $object_id );
    }
    /**
     * Sanitize, validate, and persist form values.
     *
     * @param array    $raw_values Raw values from user input.
     * @param int|null $object_id  Object ID for post_meta/user_meta forms.
     * @return true|WP_Error
     */
    public function save( array $raw_values, ?int $object_id = null ): true|WP_Error {
        $this->ensure_fields();
        /**
         * Action: fires before save begins.
         *
         * @param string   $form_name  The form name.
         * @param array    $raw_values Raw input values.
         * @param int|null $object_id  The object ID.
         */
        do_action( 'dbp/form/before_save', $this->get_name(), $raw_values, $object_id );
        $fm         = Fields_Manager::instance();
        $sanitized  = [];
        $errors     = new WP_Error();
        foreach ( $this->fields as $field_id => $field_def ) {
            $type_slug = $field_def['type'] ?? 'text';
            // Skip heading fields — display only, never stored.
            if ( $type_slug === 'heading' ) {
                continue;
            }
            $type_instance = $fm->get_type( $type_slug );
            if ( ! $type_instance ) {
                continue;
            }
            $raw = $raw_values[ $field_id ] ?? null;
            // For repeater fields, pass sub-field definitions.
            if ( $type_instance instanceof Repeater_Field && ! empty( $field_def['fields'] ) ) {
                $type_instance->set_sub_fields( $field_def['fields'] );
            }
            // Sanitize.
            if ( isset( $field_def['sanitize'] ) && is_callable( $field_def['sanitize'] ) ) {
                $clean = call_user_func( $field_def['sanitize'], $raw );
            } else {
                /**
                 * Filter: custom sanitizer per type.
                 *
                 * @param mixed $raw   The raw value.
                 * @param array $field The field definition.
                 */
                $clean = apply_filters( 'dbp/form/sanitize/' . $type_slug, $type_instance->sanitize( $raw ), $field_def );
            }
            // Validate.
            $validation = $type_instance->validate( $field_def, $clean );
            if ( $validation instanceof WP_Error ) {
                foreach ( $validation->get_error_messages() as $msg ) {
                    $errors->add( 'validation_' . $field_id, $msg );
                }
            }
            $sanitized[ $field_id ] = $clean;
        }
        if ( $errors->has_errors() ) {
            return $errors;
        }
        // Persist.
        $storage = $this->get_storage_adapter();
        $key     = $this->get_storage_key();
        $result  = $storage->set( $key, $sanitized, $object_id );
        if ( ! $result ) {
            return new WP_Error( 'save_failed', __( 'Failed to save form data.', 'directories-builder-pro' ) );
        }
        /**
         * Action: fires after a form is successfully saved.
         *
         * @param string   $form_name The form name.
         * @param array    $values    The sanitized values.
         * @param int|null $object_id The object ID.
         */
        do_action( 'dbp/form/saved', $this->get_name(), $sanitized, $object_id );
        return true;
    }
    /**
     * Render the full form HTML.
     *
     * @param int|null $object_id Object ID for post_meta/user_meta forms.
     * @return void
     */
    public function render_form( ?int $object_id = null ): void {
        $this->ensure_fields();
        $renderer = new Form_Renderer( $this );
        $renderer->render( $object_id );
    }
    /**
     * Get the storage key for this form.
     *
     * @return string
     */
    public function get_storage_key(): string {
        return 'dbp_' . $this->get_name();
    }
    /**
     * Factory: return the appropriate storage adapter.
     *
     * @return Storage_Interface
     */
    protected function get_storage_adapter(): Storage_Interface {
        return match ( $this->get_storage_type() ) {
            'post_meta' => new Post_Meta_Storage(),
            'user_meta' => new User_Meta_Storage(),
            default     => new Options_Storage(),
        };
    }
    /**
     * Get the fields definitions.
     *
     * @return array
     */
    public function get_fields(): array {
        $this->ensure_fields();
        return $this->fields;
    }
    /**
     * Get the groups definitions.
     *
     * @return array
     */
    public function get_groups(): array {
        $this->ensure_fields();
        return $this->groups;
    }
    /**
     * Migrate legacy individual wp_options to new consolidated format.
     *
     * Override in specific form definitions that need migration.
     *
     * @return array
     */
    protected function migrate_legacy_options(): array {
        return [];
    }
}
