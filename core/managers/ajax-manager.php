<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Managers;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Ajax_Manager class.
 *
 * Central registry for WordPress AJAX handlers.
 * Modules register their AJAX actions through this manager.
 *
 * @package DirectoriesBuilderPro\Core\Managers
 */
class Ajax_Manager {
    /**
     * Registered handlers.
     *
     * @var array<string, array{callback: callable, nopriv: bool}>
     */
    private array $handlers = [];
    /**
     * Register an AJAX handler.
     *
     * @param string   $action   AJAX action name (without wp_ajax_ prefix).
     * @param callable $callback Handler callback.
     * @param bool     $nopriv   Whether to also register for non-logged-in users.
     * @return void
     */
    public function register( string $action, callable $callback, bool $nopriv = false ): void {
        $this->handlers[ $action ] = [
            'callback' => $callback,
            'nopriv'   => $nopriv,
        ];
        add_action( "wp_ajax_{$action}", $callback );
        if ( $nopriv ) {
            add_action( "wp_ajax_nopriv_{$action}", $callback );
        }
    }
    /**
     * Get all registered handlers.
     *
     * @return array<string, array{callback: callable, nopriv: bool}>
     */
    public function get_handlers(): array {
        return $this->handlers;
    }
}
