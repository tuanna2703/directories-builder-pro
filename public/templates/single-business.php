<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Single Business Page (Legacy Redirect)
 *
 * @deprecated Use dbp_template('business/single') or Template_Module resolution.
 *             This file is kept for backward compatibility only.
 *
 * @package DirectoriesBuilderPro\Public\Templates
 */

// Delegate to the centralized Template Module.
// The business/single template self-prepares $args when loaded as a CPT override.
$template_path = \DirectoriesBuilderPro\Modules\Template\Loader\Template_Loader::class;

// If Template_Module is loaded, use it. Otherwise fall back.
if ( function_exists( 'dbp_template' ) ) {
    // The business/single template handles get_header/get_footer internally
    // so we include it directly via the loader to avoid double-buffering.
    $loader = new \DirectoriesBuilderPro\Modules\Template\Loader\Template_Loader();
    $path   = $loader->locate( 'business/single' );
    if ( $path ) {
        $args = [];
        include $path;
        return;
    }
}

// Ultimate fallback: show error.
get_header();
echo '<div class="dbp-container"><p>' . esc_html__( 'Template not found.', 'directories-builder-pro' ) . '</p></div>';
get_footer();