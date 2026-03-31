<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Helpers;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Geo_Helper class.
 *
 * Provides geographic calculation utilities:
 * haversine distance formula and bounding box calculator.
 *
 * @package DirectoriesBuilderPro\Core\Helpers
 */
class Geo_Helper {
    /**
     * Earth's radius in kilometers.
     */
    private const EARTH_RADIUS_KM = 6371.0;
    /**
     * Calculate the distance between two coordinates using the Haversine formula.
     *
     * @param float $lat1 Latitude of point 1.
     * @param float $lng1 Longitude of point 1.
     * @param float $lat2 Latitude of point 2.
     * @param float $lng2 Longitude of point 2.
     * @return float Distance in kilometers.
     */
    public static function haversine( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
        $lat1_rad = deg2rad( $lat1 );
        $lat2_rad = deg2rad( $lat2 );
        $dlat     = deg2rad( $lat2 - $lat1 );
        $dlng     = deg2rad( $lng2 - $lng1 );
        $a = sin( $dlat / 2 ) ** 2
            + cos( $lat1_rad ) * cos( $lat2_rad ) * sin( $dlng / 2 ) ** 2;
        $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
        return self::EARTH_RADIUS_KM * $c;
    }
    /**
     * Calculate a bounding box around a center point.
     *
     * Used as a pre-filter before running the more expensive haversine calculation.
     *
     * @param float $lat       Center latitude.
     * @param float $lng       Center longitude.
     * @param float $radius_km Radius in kilometers.
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}
     */
    public static function get_bounding_box( float $lat, float $lng, float $radius_km ): array {
        $lat_delta = $radius_km / self::EARTH_RADIUS_KM;
        $lat_delta = rad2deg( $lat_delta );
        $lng_delta = $radius_km / ( self::EARTH_RADIUS_KM * cos( deg2rad( $lat ) ) );
        $lng_delta = rad2deg( $lng_delta );
        return [
            'min_lat' => $lat - $lat_delta,
            'max_lat' => $lat + $lat_delta,
            'min_lng' => $lng - $lng_delta,
            'max_lng' => $lng + $lng_delta,
        ];
    }
    /**
     * Convert distance from kilometers to miles.
     *
     * @param float $km Distance in kilometers.
     * @return float Distance in miles.
     */
    public static function km_to_miles( float $km ): float {
        return $km * 0.621371;
    }
    /**
     * Convert distance from miles to kilometers.
     *
     * @param float $miles Distance in miles.
     * @return float Distance in kilometers.
     */
    public static function miles_to_km( float $miles ): float {
        return $miles / 0.621371;
    }
}