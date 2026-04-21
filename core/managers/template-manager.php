<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Core\Managers;

use DirectoriesBuilderPro\Modules\Template\Loader\Template_Loader;
use DirectoriesBuilderPro\Modules\Template\Renderer\Template_Renderer;
use DirectoriesBuilderPro\Modules\Template\Contracts\Contract_Validator;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template_Manager class.
 *
 * Orchestrates the Template Module components (Loader, Renderer, Validator).
 * Handles the template_include filter for CPT overrides.
 *
 * @package DirectoriesBuilderPro\Core\Managers
 */
class Template_Manager {

    /**
     * @var Template_Loader
     */
    private Template_Loader $loader;

    /**
     * @var Template_Renderer
     */
    private Template_Renderer $renderer;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->loader   = new Template_Loader();
        $this->renderer = new Template_Renderer( $this->loader );

        // Register data contracts.
        if ( class_exists( '\DirectoriesBuilderPro\Modules\Template\Contracts\Contract_Validator' ) ) {
            Contract_Validator::register_all();
        }

        add_filter( 'template_include', [ $this, 'override_cpt_templates' ], 20 );
    }

    /**
     * Override WordPress template for dbp_business CPT.
     *
     * @param string $template
     * @return string
     */
    public function override_cpt_templates( string $template ): string {
        if ( is_singular( 'dbp_business' ) ) {
            $path = $this->loader->locate( 'business/single' );
            if ( $path !== null ) {
                return $path;
            }
        }

        if ( is_post_type_archive( 'dbp_business' ) ) {
            $path = $this->loader->locate( 'business/archive' );
            if ( $path !== null ) {
                return $path;
            }
        }

        return $template;
    }

    /**
     * Render a template by slug.
     *
     * @param string $slug
     * @param array  $args
     * @param bool   $echo
     * @return string
     */
    public function render( string $slug, array $args = [], bool $echo = true ): string {
        return $this->renderer->render( $slug, $args, $echo );
    }

    /**
     * Get the loader instance.
     *
     * @return Template_Loader
     */
    public function get_loader(): Template_Loader {
        return $this->loader;
    }

    /**
     * Get the renderer instance.
     *
     * @return Template_Renderer
     */
    public function get_renderer(): Template_Renderer {
        return $this->renderer;
    }
}
