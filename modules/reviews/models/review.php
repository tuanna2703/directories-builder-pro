<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Reviews\Models;
use DirectoriesBuilderPro\Core\Base\Model_Base;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Review model class.
 *
 * Maps to the dbp_reviews table.
 *
 * @package DirectoriesBuilderPro\Modules\Reviews\Models
 */
class Review extends Model_Base {
    /**
     * Review ID.
     */
    public int $id = 0;
    /**
     * Business ID.
     */
    public int $business_id = 0;
    /**
     * Reviewer user ID.
     */
    public int $user_id = 0;
    /**
     * Rating (1-5).
     */
    public int $rating = 0;
    /**
     * Review content.
     */
    public string $content = '';
    /**
     * Review status.
     */
    public string $status = 'pending';
    /**
     * Trust score.
     */
    public int $trust_score = 0;
    /**
     * Helpful vote count.
     */
    public int $helpful = 0;
    /**
     * Not helpful vote count.
     */
    public int $not_helpful = 0;
    /**
     * Photo attachment IDs (comma-separated).
     */
    public string $photos = '';
    /**
     * Owner response text.
     */
    public string $response = '';
    /**
     * Owner response date.
     */
    public ?string $response_date = null;
    /**
     * Creation datetime.
     */
    public string $created_at = '';
    /**
     * Last update datetime.
     */
    public string $updated_at = '';
    /**
     * Get the table name.
     *
     * @return string
     */
    public function get_table_name(): string {
        return $this->db()->prefix . 'dbp_reviews';
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
     * Convert a database row array to a Review instance.
     *
     * @param array $data Database row.
     * @return static
     */
    public static function from_array( array $data ): static {
        $review = new static();
        $review->id            = (int) ( $data['id'] ?? 0 );
        $review->business_id   = (int) ( $data['business_id'] ?? 0 );
        $review->user_id       = (int) ( $data['user_id'] ?? 0 );
        $review->rating        = (int) ( $data['rating'] ?? 0 );
        $review->content       = (string) ( $data['content'] ?? '' );
        $review->status        = (string) ( $data['status'] ?? 'pending' );
        $review->trust_score   = (int) ( $data['trust_score'] ?? 0 );
        $review->helpful       = (int) ( $data['helpful'] ?? 0 );
        $review->not_helpful   = (int) ( $data['not_helpful'] ?? 0 );
        $review->photos        = (string) ( $data['photos'] ?? '' );
        $review->response      = (string) ( $data['response'] ?? '' );
        $review->response_date = $data['response_date'] ?? null;
        $review->created_at    = (string) ( $data['created_at'] ?? '' );
        $review->updated_at    = (string) ( $data['updated_at'] ?? '' );
        return $review;
    }
    /**
     * Convert the review to an array.
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'id'            => $this->id,
            'business_id'   => $this->business_id,
            'user_id'       => $this->user_id,
            'rating'        => $this->rating,
            'content'       => $this->content,
            'status'        => $this->status,
            'trust_score'   => $this->trust_score,
            'helpful'       => $this->helpful,
            'not_helpful'   => $this->not_helpful,
            'photos'        => $this->photos,
            'response'      => $this->response,
            'response_date' => $this->response_date,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}