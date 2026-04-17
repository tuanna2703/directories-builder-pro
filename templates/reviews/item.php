<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Review Item
 *
 * Displays a single review with avatar, rating, text, photos, votes, owner response.
 *
 * @slug     reviews/item
 * @version  1.0.0
 *
 * @args required: review (array — keys: id, user_id, rating, content, created_at,
 *                 helpful, not_helpful, photos, owner_response, author_name,
 *                 author_avatar, is_elite, time_ago, response, response_date, photo_urls)
 * @args optional: current_user_has_voted (bool)
 *                 is_business_owner (bool)
 *
 * @package DirectoriesBuilderPro\Templates\Reviews
 */
$review    = $args['review'] ?? [];
$review_id = (int) ( $review['id'] ?? 0 );
$user_id   = (int) ( $review['user_id'] ?? 0 );

$author_name   = $review['author_name'] ?? '';
$author_avatar = $review['author_avatar'] ?? '';
$is_elite      = $review['is_elite'] ?? false;
$rating        = (int) ( $review['rating'] ?? 0 );
$time_ago      = $review['time_ago'] ?? ( isset( $review['created_at'] ) ? dbp_time_ago( $review['created_at'] ) : '' );
$content       = $review['content'] ?? '';
$helpful       = (int) ( $review['helpful'] ?? 0 );
$not_helpful   = (int) ( $review['not_helpful'] ?? 0 );
$photos        = $review['photos'] ?? '';
$response      = $review['response'] ?? $review['owner_response'] ?? '';
$response_date = $review['response_date'] ?? '';

// Parse photo URLs.
$photo_urls = [];
if ( ! empty( $review['photo_urls'] ) ) {
    $photo_urls = $review['photo_urls'];
} elseif ( ! empty( $photos ) && is_string( $photos ) ) {
    $ids = array_map( 'absint', explode( ',', $photos ) );
    foreach ( $ids as $pid ) {
        $url = wp_get_attachment_image_url( $pid, 'thumbnail' );
        if ( $url ) {
            $photo_urls[] = $url;
        }
    }
}

