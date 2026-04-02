<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Storage;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * User_Meta_Storage — user_meta adapter.
 *
 * Stores form values as a serialized array in user_meta.
 * Requires a valid $object_id (user ID).
 *
 * @package DirectoriesBuilderPro\Modules\Form\Storage
 */
class User_Meta_Storage implements Storage_Interface {
    public function get( string $key, ?int $object_id ): mixed {
        if ( $object_id === null || $object_id <= 0 ) {
            return null;
        }
        $value = get_user_meta( $object_id, $key, true );
        return $value !== '' ? $value : null;
    }
    public function set( string $key, mixed $value, ?int $object_id ): bool {
        if ( $object_id === null || $object_id <= 0 ) {
            return false;
        }
        return (bool) update_user_meta( $object_id, $key, $value );
    }
    public function delete( string $key, ?int $object_id ): bool {
        if ( $object_id === null || $object_id <= 0 ) {
            return false;
        }
        return delete_user_meta( $object_id, $key );
    }
}
