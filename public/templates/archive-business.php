<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Business Archive (Legacy Redirect)
 *
 * @deprecated Use dbp_template('business/archive') or Template_Module resolution.
 *             This file is kept for backward compatibility only.
 *
 * @package DirectoriesBuilderPro\Public\Templates
 */

if ( function_exists( 'dbp_template' ) ) {
    $loader = new \DirectoriesBuilderPro\Modules\Template\Loader\Template_Loader();
    $path   = $loader->locate( 'business/archive' );
    if ( $path ) {
        $args = [];
        include $path;
        return;
    }
}

get_header();
echo '<div class="dbp-container"><p>' . esc_html__( 'Template not found.', 'directories-builder-pro' ) . '</p></div>';
get_footer();