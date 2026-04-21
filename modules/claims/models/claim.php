<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Claims\Models;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Claim Model.
 *
 * Represents a single row in the dbp_claims table.
 *
 * @package DirectoriesBuilderPro\Modules\Claims\Models
 */
class Claim {

    public int $id;
    public int $business_id;
    public int $user_id;
    public string $owner_name;
    public string $email;
    public ?string $phone = null;
    public string $verification_method = 'email';
    public string $status = 'pending';
    public ?string $rejection_reason = null;
    public string $created_at;
    public string $updated_at;

    /**
     * Set properties from an associative array.
     *
     * @param array $data Data from the database.
     */
    public function __construct( array $data = [] ) {
        if ( ! empty( $data ) ) {
            $this->id                  = (int) ( $data['id'] ?? 0 );
            $this->business_id         = (int) ( $data['business_id'] ?? 0 );
            $this->user_id             = (int) ( $data['user_id'] ?? 0 );
            $this->owner_name          = $data['owner_name'] ?? '';
            $this->email               = $data['email'] ?? '';
            $this->phone               = $data['phone'] ?? null;
            $this->verification_method = $data['verification_method'] ?? 'email';
            $this->status              = $data['status'] ?? 'pending';
            $this->rejection_reason    = $data['rejection_reason'] ?? null;
            $this->created_at          = $data['created_at'] ?? '';
            $this->updated_at          = $data['updated_at'] ?? '';
        }
    }
}
