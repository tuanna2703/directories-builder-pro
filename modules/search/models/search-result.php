<?php
declare(strict_types=1);

namespace DirectoriesBuilderPro\Modules\Search\Models;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Search_Result Model.
 *
 * Encapsulates the results of a search query.
 *
 * @package DirectoriesBuilderPro\Modules\Search\Models
 */
class Search_Result {

    /**
     * Array of business records.
     *
     * @var array
     */
    public array $businesses;

    /**
     * Total number of results.
     *
     * @var int
     */
    public int $total_count;

    /**
     * Total number of pages.
     *
     * @var int
     */
    public int $total_pages;

    /**
     * Current page number.
     *
     * @var int
     */
    public int $current_page;

    /**
     * Constructor.
     *
     * @param array $businesses Array of matched businesses.
     * @param int   $total_count Total available results.
     * @param int   $current_page Current requested page.
     * @param int   $per_page Items per page.
     */
    public function __construct( array $businesses, int $total_count, int $current_page, int $per_page ) {
        $this->businesses   = $businesses;
        $this->total_count  = $total_count;
        $this->current_page = $current_page;
        $this->total_pages  = max( 1, (int) ceil( $total_count / max( 1, $per_page ) ) );
    }

    /**
     * Get the next page number if it exists.
     *
     * @return int|null
     */
    public function get_next_page(): ?int {
        if ( $this->current_page < $this->total_pages ) {
            return $this->current_page + 1;
        }
        return null;
    }
}
