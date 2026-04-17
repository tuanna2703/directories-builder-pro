<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Template\Renderer;

use DirectoriesBuilderPro\Modules\Template\Loader\Template_Loader;
use DirectoriesBuilderPro\Modules\Template\Contracts\Contract_Validator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template_Renderer — Output buffered template rendering with hooks.
 *
 * Renders a template by slug through the Template_Loader, wrapping the include
 * in output buffering and firing before/after action hooks and arg filters.
 *
 * Templates receive data as a single $args array (no extract()) to prevent
 * variable collisions and keep data contracts explicit.
 *
 * @package DirectoriesBuilderPro\Modules\Template\Renderer
 * @since   1.0.0
 */
class Template_Renderer {

    /**
     * Template loader instance.
     *
     * @var Template_Loader
     */
    private Template_Loader $loader;

    /**
     * Constructor.
     *
     * @param Template_Loader $loader Loader instance for path resolution.
     */
    public function __construct( Template_Loader $loader ) {
        $this->loader = $loader;
    }

    /**
     * Render a template by slug.
     *
     * @param string $slug Template slug (e.g., 'business/card', 'partials/badge').
     * @param array  $args Data to pass to the template via $args variable.
     * @param bool   $echo Whether to echo the output (true) or just return it (false).
     * @return string The rendered HTML output.
     */
    public function render( string $slug, array $args = [], bool $echo = true ): string {
        // Resolve the template path.
        $path = $this->loader->locate( $slug );

        if ( $path === null ) {
            /**
             * Fires when a template slug cannot be resolved to a file.
             *
             * @param string $slug The template slug that was not found.
             * @param array  $args The args that were passed.
             */
            do_action( 'dbp/template/missing', $slug, $args );

            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                $comment = '<!-- DBP: template not found: ' . esc_html( $slug ) . ' -->';
                if ( $echo ) {
                    echo $comment; // phpcs:ignore WordPress.Security.EscapeOutput
                }
                return $comment;
            }

            return '';
        }

        /**
         * Filter the template args before the template is included.
         *
         * @param array  $args Template arguments.
         * @param string $slug Template slug.
         * @param string $path Resolved template file path.
         */
        $args = apply_filters( 'dbp/template/args', $args, $slug, $path );

        // Contract validation (dev mode only — zero cost in production).
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            Contract_Validator::check( $slug, $args );
        }

        // Render with output buffering.
        ob_start();

        /**
         * Fires before a template is included.
         *
         * @param string $slug Template slug.
         * @param string $path Resolved template file path.
         * @param array  $args Template arguments.
         */
        do_action( 'dbp/template/before', $slug, $path, $args );

        /**
         * Fires before a specific template is included.
         *
         * @param string $path Resolved template file path.
         * @param array  $args Template arguments.
         */
        do_action( "dbp/template/before/{$slug}", $path, $args );

        $this->include_template( $path, $args );

        /**
         * Fires after a template is included.
         *
         * @param string $slug Template slug.
         * @param string $path Resolved template file path.
         * @param array  $args Template arguments.
         */
        do_action( 'dbp/template/after', $slug, $path, $args );

        /**
         * Fires after a specific template is included.
         *
         * @param string $path Resolved template file path.
         * @param array  $args Template arguments.
         */
        do_action( "dbp/template/after/{$slug}", $path, $args );

        $output = ob_get_clean();

        if ( $echo ) {
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
        }

        return $output;
    }

    /**
     * Include a template file with isolated scope.
     *
     * Templates access data exclusively via $args['key']. No extract() is used
     * to prevent variable collisions and make data contracts explicit.
     *
     * @param string $__path Absolute path to the template file.
     * @param array  $args   Data array passed to the template.
     * @return void
     */
    private function include_template( string $__path, array $args ): void {
        // Use $__path to avoid collision with any $args keys.
        // The template file receives $args as its only variable.
        include $__path;
    }
}
