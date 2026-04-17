<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Search Results (Legacy Redirect)
 *
 * @deprecated Use dbp_template('search/results').
 *             This file is kept for backward compatibility.
 *
 * @package DirectoriesBuilderPro\Modules\Search\Templates
 */

if ( function_exists( 'dbp_template' ) ) {
    dbp_template( 'search/results' );
} else {
    echo '<!-- DBP: Template Module not loaded -->';
}