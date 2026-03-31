<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Repositories;

use DirectoriesBuilderPro\Core\Base\Model_Base;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Review_Repository class.
 *
 * Handles all database queries for dbp_reviews and dbp_review_votes tables.
 *
 * @package DirectoriesBuilderPro\Repositories
 */
class Review_Repository extends Model_Base {

    /**
     * Get the reviews table name.
     *
     * @return string
     */
    public function get_table_name(): string {
        return $this->db()->prefix . 'dbp_reviews';
    }

    /**
     * Get the review votes table name.
     *
     * @return string
     */
    public function get_votes_table_name(): string {
        return $this->db()->prefix . 'dbp_review_votes';
    }

    /**
     * Allowed orderby columns.
     *
     * @return array
     */
    protected function get_allowed_orderby_columns(): array {
        return [ 'id', 'rating', 'helpful', 'trust_score', 'created_at', 'updated_at' ];
    }

    /**
     * Find reviews for a specific business.
     *
     * @param int   $business_id Business ID.
     * @param array $args        Optional: page, per_page, orderby, status.
     * @return array
     */
    public function find_by_business( int $business_id, array $args = [] ): array {
        $table    = $this->get_table_name();
        $page     = max( 1, (int) ( $args['page'] ?? 1 ) );
        $per_page = max( 1, min( 50, (int) ( $args['per_page'] ?? 10 ) ) );
        $offset   = ( $page - 1 ) * $per_page;
        $status   = $args['status'] ?? 'approved';
        $orderby  = $args['orderby'] ?? 'relevance';

        $order_clause = match ( $orderby ) {
            'newest'  => 'ORDER BY created_at DESC',
            'highest' => 'ORDER BY rating DESC, created_at DESC',
            'lowest'  => 'ORDER BY rating ASC, created_at DESC',
            default   => 'ORDER BY trust_score DESC, helpful DESC, created_at DESC', // relevance
        };

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $this->db()->get_results(
            $this->db()->prepare(
                "SELECT * FROM {$table} WHERE business_id = %d AND status = %s {$order_clause} LIMIT %d OFFSET %d",
                $business_id,
                $status,
                $per_page,
                $offset
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Find reviews by a specific user.
     *
     * @param int $user_id User ID.
     * @param int $page    Page number.
     * @param int $per_page Results per page.
     * @return array
     */
    public function find_by_user( int $user_id, int $page = 1, int $per_page = 10 ): array {
        $table  = $this->get_table_name();
        $offset = ( max( 1, $page ) - 1 ) * $per_page;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $this->db()->get_results(
            $this->db()->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $user_id,
                $per_page,
                $offset
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Find all pending reviews.
     *
     * @return array
     */
    public function find_pending(): array {
        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $this->db()->get_results(
            $this->db()->prepare(
                "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at ASC",
                'pending'
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get average rating for a business.
     *
     * @param int $business_id Business ID.
     * @return float
     */
    public function get_average_rating( int $business_id ): float {
        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $avg = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT AVG(rating) FROM {$table} WHERE business_id = %d AND status = 'approved'",
                $business_id
            )
        );

        return round( (float) ( $avg ?? 0 ), 2 );
    }

    /**
     * Get review count for a business.
     *
     * @param int $business_id Business ID.
     * @return int
     */
    public function get_review_count( int $business_id ): int {
        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE business_id = %d AND status = 'approved'",
                $business_id
            )
        );

        return (int) ( $count ?? 0 );
    }

    /**
     * Get vote counts for a review.
     *
     * @param int $review_id Review ID.
     * @return array{helpful: int, not_helpful: int}
     */
    public function get_vote_counts( int $review_id ): array {
        $table = $this->get_votes_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $helpful = (int) $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE review_id = %d AND vote_type = 'helpful'",
                $review_id
            )
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $not_helpful = (int) $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE review_id = %d AND vote_type = 'not_helpful'",
                $review_id
            )
        );

        return [
            'helpful'     => $helpful,
            'not_helpful' => $not_helpful,
        ];
    }

    /**
     * Check if a user has voted on a review.
     *
     * @param int $review_id Review ID.
     * @param int $user_id   User ID.
     * @return bool
     */
    public function has_voted( int $review_id, int $user_id ): bool {
        $table = $this->get_votes_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE review_id = %d AND user_id = %d AND vote_type IN ('helpful', 'not_helpful')",
                $review_id,
                $user_id
            )
        );

        return (int) $count > 0;
    }

    /**
     * Insert a vote for a review.
     *
     * @param int    $review_id Review ID.
     * @param int    $user_id   User ID.
     * @param string $type      Vote type: 'helpful', 'not_helpful', or 'flag'.
     * @return bool
     */
    public function insert_vote( int $review_id, int $user_id, string $type ): bool {
        $table = $this->get_votes_table_name();

        $result = $this->db()->insert(
            $table,
            [
                'review_id' => $review_id,
                'user_id'   => $user_id,
                'vote_type' => $type,
            ],
            [ '%d', '%d', '%s' ]
        );

        if ( $result && in_array( $type, [ 'helpful', 'not_helpful' ], true ) ) {
            // Update cached counts on the review.
            $counts = $this->get_vote_counts( $review_id );
            $this->update( $review_id, [
                'helpful'     => $counts['helpful'],
                'not_helpful' => $counts['not_helpful'],
            ] );
        }

        return $result !== false;
    }

    /**
     * Check if a user has already reviewed a specific business.
     *
     * @param int $business_id Business ID.
     * @param int $user_id     User ID.
     * @return bool
     */
    public function user_has_reviewed( int $business_id, int $user_id ): bool {
        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE business_id = %d AND user_id = %d",
                $business_id,
                $user_id
            )
        );

        return (int) $count > 0;
    }

    /**
     * Count flag votes for a review.
     *
     * @param int $review_id Review ID.
     * @return int
     */
    public function get_flag_count( int $review_id ): int {
        $table = $this->get_votes_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE review_id = %d AND vote_type = 'flag'",
                $review_id
            )
        );

        return (int) ( $count ?? 0 );
    }

    /**
     * Check if a user has flagged a review.
     *
     * @param int $review_id Review ID.
     * @param int $user_id   User ID.
     * @return bool
     */
    public function has_flagged( int $review_id, int $user_id ): bool {
        $table = $this->get_votes_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE review_id = %d AND user_id = %d AND vote_type = 'flag'",
                $review_id,
                $user_id
            )
        );

        return (int) $count > 0;
    }

    /**
     * Get reviews by status with counts.
     *
     * @return array{all: int, pending: int, approved: int, rejected: int, spam: int}
     */
    public function get_status_counts(): array {
        $table = $this->get_table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $this->db()->get_results(
            "SELECT status, COUNT(*) as count FROM {$table} GROUP BY status",
            ARRAY_A
        );

        $counts = [
            'all'      => 0,
            'pending'  => 0,
            'approved' => 0,
            'rejected' => 0,
            'spam'     => 0,
        ];

        foreach ( $results as $row ) {
            $counts[ $row['status'] ] = (int) $row['count'];
            $counts['all']           += (int) $row['count'];
        }

        return $counts;
    }
}
