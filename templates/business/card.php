<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Business Card (Legacy Redirect)
 *
 * @deprecated Use dbp_template('business/card', ['business' => $data]).
 *             This file is kept for backward compatibility. It reads the legacy
 *             $business / $business_item variables from the calling scope and
 *             delegates to the new template.
 *
 * @var array $business      Business data array (from calling scope).
 * @var array $business_item Alias (from calling scope).
 *
 * @package DirectoriesBuilderPro\Public\Partials
 */

// Resolve legacy variable from the calling scope.
$__business = $business ?? $business_item ?? [];

if ( function_exists( 'dbp_template' ) ) {
    dbp_template( 'business/card', [ 'business' => $__business ] );
} else {
    // Absolute fallback — should never happen in production.
    echo '<!-- DBP: Template Module not loaded -->';
}