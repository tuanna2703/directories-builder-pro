<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Partial: Star Rating
 *
 * Renders an accessible star rating component with filled, half, and empty SVG stars.
 *
 * @slug     partials/star-rating
 * @version  1.0.0
 *
 * @args required: rating (float 0–5)
 * @args optional: show_number (bool, default true) — show numeric rating value
 *                 count (int) — total number of reviews to display
 *
 * @package DirectoriesBuilderPro\Templates\Partials
 */
$rating      = (float) ( $args['rating'] ?? 0 );
$show_number = (bool) ( $args['show_number'] ?? true );
$count       = isset( $args['count'] ) ? (int) $args['count'] : null;

// Clamp rating to 0–5 range.
$rating = max( 0.0, min( 5.0, $rating ) );
$rating_label = number_format( $rating, 1 );
?>
<div class="dbp-star-rating" aria-label="<?php echo esc_attr( sprintf(
    /* translators: %s: numeric rating value */
    __( 'Rated %s out of 5 stars', 'directories-builder-pro' ),
    $rating_label
) ); ?>">
    <div class="dbp-star-rating__stars">
        <?php for ( $i = 1; $i <= 5; $i++ ) :
            $diff = $rating - ( $i - 1 );
            if ( $diff >= 1 ) :
                // Full star.
                $star_class = 'dbp-star--filled';
                ?>
                <svg class="dbp-star <?php echo esc_attr( $star_class ); ?>" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="currentColor"/>
                </svg>
            <?php elseif ( $diff >= 0.25 && $diff < 0.75 ) :
                // Half star.
                $star_class = 'dbp-star--half';
                ?>
                <svg class="dbp-star <?php echo esc_attr( $star_class ); ?>" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <defs>
                        <linearGradient id="dbp-half-<?php echo esc_attr( (string) $i ); ?>">
                            <stop offset="50%" stop-color="currentColor"/>
                            <stop offset="50%" stop-color="transparent"/>
                        </linearGradient>
                    </defs>
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                             fill="url(#dbp-half-<?php echo esc_attr( (string) $i ); ?>)" stroke="currentColor" stroke-width="0.5"/>
                </svg>
            <?php else :
                // Empty star.
                $star_class = 'dbp-star--empty';
                ?>
                <svg class="dbp-star <?php echo esc_attr( $star_class ); ?>" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                             fill="none" stroke="currentColor" stroke-width="1"/>
                </svg>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php if ( $show_number ) : ?>
        <span class="dbp-star-rating__number"><?php echo esc_html( $rating_label ); ?></span>
    <?php endif; ?>
    <?php if ( $count !== null ) : ?>
        <span class="dbp-star-rating__count">(<?php echo esc_html( (string) $count ); ?>)</span>
    <?php endif; ?>
</div>
