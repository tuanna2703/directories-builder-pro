<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Services;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * User_Service class.
 *
 * Provides user profile, points, badges, and review history functionality.
 *
 * @package DirectoriesBuilderPro\Services
 */
class User_Service {
    /**
     * Get a user's public profile data.
     *
     * @param int $user_id WordPress user ID.
     * @return array
     */
    public function get_user_profile( int $user_id ): array {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return [];
        }
        $review_repo = new \DirectoriesBuilderPro\Repositories\Review_Repository();
        $review_count = $review_repo->count( [
            'user_id' => $user_id,
            'status'  => 'approved',
        ] );
        return [
            'id'            => $user_id,
            'display_name'  => $user->display_name,
            'avatar_url'    => get_avatar_url( $user_id, [ 'size' => 150 ] ),
            'bio'           => get_user_meta( $user_id, 'description', true ) ?: '',
            'review_count'  => $review_count,
            'photo_count'   => (int) get_user_meta( $user_id, 'dbp_photo_count', true ),
            'points'        => $this->get_user_points( $user_id ),
            'badges'        => $this->get_user_badges( $user_id ),
            'is_elite'      => (bool) get_user_meta( $user_id, 'dbp_elite', true ),
            'member_since'  => $user->user_registered,
        ];
    }
    /**
     * Get reviews by a user.
     *
     * @param int $user_id User ID.
     * @param int $page    Page number.
     * @return array
     */
    public function get_user_reviews( int $user_id, int $page = 1 ): array {
        $review_repo = new \DirectoriesBuilderPro\Repositories\Review_Repository();
        return $review_repo->find_by_user( $user_id, $page );
    }
    /**
     * Award points to a user.
     *
     * @param int    $user_id User ID.
     * @param int    $points  Points to award (can be negative).
     * @param string $reason  Reason for the points.
     * @return void
     */
    public function award_points( int $user_id, int $points, string $reason ): void {
        $current = $this->get_user_points( $user_id );
        $new_total = max( 0, $current + $points );
        update_user_meta( $user_id, 'dbp_points', $new_total );
        // Log the points transaction.
        $log = get_user_meta( $user_id, 'dbp_points_log', true );
        if ( ! is_array( $log ) ) {
            $log = [];
        }
        $log[] = [
            'points'    => $points,
            'reason'    => sanitize_text_field( $reason ),
            'total'     => $new_total,
            'timestamp' => current_time( 'mysql' ),
        ];
        // Keep only last 100 entries.
        if ( count( $log ) > 100 ) {
            $log = array_slice( $log, -100 );
        }
        update_user_meta( $user_id, 'dbp_points_log', $log );
    }
    /**
     * Get a user's badges.
     *
     * @param int $user_id User ID.
     * @return array Array of badge names.
     */
    public function get_user_badges( int $user_id ): array {
        $badges = [];
        $points       = $this->get_user_points( $user_id );
        $review_repo  = new \DirectoriesBuilderPro\Repositories\Review_Repository();
        $review_count = $review_repo->count( [
            'user_id' => $user_id,
            'status'  => 'approved',
        ] );
        // Review milestones.
        if ( $review_count >= 1 ) {
            $badges[] = __( 'First Review', 'directories-builder-pro' );
        }
        if ( $review_count >= 10 ) {
            $badges[] = __( 'Regular Reviewer', 'directories-builder-pro' );
        }
        if ( $review_count >= 50 ) {
            $badges[] = __( 'Super Reviewer', 'directories-builder-pro' );
        }
        if ( $review_count >= 100 ) {
            $badges[] = __( 'Review Master', 'directories-builder-pro' );
        }
        // Points milestones.
        if ( $points >= 100 ) {
            $badges[] = __( 'Rising Star', 'directories-builder-pro' );
        }
        if ( $points >= 500 ) {
            $badges[] = __( 'Community Leader', 'directories-builder-pro' );
        }
        // Elite badge.
        if ( (bool) get_user_meta( $user_id, 'dbp_elite', true ) ) {
            $badges[] = __( 'Elite', 'directories-builder-pro' );
        }
        return $badges;
    }
    /**
     * Get a user's total points.
     *
     * @param int $user_id User ID.
     * @return int
     */
    public function get_user_points( int $user_id ): int {
        return (int) get_user_meta( $user_id, 'dbp_points', true );
    }
}