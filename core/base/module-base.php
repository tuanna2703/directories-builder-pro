<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Abstract Module_Base class.
 *
 * Every feature module must extend this class and implement
 * get_name() and init(). The constructor automatically calls init().
 *
 * @package DirectoriesBuilderPro\Core\Base
 */
abstract class Module_Base {
    /**
     * Get the unique module name identifier.
     *
     * @return string
     */
    abstract public function get_name(): string;
    /**
     * Initialize the module — register hooks, controllers, AJAX handlers.
     *
     * @return void
     */
    abstract protected function init(): void;
    /**
     * Constructor — automatically calls init().
     */
    public function __construct() {
        $this->init();
    }
}
