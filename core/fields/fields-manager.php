<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Fields;
use DirectoriesBuilderPro\Core\Fields\Types\Text_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Textarea_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Number_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Email_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Url_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Password_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Select_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Multi_Select_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Radio_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Checkbox_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Toggle_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Color_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Media_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Repeater_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Hidden_Field;
use DirectoriesBuilderPro\Core\Fields\Types\Heading_Field;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Fields_Manager class.
 *
 * Central registry and factory for all field types.
 *
 * @package DirectoriesBuilderPro\Core\Fields
 */
class Fields_Manager {
    // Type slug constants.
    const TEXT         = 'text';
    const TEXTAREA     = 'textarea';
    const NUMBER       = 'number';
    const EMAIL        = 'email';
    const URL          = 'url';
    const PASSWORD     = 'password';
    const SELECT       = 'select';
    const MULTI_SELECT = 'multi_select';
    const RADIO        = 'radio';
    const CHECKBOX     = 'checkbox';
    const TOGGLE       = 'toggle';
    const COLOR        = 'color';
    const MEDIA        = 'media';
    const REPEATER     = 'repeater';
    const HIDDEN       = 'hidden';
    const HEADING      = 'heading';
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;
    /**
     * Registered field type instances.
     *
     * @var array<string, Field_Base>
     */
    private array $types = [];
    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Register a field type instance.
     *
     * @param Field_Base $field Field type instance.
     * @return void
     */
    public function register_type( Field_Base $field ): void {
        $this->types[ $field->get_type() ] = $field;
    }
    /**
     * Get a registered field type by slug.
     *
     * @param string $type Field type slug.
     * @return Field_Base|null
     */
    public function get_type( string $type ): ?Field_Base {
        return $this->types[ $type ] ?? null;
    }
    /**
     * Factory method: return registered Field_Base or throw if unknown.
     *
     * @param string $type Field type slug.
     * @return Field_Base
     * @throws \InvalidArgumentException If the type is not registered.
     */
    public function make( string $type ): Field_Base {
        $instance = $this->get_type( $type );
        if ( $instance === null ) {
            throw new \InvalidArgumentException(
                sprintf( 'Unknown field type: %s', $type )
            );
        }
        return $instance;
    }
    /**
     * Register all 16 built-in field types.
     *
     * Fires the dbp/form/register_field_types filter to allow third-party
     * registration of custom field types.
     *
     * @return void
     */
    public function register_defaults(): void {
        $defaults = [
            new Text_Field(),
            new Textarea_Field(),
            new Number_Field(),
            new Email_Field(),
            new Url_Field(),
            new Password_Field(),
            new Select_Field(),
            new Multi_Select_Field(),
            new Radio_Field(),
            new Checkbox_Field(),
            new Toggle_Field(),
            new Color_Field(),
            new Media_Field(),
            new Repeater_Field(),
            new Hidden_Field(),
            new Heading_Field(),
        ];
        foreach ( $defaults as $field ) {
            $this->register_type( $field );
        }
        /**
         * Filter: allow third-party code to register custom field types.
         *
         * @param Fields_Manager $manager The fields manager instance.
         */
        apply_filters( 'dbp/form/register_field_types', $this );
    }
    /**
     * Get all registered type slugs.
     *
     * @return array<string>
     */
    public function get_registered_types(): array {
        return array_keys( $this->types );
    }
}
