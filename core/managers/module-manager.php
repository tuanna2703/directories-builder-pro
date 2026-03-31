<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Managers;
use DirectoriesBuilderPro\Core\Base\Module_Base;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Module_Manager class.
 *
 * Instantiates and stores all feature modules.
 * Provides access to modules by their unique name.
 *
 * @package DirectoriesBuilderPro\Core\Managers
 */
class Module_Manager {
    /**
     * Registered module instances.
     *
     * @var array<string, Module_Base>
     */
    private array $modules = [];
    /**
     * Register an array of module class names.
     *
     * Each class is instantiated (which triggers init()) and stored.
     *
     * @param array<class-string<Module_Base>> $classes Array of fully-qualified class names.
     * @return void
     */
    public function register_modules( array $classes ): void {
        foreach ( $classes as $class ) {
            if ( ! class_exists( $class ) ) {
                continue;
            }
            /** @var Module_Base $module */
            $module = new $class();
            if ( $module instanceof Module_Base ) {
                $this->modules[ $module->get_name() ] = $module;
            }
        }
    }
    /**
     * Get a registered module by name.
     *
     * @param string $name Module name.
     * @return Module_Base|null
     */
    public function get_module( string $name ): ?Module_Base {
        return $this->modules[ $name ] ?? null;
    }
    /**
     * Get all registered modules.
     *
     * @return array<string, Module_Base>
     */
    public function get_modules(): array {
        return $this->modules;
    }
}