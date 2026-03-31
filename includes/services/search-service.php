<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Services;
use DirectoriesBuilderPro\Repositories\Business_Repository;
use DirectoriesBuilderPro\Core\Helpers\Geo_Helper;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Search_Service class.
 *
 * Handles full-text + geospatial search and autocomplete functionality.
 *
 * @package DirectoriesBuilderPro\Services
 */
class Search_Service {
    /**
     * Business repository.
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
     * Perform a full search.
     *
     * @param array $args Search arguments.
     * @return array{items: array, total: int, pages: int}
     */
    public function search( array $args ): array {
        /**
         * Filter search arguments before execution.
         *
         * @param array $args The search arguments.
         */
        $args = apply_filters( 'dbp/search/args', $args );
        $q         = sanitize_text_field( $args['q'] ?? '' );
        $lat       = isset( $args['lat'] ) ? (float) $args['lat'] : null;
        $lng       = isset( $args['lng'] ) ? (float) $args['lng'] : null;
        $radius_km = (float) ( $args['radius_km'] ?? get_option( 'dbp_default_radius_km', 10 ) );
        $category  = sanitize_text_field( $args['category'] ?? '' );
        $min_rating = isset( $args['min_rating'] ) ? (float) $args['min_rating'] : null;
        $price     = isset( $args['price'] ) ? (int) $args['price'] : null;
        $open_now  = ! empty( $args['open_now'] );
        $orderby   = sanitize_text_field( $args['orderby'] ?? 'relevance' );
        $page      = max( 1, (int) ( $args['page'] ?? 1 ) );
        $per_page  = max( 1, min( 50, (int) ( $args['per_page'] ?? get_option( 'dbp_results_per_page', 12 ) ) ) );
        // If location is provided, use geo search.
        if ( $lat !== null && $lng !== null ) {
            $bbox    = Geo_Helper::get_bounding_box( $lat, $lng, $radius_km );
            $results = $this->repository->find_near( $lat, $lng, $bbox, [
                'limit'  => 500, // Get all within bbox, we'll filter and paginate.
                'status' => 'active',
            ] );
            // Filter by exact radius.
            $results = array_filter(
                $results,
                static fn( array $b ): bool => ( $b['distance'] ?? PHP_FLOAT_MAX ) <= $radius_km
            );
            // Apply text search filter.
            if ( ! empty( $q ) ) {
                $q_lower = mb_strtolower( $q );
                $results = array_filter(
                    $results,
                    static fn( array $b ): bool =>
                        str_contains( mb_strtolower( $b['name'] ), $q_lower ) ||
                        str_contains( mb_strtolower( $b['description'] ?? '' ), $q_lower )
                );
            }
        } elseif ( ! empty( $q ) ) {
            // Text-only search.
            $filters = [ 'status' => 'active', 'limit' => 500 ];
            if ( $category ) {
                $filters['category'] = $category;
            }
            if ( $min_rating ) {
                $filters['min_rating'] = $min_rating;
            }
            if ( $price ) {
                $filters['price_level'] = $price;
            }
            $results = $this->repository->search_fulltext( $q, $filters );
        } else {
            // No search query — return all active businesses.
            $results = $this->repository->find( [ 'status' => 'active' ], 500, 0, 'featured', 'DESC' );
        }
        // Apply additional filters.
        if ( $min_rating ) {
            $results = array_filter(
                $results,
                static fn( array $b ): bool => (float) $b['avg_rating'] >= $min_rating
            );
        }
        if ( $price ) {
            $results = array_filter(
                $results,
                static fn( array $b ): bool => (int) $b['price_level'] === $price
            );
        }
        if ( $category && $lat !== null ) {
            // For geo search, filter by category via taxonomy.
            $results = array_filter( $results, static function ( array $b ) use ( $category ): bool {
                $terms = wp_get_post_terms( (int) $b['wp_post_id'], 'dbp_category', [ 'fields' => 'slugs' ] );
                return is_array( $terms ) && in_array( $category, $terms, true );
            } );
        }
        // Open now filter.
        if ( $open_now ) {
            $results = array_filter(
                $results,
                static fn( array $b ): bool => dbp_is_business_open( $b['hours'] ?? '[]' )
            );
        }
        // Re-index.
        $results = array_values( $results );
        // Sort.
        match ( $orderby ) {
            'distance' => usort( $results, static fn( $a, $b ) => ( $a['distance'] ?? PHP_FLOAT_MAX ) <=> ( $b['distance'] ?? PHP_FLOAT_MAX ) ),
            'highest_rated' => usort( $results, static fn( $a, $b ) => (float) $b['avg_rating'] <=> (float) $a['avg_rating'] ),
            'most_reviewed' => usort( $results, static fn( $a, $b ) => (int) $b['review_count'] <=> (int) $a['review_count'] ),
            'newest' => usort( $results, static fn( $a, $b ) => strtotime( $b['created_at'] ) <=> strtotime( $a['created_at'] ) ),
            default => null, // Relevance — keep current order (featured first from query).
        };
        // Pagination.
        $total = count( $results );
        $pages = (int) ceil( $total / $per_page );
        $items = array_slice( $results, ( $page - 1 ) * $per_page, $per_page );
        // Add thumbnail URLs and category names.
        foreach ( $items as &$item ) {
            $item['thumbnail_url'] = get_the_post_thumbnail_url( (int) $item['wp_post_id'], 'medium' )
                ?: dbp_get_placeholder_image_url();
            $terms = wp_get_post_terms( (int) $item['wp_post_id'], 'dbp_category', [ 'fields' => 'names' ] );
            $item['category'] = is_array( $terms ) && ! empty( $terms ) ? $terms[0] : '';
            $item['is_claimed']  = ! empty( $item['claimed_by'] );
            $item['is_featured'] = (bool) ( $item['featured'] ?? false );
            $item['is_open']     = dbp_is_business_open( $item['hours'] ?? '[]' );
            $item['permalink']   = dbp_get_business_permalink( (int) $item['wp_post_id'] );
        }
        unset( $item );
        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
        ];
    }
    /**
     * Autocomplete search.
     *
     * @param string $query Search query (min 2 chars).
     * @return array Array of suggestions.
     */
    public function autocomplete( string $query ): array {
        $query = sanitize_text_field( $query );
        if ( mb_strlen( $query ) < 2 ) {
            return [];
        }
        $suggestions = [];
        // Search businesses.
        $businesses = $this->repository->search_fulltext( $query, [
            'status' => 'active',
            'limit'  => 5,
        ] );
        foreach ( $businesses as $business ) {
            $suggestions[] = [
                'type'  => 'business',
                'label' => $business['name'],
                'value' => $business['slug'],
                'id'    => (int) $business['id'],
            ];
        }
        // Search categories.
        $categories = get_terms( [
            'taxonomy'   => 'dbp_category',
            'name__like' => $query,
            'number'     => 3,
            'hide_empty' => true,
        ] );
        if ( ! is_wp_error( $categories ) ) {
            foreach ( $categories as $cat ) {
                $suggestions[] = [
                    'type'  => 'category',
                    'label' => $cat->name,
                    'value' => $cat->slug,
                    'id'    => $cat->term_id,
                ];
            }
        }
        return $suggestions;
    }
}