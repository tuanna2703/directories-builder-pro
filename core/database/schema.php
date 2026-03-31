<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Core\Database;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Schema class.
 *
 * Defines CREATE TABLE SQL for all 6 custom tables.
 * SQL is compatible with WordPress dbDelta().
 *
 * @package DirectoriesBuilderPro\Core\Database
 */
class Schema {

    /**
     * Get all table definitions.
     *
     * @return array<string, string> Associative array of table_name => CREATE TABLE SQL.
     */
    public static function get_tables(): array {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $prefix          = $wpdb->prefix;

        return [
            'dbp_businesses' => "CREATE TABLE {$prefix}dbp_businesses (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                wp_post_id    BIGINT UNSIGNED NOT NULL,
                name          VARCHAR(255)    NOT NULL,
                slug          VARCHAR(255)    NOT NULL,
                description   LONGTEXT,
                address       VARCHAR(255),
                city          VARCHAR(100),
                state         VARCHAR(100),
                zip           VARCHAR(20),
                country       VARCHAR(100)    DEFAULT 'US',
                lat           DECIMAL(10,7),
                lng           DECIMAL(10,7),
                phone         VARCHAR(30),
                website       VARCHAR(255),
                email         VARCHAR(100),
                price_level   TINYINT         DEFAULT 1,
                hours         JSON,
                status        VARCHAR(20)     DEFAULT 'active',
                claimed_by    BIGINT UNSIGNED DEFAULT NULL,
                featured      TINYINT(1)      DEFAULT 0,
                avg_rating    DECIMAL(3,2)    DEFAULT 0.00,
                review_count  INT UNSIGNED    DEFAULT 0,
                created_at    DATETIME        DEFAULT CURRENT_TIMESTAMP,
                updated_at    DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                UNIQUE KEY slug (slug),
                KEY wp_post_id (wp_post_id),
                KEY lat_lng (lat, lng),
                KEY status (status)
            ) {$charset_collate};",

            'dbp_business_meta' => "CREATE TABLE {$prefix}dbp_business_meta (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                business_id BIGINT UNSIGNED NOT NULL,
                meta_key    VARCHAR(100)    NOT NULL,
                meta_value  LONGTEXT,
                PRIMARY KEY  (id),
                KEY business_id (business_id),
                KEY meta_key (meta_key)
            ) {$charset_collate};",

            'dbp_reviews' => "CREATE TABLE {$prefix}dbp_reviews (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                business_id  BIGINT UNSIGNED NOT NULL,
                user_id      BIGINT UNSIGNED NOT NULL,
                rating       TINYINT UNSIGNED NOT NULL,
                content      LONGTEXT        NOT NULL,
                status       VARCHAR(20)     DEFAULT 'pending',
                trust_score  SMALLINT        DEFAULT 0,
                helpful      INT UNSIGNED    DEFAULT 0,
                not_helpful  INT UNSIGNED    DEFAULT 0,
                photos       TEXT,
                response     TEXT,
                response_date DATETIME       DEFAULT NULL,
                created_at   DATETIME        DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY business_id (business_id),
                KEY user_id (user_id),
                KEY status (status),
                UNIQUE KEY one_per_user (business_id, user_id)
            ) {$charset_collate};",

            'dbp_review_votes' => "CREATE TABLE {$prefix}dbp_review_votes (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                review_id  BIGINT UNSIGNED NOT NULL,
                user_id    BIGINT UNSIGNED NOT NULL,
                vote_type  VARCHAR(20)     NOT NULL,
                created_at DATETIME        DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                UNIQUE KEY one_vote (review_id, user_id, vote_type)
            ) {$charset_collate};",

            'dbp_claims' => "CREATE TABLE {$prefix}dbp_claims (
                id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                business_id         BIGINT UNSIGNED NOT NULL,
                user_id             BIGINT UNSIGNED NOT NULL,
                owner_name          VARCHAR(255)    NOT NULL,
                email               VARCHAR(100)    NOT NULL,
                phone               VARCHAR(30),
                verification_method VARCHAR(20)     DEFAULT 'email',
                status              VARCHAR(20)     DEFAULT 'pending',
                rejection_reason    TEXT,
                created_at          DATETIME        DEFAULT CURRENT_TIMESTAMP,
                updated_at          DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY business_id (business_id),
                KEY user_id (user_id),
                KEY status (status)
            ) {$charset_collate};",

            'dbp_checkins' => "CREATE TABLE {$prefix}dbp_checkins (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                business_id BIGINT UNSIGNED NOT NULL,
                user_id     BIGINT UNSIGNED NOT NULL,
                created_at  DATETIME        DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY business_id (business_id),
                KEY user_id (user_id)
            ) {$charset_collate};",
        ];
    }
}
