<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Storage;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Options_Storage — wp_options adapter.
 *
 * Stores form values as a serialized array in a single wp_options row.
 * $object_id is ignored (options are global).
 *
 * @package DirectoriesBuilderPro\Modules\Form\Storage
 */
class Options_Storage implements Storage_Interface {
    public function get( string $key, ?int $object_id ): mixed {
        $value = get_option( $key, null );
        return $value;
    }
    public function set( string $key, mixed $value, ?int $object_id ): bool {
        return update_option( $key, $value );
    }
    public function delete( string $key, ?int $object_id ): bool {
        return delete_option( $key );
    }
}
