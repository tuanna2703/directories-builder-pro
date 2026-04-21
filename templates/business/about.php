<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Business About (Legacy Redirect)
 *
 * @deprecated Use dbp_template('business/about', ['business' => $data]).
 *             This file is kept for backward compatibility.
 *
 * @var array $business Business data array (from calling scope).
 *
 * @package DirectoriesBuilderPro\Modules\Business\Templates
 */

$__business = $business ?? [];

if ( function_exists( 'dbp_template' ) ) {
    dbp_template( 'business/about', [ 'business' => $__business ] );
} else {
    echo '<!-- DBP: Template Module not loaded -->';
}