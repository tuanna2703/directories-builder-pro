<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Review Form (Legacy Redirect)
 *
 * @deprecated Use dbp_template('reviews/form', ['business_id' => $id]).
 *             This file is kept for backward compatibility.
 *
 * @var int $business_id The business ID (from calling scope).
 *
 * @package DirectoriesBuilderPro\Modules\Reviews\Templates
 */

$__business_id = $business_id ?? 0;

if ( function_exists( 'dbp_template' ) ) {
    dbp_template( 'reviews/form', [
        'business_id' => (int) $__business_id,
    ] );
} else {
    echo '<!-- DBP: Template Module not loaded -->';
}