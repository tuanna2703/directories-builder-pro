<?php
declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Global helper functions for Directories Builder Pro.
 *
 * These functions are available throughout the plugin and in theme templates.
 *
 * @package DirectoriesBuilderPro\Core\Helpers
 */

if ( ! function_exists( 'dbp_get_star_html' ) ) {
    /**
     * Generate SVG star rating HTML.
     *
     * @param float $rating      Rating value (0–5).
     * @param bool  $show_number Whether to display the numeric rating.
     * @return string
     */
    function dbp_get_star_html( float $rating, bool $show_number = true ): string {
        $rating = max( 0.0, min( 5.0, $rating ) );
        $html   = '<span class="dbp-stars" aria-label="' . esc_attr(
            /* translators: %s: numeric rating */
            sprintf( __( '%s out of 5 stars', 'directories-builder-pro' ), number_format( $rating, 1 ) )
        ) . '">';

        for ( $i = 1; $i <= 5; $i++ ) {
            if ( $rating >= $i ) {
                // Full star.
                $html .= '<svg class="dbp-star dbp-star--filled" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="currentColor"/></svg>';
            } elseif ( $rating >= $i - 0.5 ) {
                // Half star.
                $html .= '<svg class="dbp-star dbp-star--half" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">';
                $html .= '<defs><linearGradient id="dbp-half-' . $i . '"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#D1D5DB"/></linearGradient></defs>';
                $html .= '<polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="url(#dbp-half-' . $i . ')"/></svg>';
            } else {
                // Empty star.
                $html .= '<svg class="dbp-star dbp-star--empty" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="#D1D5DB"/></svg>';
            }
        }

        if ( $show_number ) {
            $html .= '<span class="dbp-stars__number">' . esc_html( number_format( $rating, 1 ) ) . '</span>';
        }

        $html .= '</span>';

        return $html;
    }
}

if ( ! function_exists( 'dbp_format_distance' ) ) {
    /**
     * Format distance in meters to a human-readable string.
     *
     * @param float  $meters Distance in meters.
     * @param string $unit   'km' or 'miles'.
     * @return string
     */
    function dbp_format_distance( float $meters, string $unit = 'km' ): string {
        if ( empty( $unit ) ) {
            $unit = get_option( 'dbp_distance_unit', 'km' );
        }

        if ( $unit === 'miles' ) {
            $distance = $meters / 1609.344;
            if ( $distance < 0.1 ) {
                $feet = $meters * 3.28084;
                /* translators: %s: number of feet */
                return sprintf( __( '%s ft', 'directories-builder-pro' ), number_format( $feet, 0 ) );
            }
            /* translators: %s: number of miles */
            return sprintf( __( '%s mi', 'directories-builder-pro' ), number_format( $distance, 1 ) );
        }

        if ( $meters < 1000 ) {
            /* translators: %s: number of meters */
            return sprintf( __( '%s m', 'directories-builder-pro' ), number_format( $meters, 0 ) );
        }

        $km = $meters / 1000;
        /* translators: %s: number of kilometers */
        return sprintf( __( '%s km', 'directories-builder-pro' ), number_format( $km, 1 ) );
    }
}

if ( ! function_exists( 'dbp_get_price_label' ) ) {
    /**
     * Get a price level label.
     *
     * @param int $level Price level (1–4).
     * @return string
     */
    function dbp_get_price_label( int $level ): string {
        return match ( max( 1, min( 4, $level ) ) ) {
            1 => '$',
            2 => '$$',
            3 => '$$$',
            4 => '$$$$',
        };
    }
}

if ( ! function_exists( 'dbp_time_ago' ) ) {
    /**
     * Format a datetime string as a relative time string.
     *
     * @param string $datetime MySQL datetime string.
     * @return string
     */
    function dbp_time_ago( string $datetime ): string {
        $timestamp = strtotime( $datetime );
        if ( $timestamp === false ) {
            return $datetime;
        }

        $diff = time() - $timestamp;

        if ( $diff < 0 ) {
            return __( 'just now', 'directories-builder-pro' );
        }

        $intervals = [
            365 * DAY_IN_SECONDS  => [ __( '%d year ago', 'directories-builder-pro' ), __( '%d years ago', 'directories-builder-pro' ) ],
            30 * DAY_IN_SECONDS   => [ __( '%d month ago', 'directories-builder-pro' ), __( '%d months ago', 'directories-builder-pro' ) ],
            7 * DAY_IN_SECONDS    => [ __( '%d week ago', 'directories-builder-pro' ), __( '%d weeks ago', 'directories-builder-pro' ) ],
            DAY_IN_SECONDS        => [ __( '%d day ago', 'directories-builder-pro' ), __( '%d days ago', 'directories-builder-pro' ) ],
            HOUR_IN_SECONDS       => [ __( '%d hour ago', 'directories-builder-pro' ), __( '%d hours ago', 'directories-builder-pro' ) ],
            MINUTE_IN_SECONDS     => [ __( '%d minute ago', 'directories-builder-pro' ), __( '%d minutes ago', 'directories-builder-pro' ) ],
        ];

        foreach ( $intervals as $secs => $labels ) {
            $count = (int) floor( $diff / $secs );
            if ( $count >= 1 ) {
                $label = ( $count === 1 ) ? $labels[0] : $labels[1];
                return sprintf( $label, $count );
            }
        }

        return __( 'just now', 'directories-builder-pro' );
    }
}

if ( ! function_exists( 'dbp_get_business_permalink' ) ) {
    /**
     * Get the permalink for a business listing.
     *
     * @param int $post_id WordPress post ID for the business.
     * @return string
     */
    function dbp_get_business_permalink( int $post_id ): string {
        return get_permalink( $post_id ) ?: home_url( '/' );
    }
}

if ( ! function_exists( 'dbp_get_placeholder_image_url' ) ) {
    /**
     * Get the placeholder image URL for business listings without photos.
     *
     * @return string
     */
    function dbp_get_placeholder_image_url(): string {
        return DBP_URL . 'assets/images/placeholder.svg';
    }
}

if ( ! function_exists( 'dbp_is_business_open' ) ) {
    /**
     * Check if a business is currently open based on its hours JSON.
     *
     * @param array|string $hours Hours data — either a JSON string or already decoded array.
     * @return bool
     */
    function dbp_is_business_open( array|string $hours ): bool {
        if ( is_string( $hours ) ) {
            $hours = json_decode( $hours, true );
        }

        if ( empty( $hours ) || ! is_array( $hours ) ) {
            return false;
        }

        $current_day  = strtolower( gmdate( 'l' ) );
        $current_time = gmdate( 'H:i' );

        // Try WordPress timezone.
        $wp_timezone = wp_timezone();
        if ( $wp_timezone ) {
            $now          = new \DateTime( 'now', $wp_timezone );
            $current_day  = strtolower( $now->format( 'l' ) );
            $current_time = $now->format( 'H:i' );
        }

        if ( ! isset( $hours[ $current_day ] ) ) {
            return false;
        }

        $day_hours = $hours[ $current_day ];

        // Check if explicitly closed.
        if ( isset( $day_hours['closed'] ) && $day_hours['closed'] ) {
            return false;
        }

        if ( ! isset( $day_hours['open'], $day_hours['close'] ) ) {
            return false;
        }

        return $current_time >= $day_hours['open'] && $current_time <= $day_hours['close'];
    }
}
