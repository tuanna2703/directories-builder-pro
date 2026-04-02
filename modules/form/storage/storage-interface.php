<?php
declare(strict_types=1);
namespace DirectoriesBuilderPro\Modules\Form\Storage;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Storage_Interface — contract for all storage adapters.
 *
 * @package DirectoriesBuilderPro\Modules\Form\Storage
 */
interface Storage_Interface {
    /**
     * Get stored value.
     *
     * @param string   $key       Storage key.
     * @param int|null $object_id Object ID (post ID, user ID) or null for options.
     * @return mixed
     */
    public function get( string $key, ?int $object_id ): mixed;
    /**
     * Set (create or update) a value.
     *
     * @param string   $key       Storage key.
     * @param mixed    $value     Value to store.
     * @param int|null $object_id Object ID or null for options.
     * @return bool
     */
    public function set( string $key, mixed $value, ?int $object_id ): bool;
    /**
     * Delete a stored value.
     *
     * @param string   $key       Storage key.
     * @param int|null $object_id Object ID or null for options.
     * @return bool
     */
    public function delete( string $key, ?int $object_id ): bool;
}
