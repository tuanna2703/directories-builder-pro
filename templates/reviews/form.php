<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Template: Review Form
 *
 * Interactive review submission form with star picker, textarea, photo upload.
 *
 * @slug     reviews/form
 * @version  1.0.0
 *
 * @args required: business_id (int)
 * @args optional: existing_review (array) — pre-populate for edit mode
 *
 * @package DirectoriesBuilderPro\Templates\Reviews
 */
$business_id = (int) ( $args['business_id'] ?? 0 );
$existing    = $args['existing_review'] ?? null;
$min_length  = (int) get_option( 'dbp_min_review_length', 25 );
$max_photos  = (int) get_option( 'dbp_max_photos_per_review', 5 );
$user_id     = get_current_user_id();
?>
<div class="dbp-review-form-wrapper" id="dbp-review-form-wrapper">
    <?php if ( ! is_user_logged_in() ) : ?>
        <?php dbp_template( 'partials/notice', [
            'type'    => 'info',
            'message' => __( 'Log in to write a review.', 'directories-builder-pro' ),
        ] ); ?>
        <div class="dbp-review-form__login-prompt">
            <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="dbp-btn dbp-btn--primary">
                <?php esc_html_e( 'Log In', 'directories-builder-pro' ); ?>
            </a>
        </div>
    <?php else :
        // Check if user already reviewed this business.
        $review_repo  = new \DirectoriesBuilderPro\Repositories\Review_Repository();
        $has_reviewed = $review_repo->user_has_reviewed( $business_id, $user_id );
        ?>
        <?php if ( $has_reviewed && empty( $existing ) ) : ?>
            <div class="dbp-review-form__already-reviewed">
                <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" fill="currentColor"/>
                </svg>
                <p><?php esc_html_e( 'You have already reviewed this business. You can edit your existing review.', 'directories-builder-pro' ); ?></p>
            </div>
        <?php else : ?>
            <div class="dbp-review-form" id="dbp-review-form">
                <h3 class="dbp-review-form__title">
                    <?php esc_html_e( 'Write a Review', 'directories-builder-pro' ); ?>
                </h3>

                <!-- Star Picker -->
                <div class="dbp-star-picker" id="dbp-star-picker" role="radiogroup" aria-label="<?php esc_attr_e( 'Rating', 'directories-builder-pro' ); ?>">
                    <span class="dbp-star-picker__label"><?php esc_html_e( 'Select your rating', 'directories-builder-pro' ); ?></span>
                    <div class="dbp-star-picker__stars">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <button type="button"
                                    class="dbp-star-picker__star"
                                    data-value="<?php echo esc_attr( (string) $i ); ?>"
                                    role="radio"
                                    aria-checked="false"
                                    aria-label="<?php echo esc_attr( sprintf(
                                        /* translators: %d: star rating number */
                                        _n( '%d star', '%d stars', $i, 'directories-builder-pro' ),
                                        $i
                                    ) ); ?>"
                                    tabindex="<?php echo $i === 1 ? '0' : '-1'; ?>">
                                <svg viewBox="0 0 24 24" width="32" height="32">
                                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="currentColor"/>
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <span class="dbp-star-picker__text" id="dbp-rating-text"></span>
                    <input type="hidden" name="rating" id="dbp-rating-value" value="0">
                </div>

                <!-- Review Content -->
                <div class="dbp-review-form__content">
                    <label for="dbp-review-content" class="dbp-review-form__label">
                        <?php esc_html_e( 'Your review', 'directories-builder-pro' ); ?>
                    </label>
                    <textarea id="dbp-review-content"
                              name="content"
                              class="dbp-review-form__textarea"
                              rows="5"
                              minlength="<?php echo esc_attr( (string) $min_length ); ?>"
                              data-min-length="<?php echo esc_attr( (string) $min_length ); ?>"
                              placeholder="<?php esc_attr_e( 'Share your experience with this business...', 'directories-builder-pro' ); ?>"
                              required></textarea>
                    <div class="dbp-review-form__counter" id="dbp-char-counter">
                        <span id="dbp-char-count">0</span> / <?php echo esc_html( (string) $min_length ); ?>
                        <?php esc_html_e( 'minimum characters', 'directories-builder-pro' ); ?>
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="dbp-review-form__photos">
                    <label class="dbp-review-form__label">
                        <?php
                        /* translators: %d: maximum number of photos */
                        echo esc_html( sprintf( __( 'Photos (up to %d)', 'directories-builder-pro' ), $max_photos ) );
                        ?>
                    </label>
                    <div class="dbp-photo-upload" id="dbp-photo-upload">
                        <div class="dbp-photo-upload__dropzone" id="dbp-dropzone">
                            <svg viewBox="0 0 24 24" width="32" height="32" class="dbp-photo-upload__icon" aria-hidden="true">
                                <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" fill="currentColor" opacity="0.5"/>
                            </svg>
                            <p class="dbp-photo-upload__text">
                                <?php esc_html_e( 'Drag photos here or click to upload', 'directories-builder-pro' ); ?>
                            </p>
                            <input type="file"
                                   id="dbp-photo-input"
                                   class="dbp-photo-upload__input"
                                   multiple
                                   accept="image/jpeg,image/png,image/webp"
                                   aria-label="<?php esc_attr_e( 'Upload photos', 'directories-builder-pro' ); ?>">
                        </div>
                        <div class="dbp-photo-upload__previews" id="dbp-photo-previews"></div>
                        <div class="dbp-photo-upload__error" id="dbp-photo-error" style="display:none;"></div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="dbp-review-form__messages" id="dbp-review-messages" style="display:none;"></div>

                <!-- Submit -->
                <div class="dbp-review-form__actions">
                    <?php wp_nonce_field( 'dbp_nonce', 'nonce' ); ?>
                    <input type="hidden" name="business_id" value="<?php echo esc_attr( (string) $business_id ); ?>">
                    <button type="button"
                            class="dbp-btn dbp-btn--primary dbp-review-form__submit"
                            id="dbp-submit-review"
                            data-business-id="<?php echo esc_attr( (string) $business_id ); ?>">
                        <span class="dbp-btn__text"><?php esc_html_e( 'Submit Review', 'directories-builder-pro' ); ?></span>
                        <span class="dbp-spinner" hidden></span>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
