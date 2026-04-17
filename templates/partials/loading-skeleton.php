<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Loading Skeleton
 *
 * Renders pulsing placeholder elements that mimic business cards or review items
 * while content is loading.
 *
 * @slug     partials/loading-skeleton
 * @version  1.0.0
 *
 * @args optional: count (int, default 3) — number of skeleton items to render
 *                 type (string: 'card'|'list', default 'card') — skeleton layout type
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$count = (int) ( $args['count'] ?? 3 );
$type  = $args['type'] ?? 'card';

// Validate type.
if ( ! in_array( $type, [ 'card', 'list' ], true ) ) {
    $type = 'card';
}

// Clamp count to reasonable range.
$count = max( 1, min( 12, $count ) );
?>
<div class="dbp-skeleton dbp-skeleton--<?php echo esc_attr( $type ); ?>" aria-hidden="true" role="presentation">
    <?php if ( $type === 'card' ) : ?>
        <?php // Card skeleton: mimics business card layout. ?>
        <div class="dbp-skeleton__grid">
            <?php for ( $i = 0; $i < $count; $i++ ) : ?>
                <div class="dbp-skeleton-card">
                    <div class="dbp-skeleton-card__image dbp-skeleton__pulse"></div>
                    <div class="dbp-skeleton-card__content">
                        <div class="dbp-skeleton-card__line dbp-skeleton-card__line--title dbp-skeleton__pulse"></div>
                        <div class="dbp-skeleton-card__line dbp-skeleton-card__line--stars dbp-skeleton__pulse"></div>
                        <div class="dbp-skeleton-card__line dbp-skeleton__pulse"></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php else : ?>
        <?php // List skeleton: mimics review item layout. ?>
        <div class="dbp-skeleton__list">
            <?php for ( $i = 0; $i < $count; $i++ ) : ?>
                <div class="dbp-skeleton-item">
                    <div class="dbp-skeleton-item__avatar dbp-skeleton__pulse"></div>
                    <div class="dbp-skeleton-item__content">
                        <div class="dbp-skeleton-item__line dbp-skeleton-item__line--short dbp-skeleton__pulse"></div>
                        <div class="dbp-skeleton-item__line dbp-skeleton-item__line--stars dbp-skeleton__pulse"></div>
                        <div class="dbp-skeleton-item__line dbp-skeleton__pulse"></div>
                        <div class="dbp-skeleton-item__line dbp-skeleton__pulse"></div>
                        <div class="dbp-skeleton-item__line dbp-skeleton-item__line--short dbp-skeleton__pulse"></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