// Vote status.
$has_voted = $args['current_user_has_voted'] ?? false;
if ( ! $has_voted ) {
    $current_user_id = get_current_user_id();
    if ( $current_user_id && $review_id ) {
        $repo      = new \DirectoriesBuilderPro\Repositories\Review_Repository();
        $has_voted = $repo->has_voted( $review_id, $current_user_id );
    }
}
?>
<div class="dbp-review-item" id="dbp-review-<?php echo esc_attr( (string) $review_id ); ?>" data-review-id="<?php echo esc_attr( (string) $review_id ); ?>">
    <div class="dbp-review-item__header">
        <div class="dbp-review-item__avatar">
            <?php if ( $author_avatar ) : ?>
                <img src="<?php echo esc_url( $author_avatar ); ?>" alt="<?php echo esc_attr( $author_name ); ?>" width="40" height="40" class="dbp-review-item__avatar-img">
            <?php else :
                dbp_template( 'partials/avatar', [ 'user_id' => $user_id, 'size' => 40 ] );
            endif; ?>
        </div>
        <div class="dbp-review-item__author-info">
            <span class="dbp-review-item__author-name">
                <?php echo esc_html( $author_name ?: __( 'Anonymous', 'directories-builder-pro' ) ); ?>
            </span>
            <?php if ( $is_elite ) :
                dbp_template( 'partials/badge', [ 'type' => 'elite' ] );
            endif; ?>
            <div class="dbp-review-item__meta">
                <?php dbp_template( 'partials/star-rating', [ 'rating' => (float) $rating, 'show_number' => false ] ); ?>
                <span class="dbp-review-item__date"><?php echo esc_html( $time_ago ); ?></span>
            </div>
        </div>
    </div>

    <div class="dbp-review-item__content">
        <?php if ( mb_strlen( $content ) > 300 ) : ?>
            <p class="dbp-review-item__text dbp-review-item__text--truncated" id="dbp-review-text-<?php echo esc_attr( (string) $review_id ); ?>">
                <?php echo esc_html( mb_substr( $content, 0, 300 ) ); ?>&hellip;
            </p>
            <p class="dbp-review-item__text dbp-review-item__text--full" style="display:none;" id="dbp-review-full-<?php echo esc_attr( (string) $review_id ); ?>">
                <?php echo esc_html( $content ); ?>
            </p>
            <button type="button" class="dbp-review-item__read-more" data-review-id="<?php echo esc_attr( (string) $review_id ); ?>">
                <?php esc_html_e( 'Read more', 'directories-builder-pro' ); ?>
            </button>
        <?php else : ?>
            <p class="dbp-review-item__text"><?php echo esc_html( $content ); ?></p>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $photo_urls ) ) : ?>
        <div class="dbp-review-item__photos">
            <?php
            $display_count = min( 3, count( $photo_urls ) );
            for ( $i = 0; $i < $display_count; $i++ ) :
                ?>
                <a href="<?php echo esc_url( $photo_urls[ $i ] ); ?>" class="dbp-review-item__photo dbp-lightbox-trigger">
                    <img src="<?php echo esc_url( $photo_urls[ $i ] ); ?>" alt="" loading="lazy" width="100" height="100">
                </a>
            <?php endfor; ?>
            <?php if ( count( $photo_urls ) > 3 ) : ?>
                <span class="dbp-review-item__more-photos">
                    <?php
                    /* translators: %d: number of additional photos */
                    echo esc_html( sprintf( __( '+%d more', 'directories-builder-pro' ), count( $photo_urls ) - 3 ) );
                    ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="dbp-review-item__actions">
        <button type="button"
                class="dbp-vote-btn dbp-vote-btn--helpful <?php echo $has_voted ? 'dbp-vote-btn--disabled' : ''; ?>"
                data-review-id="<?php echo esc_attr( (string) $review_id ); ?>"
                data-vote="helpful"
                <?php echo $has_voted ? 'disabled' : ''; ?>>
            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z" fill="currentColor"/></svg>
            <?php esc_html_e( 'Helpful', 'directories-builder-pro' ); ?>
            <span class="dbp-vote-btn__count" id="dbp-helpful-<?php echo esc_attr( (string) $review_id ); ?>">(<?php echo esc_html( (string) $helpful ); ?>)</span>
        </button>
        <button type="button"
                class="dbp-vote-btn dbp-vote-btn--not-helpful <?php echo $has_voted ? 'dbp-vote-btn--disabled' : ''; ?>"
                data-review-id="<?php echo esc_attr( (string) $review_id ); ?>"
                data-vote="not_helpful"
                <?php echo $has_voted ? 'disabled' : ''; ?>>
            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M15 3H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05c-.09.23-.14.47-.14.73v2c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2zm4 0v12h4V3h-4z" fill="currentColor"/></svg>
            <span class="dbp-vote-btn__count" id="dbp-not-helpful-<?php echo esc_attr( (string) $review_id ); ?>">(<?php echo esc_html( (string) $not_helpful ); ?>)</span>
        </button>
        <?php if ( is_user_logged_in() ) : ?>
            <button type="button" class="dbp-flag-btn" data-review-id="<?php echo esc_attr( (string) $review_id ); ?>">
                <?php esc_html_e( 'Report', 'directories-builder-pro' ); ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $response ) ) : ?>
        <div class="dbp-review-item__response">
            <div class="dbp-review-item__response-header">
                <?php dbp_template( 'partials/badge', [ 'type' => 'claimed', 'label' => __( 'Business Owner', 'directories-builder-pro' ) ] ); ?>
                <?php if ( $response_date ) : ?>
                    <span class="dbp-review-item__response-date"><?php echo esc_html( dbp_time_ago( $response_date ) ); ?></span>
                <?php endif; ?>
            </div>
            <p class="dbp-review-item__response-text"><?php echo esc_html( $response ); ?></p>
        </div>
    <?php endif; ?>
</div>
