<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\PostTypes;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Business_Post_Type class.
 *
 * Registers the dbp_business custom post type and its associated taxonomies.
 *
 * @package DirectoriesBuilderPro\PostTypes
 */
class Business_Post_Type {

    /**
     * Post type slug.
     */
    public const POST_TYPE = 'dbp_business';

    /**
     * Category taxonomy slug.
     */
    public const TAXONOMY_CATEGORY = 'dbp_category';

    /**
     * Neighborhood taxonomy slug.
     */
    public const TAXONOMY_NEIGHBORHOOD = 'dbp_neighborhood';

    /**
     * Register the custom post type and taxonomies.
     *
     * @return void
     */
    public function register(): void {
        $this->register_post_type();
        $this->register_category_taxonomy();
        $this->register_neighborhood_taxonomy();
    }

    /**
     * Register the dbp_business custom post type.
     *
     * @return void
     */
    private function register_post_type(): void {
        $labels = [
            'name'                  => _x( 'Businesses', 'Post Type General Name', 'directories-builder-pro' ),
            'singular_name'         => _x( 'Business', 'Post Type Singular Name', 'directories-builder-pro' ),
            'menu_name'             => __( 'Businesses', 'directories-builder-pro' ),
            'name_admin_bar'        => __( 'Business', 'directories-builder-pro' ),
            'archives'              => __( 'Business Archives', 'directories-builder-pro' ),
            'attributes'            => __( 'Business Attributes', 'directories-builder-pro' ),
            'parent_item_colon'     => __( 'Parent Business:', 'directories-builder-pro' ),
            'all_items'             => __( 'All Businesses', 'directories-builder-pro' ),
            'add_new_item'          => __( 'Add New Business', 'directories-builder-pro' ),
            'add_new'               => __( 'Add New', 'directories-builder-pro' ),
            'new_item'              => __( 'New Business', 'directories-builder-pro' ),
            'edit_item'             => __( 'Edit Business', 'directories-builder-pro' ),
            'update_item'           => __( 'Update Business', 'directories-builder-pro' ),
            'view_item'             => __( 'View Business', 'directories-builder-pro' ),
            'view_items'            => __( 'View Businesses', 'directories-builder-pro' ),
            'search_items'          => __( 'Search Business', 'directories-builder-pro' ),
            'not_found'             => __( 'No businesses found', 'directories-builder-pro' ),
            'not_found_in_trash'    => __( 'No businesses found in Trash', 'directories-builder-pro' ),
            'featured_image'        => __( 'Business Photo', 'directories-builder-pro' ),
            'set_featured_image'    => __( 'Set business photo', 'directories-builder-pro' ),
            'remove_featured_image' => __( 'Remove business photo', 'directories-builder-pro' ),
            'use_featured_image'    => __( 'Use as business photo', 'directories-builder-pro' ),
            'insert_into_item'      => __( 'Insert into business', 'directories-builder-pro' ),
            'uploaded_to_this_item' => __( 'Uploaded to this business', 'directories-builder-pro' ),
            'items_list'            => __( 'Businesses list', 'directories-builder-pro' ),
            'items_list_navigation' => __( 'Businesses list navigation', 'directories-builder-pro' ),
            'filter_items_list'     => __( 'Filter businesses list', 'directories-builder-pro' ),
        ];

        $args = [
            'label'               => __( 'Business', 'directories-builder-pro' ),
            'description'         => __( 'Business listings for the directory', 'directories-builder-pro' ),
            'labels'              => $labels,
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
            'taxonomies'          => [ self::TAXONOMY_CATEGORY, self::TAXONOMY_NEIGHBORHOOD ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 27,
            'menu_icon'           => 'dashicons-store',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest'        => true,
            'rewrite'             => [
                'slug'       => 'business',
                'with_front' => false,
            ],
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Register the dbp_category taxonomy (hierarchical, like WordPress categories).
     *
     * @return void
     */
    private function register_category_taxonomy(): void {
        $labels = [
            'name'              => _x( 'Business Categories', 'taxonomy general name', 'directories-builder-pro' ),
            'singular_name'     => _x( 'Business Category', 'taxonomy singular name', 'directories-builder-pro' ),
            'search_items'      => __( 'Search Business Categories', 'directories-builder-pro' ),
            'all_items'         => __( 'All Business Categories', 'directories-builder-pro' ),
            'parent_item'       => __( 'Parent Business Category', 'directories-builder-pro' ),
            'parent_item_colon' => __( 'Parent Business Category:', 'directories-builder-pro' ),
            'edit_item'         => __( 'Edit Business Category', 'directories-builder-pro' ),
            'update_item'       => __( 'Update Business Category', 'directories-builder-pro' ),
            'add_new_item'      => __( 'Add New Business Category', 'directories-builder-pro' ),
            'new_item_name'     => __( 'New Business Category Name', 'directories-builder-pro' ),
            'menu_name'         => __( 'Categories', 'directories-builder-pro' ),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => 'business-category' ],
        ];

        register_taxonomy( self::TAXONOMY_CATEGORY, [ self::POST_TYPE ], $args );
    }

    /**
     * Register the dbp_neighborhood taxonomy (flat, like WordPress tags).
     *
     * @return void
     */
    private function register_neighborhood_taxonomy(): void {
        $labels = [
            'name'                       => _x( 'Neighborhoods', 'taxonomy general name', 'directories-builder-pro' ),
            'singular_name'              => _x( 'Neighborhood', 'taxonomy singular name', 'directories-builder-pro' ),
            'search_items'               => __( 'Search Neighborhoods', 'directories-builder-pro' ),
            'popular_items'              => __( 'Popular Neighborhoods', 'directories-builder-pro' ),
            'all_items'                  => __( 'All Neighborhoods', 'directories-builder-pro' ),
            'edit_item'                  => __( 'Edit Neighborhood', 'directories-builder-pro' ),
            'update_item'                => __( 'Update Neighborhood', 'directories-builder-pro' ),
            'add_new_item'               => __( 'Add New Neighborhood', 'directories-builder-pro' ),
            'new_item_name'              => __( 'New Neighborhood Name', 'directories-builder-pro' ),
            'separate_items_with_commas' => __( 'Separate neighborhoods with commas', 'directories-builder-pro' ),
            'add_or_remove_items'        => __( 'Add or remove neighborhoods', 'directories-builder-pro' ),
            'choose_from_most_used'      => __( 'Choose from the most used neighborhoods', 'directories-builder-pro' ),
            'not_found'                  => __( 'No neighborhoods found', 'directories-builder-pro' ),
            'menu_name'                  => __( 'Neighborhoods', 'directories-builder-pro' ),
        ];

        $args = [
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'show_in_rest'          => true,
            'rewrite'               => [ 'slug' => 'neighborhood' ],
        ];

        register_taxonomy( self::TAXONOMY_NEIGHBORHOOD, [ self::POST_TYPE ], $args );
    }
}
