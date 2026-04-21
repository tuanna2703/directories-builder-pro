<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Review Item (Legacy Redirect)
 *
 * @deprecated Use dbp_template('reviews/item', ['review' => $data]).
 *             This file is kept for backward compatibility.
 *
 * @var array $review      Review data array (from calling scope).
 * @var int   $business_id Business ID (from calling scope).
 *
 * @package DirectoriesBuilderPro\Public\Partials
 */

$__review = $review ?? [];

if ( function_exists( 'dbp_template' ) ) {
    dbp_template( 'reviews/item', [ 'review' => $__review ] );
} else {
    echo '<!-- DBP: Template Module not loaded -->';
}