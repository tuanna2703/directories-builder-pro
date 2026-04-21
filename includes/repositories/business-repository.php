<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Repositories;

use DirectoriesBuilderPro\Core\Base\Model_Base;
use DirectoriesBuilderPro\Core\Helpers\Geo_Helper;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Business_Repository class.
 *
 * Handles all database queries for dbp_businesses and dbp_business_meta tables.
 *
 * @package DirectoriesBuilderPro\Repositories
 */
class Business_Repository extends Model_Base {

    /**
     * Get the businesses table name.
     *
     * @return string
     */
    public function get_table_name(): string {
        return $this->db()->prefix . 'dbp_businesses';
    }

    /**
     * Get the business meta table name.
     *
     * @return string
     */
    public function get_meta_table_name(): string {
        return $this->db()->prefix . 'dbp_business_meta';
    }

    /**
     * Allowed orderby columns.
     *
     * @return array
     */
    protected function get_allowed_orderby_columns(): array {
        return [ 'id', 'name', 'avg_rating', 'review_count', 'price_level', 'created_at', 'updated_at', 'featured' ];
    }

    /**
     * Find a business by its WordPress post ID.
     *
     * @param int $post_id WordPress post ID.
     * @return array|null
     */
    public function find_by_post_id( int $post_id ): ?array {
        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $result = $this->db()->get_row(
            $this->db()->prepare( "SELECT * FROM {$table} WHERE wp_post_id = %d", $post_id ),
            ARRAY_A
        );

        return $result ?: null;
    }

    /**
     * Find businesses near a geographic location.
     *
     * Uses bounding box pre-filter then haversine post-sort.
     *
     * @param float $lat    Center latitude.
     * @param float $lng    Center longitude.
     * @param array $bbox   Bounding box from Geo_Helper::get_bounding_box().
     * @param array $args   Additional args: limit, offset, status.
     * @return array Array of business records with 'distance' field added.
     */
    public function find_near( float $lat, float $lng, array $bbox, array $args = [] ): array {
        $table  = $this->get_table_name();
        $limit  = (int) ( $args['limit'] ?? 100 );
        $offset = (int) ( $args['offset'] ?? 0 );
        $status = $args['status'] ?? 'active';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $this->db()->get_results(
            $this->db()->prepare(
                "SELECT * FROM {$table}
                WHERE lat BETWEEN %f AND %f
                AND lng BETWEEN %f AND %f
                AND status = %s
                LIMIT %d OFFSET %d",
                $bbox['min_lat'],
                $bbox['max_lat'],
                $bbox['min_lng'],
                $bbox['max_lng'],
                $status,
                $limit,
                $offset
            ),
            ARRAY_A
        );

        if ( empty( $results ) ) {
            return [];
        }

        // Calculate actual distances and sort.
        foreach ( $results as &$business ) {
            $business['distance'] = Geo_Helper::haversine(
                $lat,
                $lng,
                (float) $business['lat'],
                (float) $business['lng']
            );
        }
        unset( $business );

        // Sort by distance ascending.
        usort( $results, static fn( array $a, array $b ): int => $a['distance'] <=> $b['distance'] );

        return $results;
    }

    /**
     * Full-text search on business name and description.
     *
     * @param string $query   Search query string.
     * @param array  $filters Additional filters: category, min_rating, price_level, status.
     * @return array
     */
    public function search_fulltext( string $query, array $filters = [] ): array {
        $table   = $this->get_table_name();
        $like    = '%' . $this->db()->esc_like( $query ) . '%';
        $status  = $filters['status'] ?? 'active';
        $limit   = (int) ( $filters['limit'] ?? 100 );
        $offset  = (int) ( $filters['offset'] ?? 0 );

        $where_clauses = [ 'status = %s' ];
        $values        = [ $status ];

        // Search query.
        $where_clauses[] = '(name LIKE %s OR description LIKE %s)';
        $values[]        = $like;
        $values[]        = $like;

        // Category filter (via WordPress taxonomy on wp_post_id).
        if ( ! empty( $filters['category'] ) ) {
            $category_term = get_term_by( 'slug', sanitize_title( $filters['category'] ), 'dbp_category' );
            if ( $category_term ) {
                $post_ids = get_posts( [
                    'post_type'      => 'dbp_business',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery
                        [
                            'taxonomy' => 'dbp_category',
                            'field'    => 'term_id',
                            'terms'    => $category_term->term_id,
                        ],
                    ],
                ] );

                if ( ! empty( $post_ids ) ) {
                    $placeholders    = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
                    $where_clauses[] = "wp_post_id IN ({$placeholders})";
                    $values          = array_merge( $values, $post_ids );
                } else {
                    return []; // No businesses in this category.
                }
            }
        }

        // Min rating filter.
        if ( ! empty( $filters['min_rating'] ) ) {
            $where_clauses[] = 'avg_rating >= %f';
            $values[]        = (float) $filters['min_rating'];
        }

        // Price level filter.
        if ( ! empty( $filters['price_level'] ) ) {
            $where_clauses[] = 'price_level = %d';
            $values[]        = (int) $filters['price_level'];
        }

