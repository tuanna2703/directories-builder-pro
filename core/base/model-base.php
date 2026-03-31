<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Core\Base;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Abstract Model_Base class.
 *
 * Provides CRUD helpers using $wpdb->prepare() for all database interactions.
 *
 * @package DirectoriesBuilderPro\Core\Base
 */
abstract class Model_Base {
    /**
     * Get the full table name including WordPress prefix.
     *
     * @return string
     */
    abstract public function get_table_name(): string;
    /**
     * Get the global $wpdb instance.
     *
     * @return \wpdb
     */
    protected function db(): \wpdb {
        global $wpdb;
        return $wpdb;
    }
    /**
     * Find records matching optional WHERE conditions.
     *
     * @param array  $where   Associative array of column => value conditions.
     * @param int    $limit   Maximum number of records to return.
     * @param int    $offset  Number of records to skip.
     * @param string $orderby Column to order by.
     * @param string $order   ASC or DESC.
     * @return array
     */
    public function find( array $where = [], int $limit = 100, int $offset = 0, string $orderby = 'id', string $order = 'DESC' ): array {
        $table = $this->get_table_name();
        $order = strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC';
        $allowed_orderby = $this->get_allowed_orderby_columns();
        if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
            $orderby = 'id';
        }
        if ( empty( $where ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $sql = $this->db()->prepare(
                "SELECT * FROM {$table} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        } else {
            $conditions = [];
            $values     = [];
            foreach ( $where as $column => $value ) {
                $column = sanitize_key( $column );
                if ( is_int( $value ) ) {
                    $conditions[] = "`{$column}` = %d";
                } elseif ( is_float( $value ) ) {
                    $conditions[] = "`{$column}` = %f";
                } else {
                    $conditions[] = "`{$column}` = %s";
                }
                $values[] = $value;
            }
            $where_clause = implode( ' AND ', $conditions );
            $values[]     = $limit;
            $values[]     = $offset;
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $sql = $this->db()->prepare(
                "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                ...$values
            );
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->db()->get_results( $sql, ARRAY_A ) ?: [];
    }
    /**
     * Find a single record by ID.
     *
     * @param int $id Record ID.
     * @return array|null
     */
    public function find_by_id( int $id ): ?array {
        $table = $this->get_table_name();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $result = $this->db()->get_row(
            $this->db()->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
            ARRAY_A
        );
        return $result ?: null;
    }
    /**
     * Insert a new record.
     *
     * @param array $data Associative array of column => value.
     * @return int|false The insert ID on success, false on failure.
     */
    public function insert( array $data ): int|false {
        $table   = $this->get_table_name();
        $formats = [];
        foreach ( $data as $value ) {
            if ( is_int( $value ) ) {
                $formats[] = '%d';
            } elseif ( is_float( $value ) ) {
                $formats[] = '%f';
            } else {
                $formats[] = '%s';
            }
        }
        $result = $this->db()->insert( $table, $data, $formats );
        return $result ? (int) $this->db()->insert_id : false;
    }
    /**
     * Update a record by ID.
     *
     * @param int   $id   Record ID.
     * @param array $data Associative array of column => value to update.
     * @return bool
     */
    public function update( int $id, array $data ): bool {
        $table   = $this->get_table_name();
        $formats = [];
        foreach ( $data as $value ) {
            if ( is_int( $value ) ) {
                $formats[] = '%d';
            } elseif ( is_float( $value ) ) {
                $formats[] = '%f';
            } else {
                $formats[] = '%s';
            }
        }
        $result = $this->db()->update( $table, $data, [ 'id' => $id ], $formats, [ '%d' ] );
        return $result !== false;
    }
    /**
     * Delete a record by ID.
     *
     * @param int $id Record ID.
     * @return bool
     */
    public function delete( int $id ): bool {
        $table  = $this->get_table_name();
        $result = $this->db()->delete( $table, [ 'id' => $id ], [ '%d' ] );
        return $result !== false;
    }
    /**
     * Count records matching optional WHERE conditions.
     *
     * @param array $where Associative array of column => value conditions.
     * @return int
     */
    public function count( array $where = [] ): int {
        $table = $this->get_table_name();
        if ( empty( $where ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $count = $this->db()->get_var( "SELECT COUNT(*) FROM {$table}" );
        } else {
            $conditions = [];
            $values     = [];
            foreach ( $where as $column => $value ) {
                $column = sanitize_key( $column );
                if ( is_int( $value ) ) {
                    $conditions[] = "`{$column}` = %d";
                } elseif ( is_float( $value ) ) {
                    $conditions[] = "`{$column}` = %f";
                } else {
                    $conditions[] = "`{$column}` = %s";
                }
                $values[] = $value;
            }
            $where_clause = implode( ' AND ', $conditions );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $count = $this->db()->get_var(
                $this->db()->prepare(
                    "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}",
                    ...$values
                )
            );
        }
        return (int) ( $count ?? 0 );
    }
    /**
     * Get list of allowed orderby columns.
     *
     * @return array
     */
    protected function get_allowed_orderby_columns(): array {
        return [ 'id', 'created_at', 'updated_at' ];
    }
}
