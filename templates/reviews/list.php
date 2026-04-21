<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Review List (Legacy Redirect)
 *
 * @deprecated Use dbp_template('reviews/list', [...]).
 *             This file is kept for backward compatibility.
 *
 * @var int   $business_id The business ID (from calling scope).
 * @var array $reviews     Array of review data (from calling scope).
 * @var int   $total       Total review count (from calling scope).
 *
 * @package DirectoriesBuilderPro\Modules\Reviews\Templates
 */

$__business_id = $business_id ?? 0;
$__reviews     = $reviews ?? [];
$__total       = $total ?? count( $__reviews );

if ( function_exists( 'dbp_template' ) ) {
    dbp_template( 'reviews/list', [
        'reviews'     => $__reviews,
        'business_id' => (int) $__business_id,
        'total'       => (int) $__total,
    ] );
} else {
    echo '<!-- DBP: Template Module not loaded -->';
}