<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Search Bar (Legacy Redirect)
 *
 * @deprecated Use dbp_template('search/bar').
 *             This file is kept for backward compatibility.
 *
 * @package DirectoriesBuilderPro\Modules\Search\Templates
 */

if ( function_exists( 'dbp_template' ) ) {
    dbp_template( 'search/bar', [
        'default_query'    => sanitize_text_field( $_GET['q'] ?? '' ),
        'default_location' => sanitize_text_field( $_GET['location'] ?? '' ),
    ] );
} else {
    echo '<!-- DBP: Template Module not loaded -->';
}