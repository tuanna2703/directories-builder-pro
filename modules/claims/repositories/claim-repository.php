<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Claims\Repositories;

use DirectoriesBuilderPro\Core\Base\Model_Base;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Claim_Repository class.
 *
 * This repository is located in the claims module because the claims domain
 * fully owns the lifecycle of claim requests.
 *
 * @package DirectoriesBuilderPro\Modules\Claims\Repositories
 */
class Claim_Repository extends Model_Base {

    /**
     * Get the table name.
     *
     * @return string
     */
    public function get_table_name(): string {
        return $this->db()->prefix . 'dbp_claims';
    }

    /**
     * Find a claim by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function find_by_id( int $id ): ?array {
        $table = $this->get_table_name();
        $result = $this->db()->get_row(
            $this->db()->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
            ARRAY_A
        );
        return $result ?: null;
    }

    /**
     * Find claims by business ID.
     *
     * @param int $id
     * @return array
     */
    public function find_by_business( int $id ): array {
        $table = $this->get_table_name();
        $results = $this->db()->get_results(
            $this->db()->prepare( "SELECT * FROM {$table} WHERE business_id = %d", $id ),
            ARRAY_A
        );
        return $results ?: [];
    }

    /**
     * Find claims by user ID.
     *
     * @param int $id
     * @return array
     */
    public function find_by_user( int $id ): array {
        $table = $this->get_table_name();
        $results = $this->db()->get_results(
            $this->db()->prepare( "SELECT * FROM {$table} WHERE user_id = %d", $id ),
            ARRAY_A
        );
        return $results ?: [];
    }

    /**
     * Find all pending claims.
     *
     * @return array
     */
    public function find_pending(): array {
        $table = $this->get_table_name();
        $results = $this->db()->get_results(
            "SELECT * FROM {$table} WHERE status = 'pending' ORDER BY created_at ASC",
            ARRAY_A
        );
        return $results ?: [];
    }

    /**
     * Approve a claim.
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function approve( int $id, int $user_id ): bool {
        $table = $this->get_table_name();
        $result = $this->db()->update(
            $table,
            [ 'status' => 'approved', 'user_id' => $user_id ],
            [ 'id' => $id ],
            [ '%s', '%d' ],
            [ '%d' ]
        );
        return $result !== false;
    }

    /**
     * Reject a claim.
     *
     * @param int $id
     * @param string $reason
     * @return bool
     */
    public function reject( int $id, string $reason ): bool {
        $table = $this->get_table_name();
        $result = $this->db()->update(
            $table,
            [ 'status' => 'rejected', 'rejection_reason' => sanitize_textarea_field( $reason ) ],
            [ 'id' => $id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );
        return $result !== false;
    }

    /**
     * Insert a new claim.
     * 
     * @param array $data
     * @return int
     */
    public function insert( array $data ): int {
        $table = $this->get_table_name();
        $this->db()->insert( $table, $data );
        return (int) $this->db()->insert_id;
    }

    /**
     * Check if user has a pending claim for the business.
     *
     * @param int $business_id
     * @param int $user_id
     * @return bool
     */
    public function has_pending_claim( int $business_id, int $user_id ): bool {
        $table = $this->get_table_name();
        $count = $this->db()->get_var(
            $this->db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE business_id = %d AND user_id = %d AND status = 'pending'",
                $business_id,
                $user_id
            )
        );
        return (int) $count > 0;
    }
}
