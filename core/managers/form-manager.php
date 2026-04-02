<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Managers;
use DirectoriesBuilderPro\Core\Base\Form_Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Form_Manager class.
 *
 * Central registry for Form_Base subclasses. Modules and third-party code
 * register their form definitions here.
 *
 * @package DirectoriesBuilderPro\Core\Managers
 */
class Form_Manager {
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;
    /**
     * Registered form instances.
     *
     * @var array<string, Form_Base>
     */
    private array $forms = [];
    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function get_instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Register a form definition.
     *
     * Calls register_fields() immediately and fires the before_register filter.
     *
     * @param Form_Base $form Form instance.
     * @return void
     */
    public function register( Form_Base $form ): void {
        /**
         * Filter: allow modifying a form before registration.
         *
         * @param Form_Base $form The form instance.
         */
        $form = apply_filters( 'dbp/form/before_register', $form );
        $form->ensure_fields();
        $this->forms[ $form->get_name() ] = $form;
    }
    /**
     * Get a registered form by name.
     *
     * @param string $name Form name slug.
     * @return Form_Base|null
     */
    public function get( string $name ): ?Form_Base {
        return $this->forms[ $name ] ?? null;
    }
    /**
     * Get all registered forms.
     *
     * @return array<string, Form_Base>
     */
    public function get_all(): array {
        return $this->forms;
    }
}
