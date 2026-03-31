<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Business\Models;
use DirectoriesBuilderPro\Core\Base\Model_Base;
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Business model class.
 *
 * Maps to the dbp_businesses table.
 *
 * @package DirectoriesBuilderPro\Modules\Business\Models
 */
class Business extends Model_Base {
    public int $id = 0;
    public int $wp_post_id = 0;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $zip = '';
    public string $country = 'US';
    public ?float $lat = null;
    public ?float $lng = null;
    public string $phone = '';
    public string $website = '';
    public string $email = '';
    public int $price_level = 1;
    public ?string $hours = null;
    public string $status = 'active';
    public ?int $claimed_by = null;
    public int $featured = 0;
    public float $avg_rating = 0.00;
    public int $review_count = 0;
    public string $created_at = '';
    public string $updated_at = '';
    public function get_table_name(): string {
        return $this->db()->prefix . 'dbp_businesses';
    }
    protected function get_allowed_orderby_columns(): array {
        return [ 'id', 'name', 'avg_rating', 'review_count', 'price_level', 'created_at', 'updated_at' ];
    }
    public static function from_array( array $data ): static {
        $business = new static();
        $business->id           = (int) ( $data['id'] ?? 0 );
        $business->wp_post_id   = (int) ( $data['wp_post_id'] ?? 0 );
        $business->name         = (string) ( $data['name'] ?? '' );
        $business->slug         = (string) ( $data['slug'] ?? '' );
        $business->description  = (string) ( $data['description'] ?? '' );
        $business->address      = (string) ( $data['address'] ?? '' );
        $business->city         = (string) ( $data['city'] ?? '' );
        $business->state        = (string) ( $data['state'] ?? '' );
        $business->zip          = (string) ( $data['zip'] ?? '' );
        $business->country      = (string) ( $data['country'] ?? 'US' );
        $business->lat          = isset( $data['lat'] ) ? (float) $data['lat'] : null;
        $business->lng          = isset( $data['lng'] ) ? (float) $data['lng'] : null;
        $business->phone        = (string) ( $data['phone'] ?? '' );
        $business->website      = (string) ( $data['website'] ?? '' );
        $business->email        = (string) ( $data['email'] ?? '' );
        $business->price_level  = (int) ( $data['price_level'] ?? 1 );
        $business->hours        = $data['hours'] ?? null;
        $business->status       = (string) ( $data['status'] ?? 'active' );
        $business->claimed_by   = isset( $data['claimed_by'] ) ? (int) $data['claimed_by'] : null;
        $business->featured     = (int) ( $data['featured'] ?? 0 );
        $business->avg_rating   = (float) ( $data['avg_rating'] ?? 0 );
        $business->review_count = (int) ( $data['review_count'] ?? 0 );
        $business->created_at   = (string) ( $data['created_at'] ?? '' );
        $business->updated_at   = (string) ( $data['updated_at'] ?? '' );
        return $business;
    }
    public function to_array(): array {
        return [
            'id'           => $this->id,
            'wp_post_id'   => $this->wp_post_id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'description'  => $this->description,
            'address'      => $this->address,
            'city'         => $this->city,
            'state'        => $this->state,
            'zip'          => $this->zip,
            'country'      => $this->country,
            'lat'          => $this->lat,
            'lng'          => $this->lng,
            'phone'        => $this->phone,
            'website'      => $this->website,
            'email'        => $this->email,
            'price_level'  => $this->price_level,
            'hours'        => $this->hours,
            'status'       => $this->status,
            'claimed_by'   => $this->claimed_by,
            'featured'     => $this->featured,
            'avg_rating'   => $this->avg_rating,
            'review_count' => $this->review_count,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}