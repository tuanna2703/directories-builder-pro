<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Base;
use WP_REST_Response;
use WP_Error;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Abstract Controller_Base class.
 *
 * Provides REST route registration helpers and standard response builders.
 * All module controllers extend this class.
 *
 * @package DirectoriesBuilderPro\Core\Base
 */
abstract class Controller_Base {
    /**
     * REST API namespace.
     *
     * @var string
     */
    protected string $namespace = 'directories-builder-pro/v1';
    /**
     * Register all routes for the controller.
     *
     * @return void
     */
    abstract public function register_routes(): void;
    /**
     * Register a single REST route.
     *
     * @param string $path  Route path (e.g. '/businesses').
     * @param array  $args  Route arguments compatible with register_rest_route().
     * @return void
     */
    protected function register_route( string $path, array $args ): void {
        register_rest_route( $this->namespace, $path, $args );
    }
    /**
     * Return a success response.
     *
     * @param mixed $data    Response data.
     * @param int   $status  HTTP status code.
     * @return WP_REST_Response
     */
    protected function success( mixed $data, int $status = 200 ): WP_REST_Response {
        return new WP_REST_Response( $data, $status );
    }
    /**
     * Return an error response.
     *
     * @param string $message Error message.
     * @param int    $code    HTTP status code.
     * @param string $error_code Error code string.
     * @return WP_Error
     */
    protected function error( string $message, int $code = 400, string $error_code = 'rest_error' ): WP_Error {
        return new WP_Error( $error_code, $message, [ 'status' => $code ] );
    }
}