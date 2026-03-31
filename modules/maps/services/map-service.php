<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Maps\Services;
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Map_Service class.
 *
 * Google Maps URL builders and GeoJSON conversion.
 *
 * @package DirectoriesBuilderPro\Modules\Maps\Services
 */
class Map_Service {
    /**
     * Get a Google Maps embed URL.
     */
    public static function get_embed_url( float $lat, float $lng, string $address = '' ): string {
        $key = get_option( 'dbp_google_maps_key', '' );
        if ( empty( $key ) ) {
            return '';
        }
        $q = $address ?: "{$lat},{$lng}";
        return sprintf(
            'https://www.google.com/maps/embed/v1/place?key=%s&q=%s&center=%s,%s&zoom=15',
            esc_attr( $key ),
            urlencode( $q ),
            $lat,
            $lng
        );
    }
    /**
     * Build a GeoJSON FeatureCollection from business data.
     */
    public static function build_geojson( array $businesses ): array {
        $features = [];
        foreach ( $businesses as $business ) {
            $lat = (float) ( $business['lat'] ?? 0 );
            $lng = (float) ( $business['lng'] ?? 0 );
            if ( ! $lat || ! $lng ) {
                continue;
            }
            $features[] = [
                'type'       => 'Feature',
                'geometry'   => [
                    'type'        => 'Point',
                    'coordinates' => [ $lng, $lat ], // GeoJSON uses [lng, lat].
                ],
                'properties' => [
                    'id'            => (int) ( $business['id'] ?? 0 ),
                    'name'          => $business['name'] ?? '',
                    'slug'          => $business['slug'] ?? '',
                    'rating'        => (float) ( $business['avg_rating'] ?? 0 ),
                    'review_count'  => (int) ( $business['review_count'] ?? 0 ),
                    'price_level'   => (int) ( $business['price_level'] ?? 1 ),
                    'thumbnail_url' => $business['thumbnail_url'] ?? dbp_get_placeholder_image_url(),
                    'permalink'     => $business['permalink'] ?? dbp_get_business_permalink( (int) ( $business['wp_post_id'] ?? 0 ) ),
                ],
            ];
        }
        return [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];
    }
    /**
     * Get a static Google Maps image URL.
     */
    public static function get_static_map_url( float $lat, float $lng, int $zoom = 15, string $size = '600x300' ): string {
        $key = get_option( 'dbp_google_maps_key', '' );
        if ( empty( $key ) ) {
            return '';
        }
        return sprintf(
            'https://maps.googleapis.com/maps/api/staticmap?center=%s,%s&zoom=%d&size=%s&markers=%s,%s&key=%s',
            $lat, $lng, $zoom, $size, $lat, $lng, esc_attr( $key )
        );
    }
    /**
     * Get a Google Maps directions URL.
     */
    public static function get_directions_url( string $address ): string {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode( $address );
    }
}