        $where = implode( ' AND ', $where_clauses );
        $values[] = $limit;
        $values[] = $offset;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $this->db()->get_results(
            $this->db()->prepare(
                "SELECT * FROM {$table} WHERE {$where} ORDER BY featured DESC, avg_rating DESC LIMIT %d OFFSET %d",
                ...$values
            ),
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Count search results.
     *
     * @param string $query   Search query.
     * @param array  $filters Filters.
     * @return int
     */
    public function count_search_results( string $query, array $filters = [] ): int {
        $table   = $this->get_table_name();
        $like    = '%' . $this->db()->esc_like( $query ) . '%';
        $status  = $filters['status'] ?? 'active';

        $where_clauses = [ 'status = %s' ];
        $values        = [ $status ];

        $where_clauses[] = '(name LIKE %s OR description LIKE %s)';
        $values[]        = $like;
        $values[]        = $like;

        if ( ! empty( $filters['min_rating'] ) ) {
            $where_clauses[] = 'avg_rating >= %f';
            $values[]        = (float) $filters['min_rating'];
        }

        if ( ! empty( $filters['price_level'] ) ) {
            $where_clauses[] = 'price_level = %d';
            $values[]        = (int) $filters['price_level'];
        }

        $where = implode( ' AND ', $where_clauses );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE {$where}",
                ...$values
            )
        );

        return (int) ( $count ?? 0 );
    }

    /**
     * Get a single meta value for a business.
     *
     * @param int    $business_id Business ID.
     * @param string $key         Meta key.
     * @return mixed|null
     */
    public function get_meta( int $business_id, string $key ): mixed {
        $table = $this->get_meta_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $value = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT meta_value FROM {$table} WHERE business_id = %d AND meta_key = %s",
                $business_id,
                $key
            )
        );

        if ( $value === null ) {
            return null;
        }

        $decoded = json_decode( $value, true );
        return ( json_last_error() === JSON_ERROR_NONE ) ? $decoded : $value;
    }

    /**
     * Update or insert a meta value for a business.
     *
     * @param int    $business_id Business ID.
     * @param string $key         Meta key.
     * @param mixed  $value       Meta value (will be JSON-encoded if array/object).
     * @return bool
     */
    public function update_meta( int $business_id, string $key, mixed $value ): bool {
        $table      = $this->get_meta_table_name();
        $meta_value = is_array( $value ) || is_object( $value ) ? wp_json_encode( $value ) : (string) $value;

        // Check if meta exists.
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $existing = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT id FROM {$table} WHERE business_id = %d AND meta_key = %s",
                $business_id,
                $key
            )
        );

        if ( $existing ) {
            $result = $this->db()->update(
                $table,
                [ 'meta_value' => $meta_value ],
                [ 'business_id' => $business_id, 'meta_key' => $key ],
                [ '%s' ],
                [ '%d', '%s' ]
            );
            return $result !== false;
        }

        $result = $this->db()->insert(
            $table,
            [
                'business_id' => $business_id,
                'meta_key'    => $key,
                'meta_value'  => $meta_value,
            ],
            [ '%d', '%s', '%s' ]
        );

        return $result !== false;
    }

    /**
     * Get all meta for a business.
     *
     * @param int $business_id Business ID.
     * @return array Associative array of meta_key => meta_value.
     */
    public function get_all_meta( int $business_id ): array {
        $table = $this->get_meta_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $this->db()->get_results(
            $this->db()->prepare(
                "SELECT meta_key, meta_value FROM {$table} WHERE business_id = %d",
                $business_id
            ),
            ARRAY_A
        );

        $meta = [];
        foreach ( $results as $row ) {
            $decoded = json_decode( $row['meta_value'], true );
            $meta[ $row['meta_key'] ] = ( json_last_error() === JSON_ERROR_NONE ) ? $decoded : $row['meta_value'];
        }

        return $meta;
    }

    /**
     * Get featured businesses.
     *
     * @param int $limit Max results.
     * @return array
     */
    public function get_featured( int $limit = 6 ): array {
        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $this->db()->get_results(
            $this->db()->prepare(
                "SELECT * FROM {$table} WHERE featured = 1 AND status = 'active' ORDER BY avg_rating DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Check if a user has checked in today.
     *
     * @param int $business_id
     * @param int $user_id
     * @return bool
     */
    public function has_checked_in_today( int $business_id, int $user_id ): bool {
        $table = $this->db()->prefix . 'dbp_checkins';
        $today = gmdate( 'Y-m-d' );
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE business_id = %d AND user_id = %d AND DATE(created_at) = %s",
                $business_id,
                $user_id,
                $today
            )
        );
        return (int) $count > 0;
    }

    /**
     * Insert a new check-in.
     *
     * @param int $business_id
     * @param int $user_id
     * @return int
     */
    public function insert_checkin( int $business_id, int $user_id ): int {
        $table = $this->db()->prefix . 'dbp_checkins';
        
        $this->db()->insert( $table, [
            'business_id' => $business_id,
            'user_id'     => $user_id,
        ], [ '%d', '%d' ] );
        
        return (int) $this->db()->insert_id;
    }

    /**
     * Find similar businesses in the same city.
     *
     * @param int $business_id
     * @param int $limit
     * @return array
     */
    public function find_similar( int $business_id, int $limit = 3 ): array {
        $business = $this->find_by_id( $business_id );
        if ( ! $business ) {
            return [];
        }

        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $this->db()->get_results(
            $this->db()->prepare(
                "SELECT * FROM {$table} WHERE city = %s AND id != %d AND status = 'active' ORDER BY avg_rating DESC LIMIT %d",
                $business['city'],
                $business_id,
                $limit
            ),
            ARRAY_A
        );

        return $results ?: [];
    }
}
