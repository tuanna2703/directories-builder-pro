<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Storage;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Post_Meta_Storage — post_meta adapter.
 *
 * Stores form values as a serialized array in post_meta.
 * Requires a valid $object_id (post ID).
 *
 * @package DirectoriesBuilderPro\Modules\Form\Storage
 */
class Post_Meta_Storage implements Storage_Interface {
    public function get( string $key, ?int $object_id ): mixed {
        if ( $object_id === null || $object_id <= 0 ) {
            return null;
        }
        $value = get_post_meta( $object_id, $key, true );
        return $value !== '' ? $value : null;
    }
    public function set( string $key, mixed $value, ?int $object_id ): bool {
        if ( $object_id === null || $object_id <= 0 ) {
            return false;
        }
        return (bool) update_post_meta( $object_id, $key, $value );
    }
    public function delete( string $key, ?int $object_id ): bool {
        if ( $object_id === null || $object_id <= 0 ) {
            return false;
        }
        return delete_post_meta( $object_id, $key );
    }
}
