<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Services;

use DirectoriesBuilderPro\Repositories\Business_Repository;
use DirectoriesBuilderPro\Core\Helpers\Geo_Helper;
use WP_Error;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Business_Service class.
 *
 * Business logic layer for business listings.
 * Handles CRUD, average rating calculation, featured and nearby queries.
 *
 * @package DirectoriesBuilderPro\Services
 */
class Business_Service {

    /**
     * Business repository instance.
     *
     * @var Business_Repository
     */
    private Business_Repository $repository;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->repository = new Business_Repository();
    }

    /**
     * Get a single business by ID.
     *
     * @param int $id Business record ID.
     * @return array|null
     */
    public function get_business( int $id ): ?array {
        $business = $this->repository->find_by_id( $id );

        if ( $business ) {
            $business['meta'] = $this->repository->get_all_meta( $id );
        }

        return $business;
    }

    /**
     * Get a business by its WordPress post ID.
     *
     * @param int $post_id WordPress post ID.
     * @return array|null
     */
    public function get_business_by_post_id( int $post_id ): ?array {
        $business = $this->repository->find_by_post_id( $post_id );

        if ( $business ) {
            $business['meta'] = $this->repository->get_all_meta( (int) $business['id'] );
        }

        return $business;
    }

    /**
     * Create a new business listing.
     *
     * @param array $data Business data.
     * @return int|WP_Error Business ID on success, WP_Error on failure.
     */
    public function create_business( array $data ): int|WP_Error {
        // Validate required fields.
        $required = [ 'name', 'wp_post_id' ];
        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error(
                    'missing_field',
                    /* translators: %s: field name */
                    sprintf( __( 'The %s field is required.', 'directories-builder-pro' ), $field ),
                    [ 'status' => 400 ]
                );
            }
        }

        // Generate slug from name if not provided.
        if ( empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['name'] );
        }

        // Sanitize data.
        $insert_data = [
            'wp_post_id'  => (int) $data['wp_post_id'],
            'name'        => sanitize_text_field( $data['name'] ),
            'slug'        => sanitize_title( $data['slug'] ),
            'description' => wp_kses_post( $data['description'] ?? '' ),
            'address'     => sanitize_text_field( $data['address'] ?? '' ),
            'city'        => sanitize_text_field( $data['city'] ?? '' ),
            'state'       => sanitize_text_field( $data['state'] ?? '' ),
            'zip'         => sanitize_text_field( $data['zip'] ?? '' ),
            'country'     => sanitize_text_field( $data['country'] ?? 'US' ),
            'lat'         => isset( $data['lat'] ) ? (float) $data['lat'] : null,
            'lng'         => isset( $data['lng'] ) ? (float) $data['lng'] : null,
            'phone'       => sanitize_text_field( $data['phone'] ?? '' ),
            'website'     => esc_url_raw( $data['website'] ?? '' ),
            'email'       => sanitize_email( $data['email'] ?? '' ),
            'price_level' => max( 1, min( 4, (int) ( $data['price_level'] ?? 1 ) ) ),
            'hours'       => isset( $data['hours'] ) ? wp_json_encode( $data['hours'] ) : null,
            'status'      => in_array( $data['status'] ?? 'active', [ 'active', 'inactive', 'pending' ], true )
                ? $data['status'] : 'active',
        ];

        // Remove null values.
        $insert_data = array_filter( $insert_data, static fn( $v ) => $v !== null );

        $id = $this->repository->insert( $insert_data );

        if ( $id === false ) {
            return new WP_Error( 'insert_failed', __( 'Failed to create business.', 'directories-builder-pro' ), [ 'status' => 500 ] );
        }

        // Insert meta data.
        if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {
            foreach ( $data['meta'] as $key => $value ) {
                $this->repository->update_meta( $id, sanitize_key( $key ), $value );
            }
        }

        return $id;
    }

    /**
     * Update a business listing.
     *
     * @param int   $id   Business record ID.
     * @param array $data Fields to update.
     * @return bool|WP_Error
     */
    public function update_business( int $id, array $data ): bool|WP_Error {
        $business = $this->repository->find_by_id( $id );
        if ( ! $business ) {
            return new WP_Error( 'not_found', __( 'Business not found.', 'directories-builder-pro' ), [ 'status' => 404 ] );
        }

        $update_data = [];

        $text_fields = [ 'name', 'address', 'city', 'state', 'zip', 'country', 'phone' ];
        foreach ( $text_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $update_data[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }

        if ( isset( $data['description'] ) ) {
            $update_data['description'] = wp_kses_post( $data['description'] );
        }

        if ( isset( $data['slug'] ) ) {
            $update_data['slug'] = sanitize_title( $data['slug'] );
        }

        if ( isset( $data['website'] ) ) {
            $update_data['website'] = esc_url_raw( $data['website'] );
        }

        if ( isset( $data['email'] ) ) {
            $update_data['email'] = sanitize_email( $data['email'] );
        }

        if ( isset( $data['lat'] ) ) {
            $update_data['lat'] = (float) $data['lat'];
        }

        if ( isset( $data['lng'] ) ) {
            $update_data['lng'] = (float) $data['lng'];
        }

        if ( isset( $data['price_level'] ) ) {
            $update_data['price_level'] = max( 1, min( 4, (int) $data['price_level'] ) );
        }

        if ( isset( $data['hours'] ) ) {
            $update_data['hours'] = wp_json_encode( $data['hours'] );
        }

        if ( isset( $data['status'] ) && in_array( $data['status'], [ 'active', 'inactive', 'pending' ], true ) ) {
            $update_data['status'] = $data['status'];
        }

        if ( isset( $data['featured'] ) ) {
            $update_data['featured'] = (int) (bool) $data['featured'];
        }

        if ( isset( $data['claimed_by'] ) ) {
            $update_data['claimed_by'] = (int) $data['claimed_by'];
        }

        if ( ! empty( $update_data ) ) {
            $this->repository->update( $id, $update_data );
        }

        // Update meta data.
        if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {
            foreach ( $data['meta'] as $key => $value ) {
                $this->repository->update_meta( $id, sanitize_key( $key ), $value );
            }
        }

        return true;
    }

    /**
     * Delete a business listing.
     *
     * @param int $id Business record ID.
     * @return bool
     */
    public function delete_business( int $id ): bool {
        return $this->repository->delete( $id );
    }

    /**
     * Calculate and update the average rating for a business.
     *
     * @param int $business_id Business record ID.
     * @return float The new average rating.
     */
    public function calculate_average_rating( int $business_id ): float {
        $review_repo = new \DirectoriesBuilderPro\Repositories\Review_Repository();
        $avg_rating  = $review_repo->get_average_rating( $business_id );
        $count       = $review_repo->get_review_count( $business_id );

        $this->repository->update( $business_id, [
            'avg_rating'   => $avg_rating,
            'review_count' => $count,
        ] );

        return $avg_rating;
    }

    /**
     * Get featured businesses.
     *
     * @param int $limit Max results.
     * @return array
     */
    public function get_featured_businesses( int $limit = 6 ): array {
        return $this->repository->get_featured( $limit );
    }

    /**
     * Get businesses near a location.
     *
     * @param float $lat       Latitude.
     * @param float $lng       Longitude.
     * @param float $radius_km Radius in kilometers.
     * @return array
     */
    public function get_nearby_businesses( float $lat, float $lng, float $radius_km = 10.0 ): array {
        $bbox = Geo_Helper::get_bounding_box( $lat, $lng, $radius_km );
        $businesses = $this->repository->find_near( $lat, $lng, $bbox );

        // Filter to within exact radius.
        return array_filter(
            $businesses,
            static fn( array $b ): bool => $b['distance'] <= $radius_km
        );
    }

    /**
     * Get similar businesses (same category + city).
     *
     * @param int $business_id Business record ID.
     * @param int $limit       Max results.
     * @return array
     */
    public function get_similar_businesses( int $business_id, int $limit = 3 ): array {
        return $this->repository->find_similar( $business_id, $limit );
    }

    /**
     * Get the repository instance.
     *
     * @return Business_Repository
     */
    public function get_repository(): Business_Repository {
        return $this->repository;
    }
}
