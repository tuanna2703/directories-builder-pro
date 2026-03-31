# Agent Prompt
## Directories Builder Pro — Full Build Instructions

> Paste this prompt into Claude Code, Cursor, Aider, or any agentic coding tool.
> The agent will research, plan, build, and self-audit the complete plugin.

---

```
You are a senior WordPress plugin architect and full-stack developer. Your task
is to research, plan, build, and execute a production-quality WordPress plugin
called `directories-builder-pro` — a Yelp-like local business review platform
built with an Elementor-inspired modular architecture.

Read the prd.md and structure.md files in this project before writing any code.
They are the source of truth for features, database schema, API endpoints, and
folder layout. Do not deviate from them without flagging the discrepancy first.


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 1 — RESEARCH & AUDIT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Before writing any code:

1. Read prd.md and structure.md in full.
2. Identify any files or systems implied by the PRD but missing from the
   structure (e.g., shortcode registration, JSON-LD injection, email
   notification helpers) and add them to your build plan.
3. Confirm that all Elementor-inspired patterns are present:
   singleton Plugin, Module Manager, abstract base classes, hook-based
   registration inside init() methods.
4. Flag any architectural risks — missing migrations runner, no nonce layer,
   absent capability checks — and resolve them before writing a single line.


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 2 — PLANNING
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Produce and output a detailed build plan containing:

- File-by-file implementation order
  (bootstrap → core → includes → modules → admin → public → assets)
- Confirmation of database schema from prd.md (list all 6 tables)
- WordPress hooks map: which action/filter each class registers, and when
- REST API route table: method, path, permission_callback, handler method
- JS module plan: what each file in /assets/js/modules/ does, which
  endpoints or AJAX actions it calls
- Security checklist per module: nonce, capability check, sanitization,
  escaping


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 3 — BUILD
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Implement every file listed in structure.md. No stubs, no placeholders —
write complete, working, production-ready code for each file.
Follow this exact order:


──────────────────────────────────────
BOOTSTRAP
──────────────────────────────────────

directories-builder-pro.php
  - declare(strict_types=1)
  - Define: DBP_VERSION, DBP_PATH, DBP_URL, DBP_BASENAME
  - Load includes/autoloader.php
  - Hook Plugin::instance() on plugins_loaded
  - register_activation_hook   → Migrations::run()
  - register_deactivation_hook → flush_rewrite_rules()
  - register_uninstall_hook    → uninstall.php


──────────────────────────────────────
CORE LAYER
──────────────────────────────────────

core/base/module-base.php
  - Abstract class Module_Base
  - abstract public function get_name(): string
  - abstract protected function init(): void
  - public function __construct() { $this->init(); }

core/base/controller-base.php
  - Abstract class Controller_Base
  - protected string $namespace = 'directories-builder-pro/v1'
  - protected function register_route(string $path, array $args): void
    wraps register_rest_route()
  - protected function success($data): WP_REST_Response
  - protected function error(string $message, int $code): WP_Error

core/base/model-base.php
  - Abstract class Model_Base
  - abstract public function get_table_name(): string
  - public function find(array $where = []): array
  - public function find_by_id(int $id): ?array
  - public function insert(array $data): int|false
  - public function update(int $id, array $data): bool
  - public function delete(int $id): bool
  - All queries use $wpdb->prepare()

core/managers/module-manager.php
  - class Module_Manager
  - private array $modules = []
  - public function register_modules(array $classes): void
    instantiates each, stores by get_name()
  - public function get_module(string $name): ?Module_Base

core/managers/asset-manager.php
  - class Asset_Manager
  - Hooks wp_enqueue_scripts: enqueue frontend.css + frontend.js
  - Hooks admin_enqueue_scripts: enqueue admin.css + admin.js
  - wp_localize_script passes dbpData:
    { ajaxurl, nonce, pluginUrl, mapsKey, distanceUnit }
  - Enqueues Google Maps JS only on pages that need it (check query var)

core/managers/ajax-manager.php
  - class Ajax_Manager
  - private array $handlers = []
  - public function register(
        string $action,
        callable $callback,
        bool $nopriv = false
    ): void
  - Registers wp_ajax_{action} and optionally wp_ajax_nopriv_{action}

core/database/schema.php
  - class Schema
  - public static function get_tables(): array
    returns associative array: table_name => CREATE TABLE SQL
  - Include all 6 tables from prd.md:
    dbp_businesses, dbp_business_meta, dbp_reviews,
    dbp_review_votes, dbp_claims, dbp_checkins
  - Use $wpdb->prefix before each table name
  - All SQL compatible with dbDelta()

core/database/migrations.php
  - class Migrations
  - public static function run(): void
    - require_once ABSPATH . 'wp-admin/includes/upgrade.php'
    - foreach Schema::get_tables() as $sql → dbDelta($sql)
    - update_option('dbp_db_version', DBP_VERSION)
  - public static function needs_upgrade(): bool
    - compare get_option('dbp_db_version') with DBP_VERSION

core/helpers/functions.php
  - dbp_get_star_html(float $rating, bool $show_number = true): string
    returns SVG star icons (filled/half/empty) + optional numeric label
  - dbp_format_distance(float $meters, string $unit = 'km'): string
  - dbp_get_price_label(int $level): string   // $, $$, $$$, $$$$
  - dbp_time_ago(string $datetime): string
  - dbp_get_business_permalink(int $post_id): string
  - dbp_get_placeholder_image_url(): string
  - dbp_is_business_open(array $hours): bool
    checks current day + time against stored hours JSON

core/helpers/geo-helper.php
  - class Geo_Helper
  - public static function haversine(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float   // returns distance in km
  - public static function get_bounding_box(
        float $lat, float $lng, float $radius_km
    ): array   // [min_lat, max_lat, min_lng, max_lng]


──────────────────────────────────────
INCLUDES LAYER
──────────────────────────────────────

includes/autoloader.php
  - spl_autoload_register closure
  - Namespace map:
      DirectoriesBuilderPro\Core\     → /core/
      DirectoriesBuilderPro\Modules\  → /modules/
      DirectoriesBuilderPro\          → /includes/
  - Convert namespace separators to directory separators
  - Convert class name CamelCase to kebab-case for filename

includes/plugin.php
  - class Plugin (singleton)
  - private static ?Plugin $instance = null
  - public static function instance(): Plugin
  - private Module_Manager $module_manager
  - private Asset_Manager $asset_manager
  - private Ajax_Manager $ajax_manager
  - private function __construct()
      - init managers
      - add_action('init', [$this, 'on_init'])
      - add_action('wp_enqueue_scripts', [$this->asset_manager, 'enqueue_frontend'])
      - add_action('admin_enqueue_scripts', [$this->asset_manager, 'enqueue_admin'])
      - add_action('admin_menu', [$this, 'register_admin_pages'])
      - add_filter('template_include', [$this, 'override_templates'])
  - public function on_init(): void
      - register Business CPT
      - register taxonomies
      - register REST routes (delegate to module controllers)
  - private function register_modules(): void
      - pass all 5 module class names to Module_Manager
  - public function register_admin_pages(): void
      - add_menu_page for Dashboard
      - add_submenu_page for Settings, Review Moderation

includes/post-types/business.php
  - class Business_Post_Type
  - public function register(): void
  - CPT slug: dbp_business
  - Rewrite slug: business
  - Has archive: true, publicly queryable: true
  - Supports: title, editor, thumbnail, excerpt, custom-fields
  - Labels: full set (name, singular_name, add_new, edit_item, etc.)
  - Register taxonomy dbp_category (hierarchical, like category)
  - Register taxonomy dbp_neighborhood (flat, like tag)
  - register_meta for JSON-LD injection flag

includes/services/business-service.php
  - class Business_Service
  - Depends on Business_Repository
  - get_business(int $id): ?array
  - create_business(array $data): int|WP_Error
    validates required fields, geocodes if lat/lng absent
  - update_business(int $id, array $data): bool|WP_Error
  - delete_business(int $id): bool
  - calculate_average_rating(int $business_id): float
    queries dbp_reviews WHERE status='approved', updates avg_rating cache
  - get_featured_businesses(int $limit = 6): array
  - get_nearby_businesses(float $lat, float $lng, float $radius_km): array
  - get_similar_businesses(int $business_id, int $limit = 3): array

includes/services/review-service.php
  - class Review_Service
  - Depends on Review_Repository, Business_Service
  - submit_review(array $data): int|WP_Error
    - validate: rating 1–5, content min 25 chars, one per user per business
    - calculate trust score
    - set status based on trust score + moderation mode setting
    - fire do_action('dbp/review/submitted', $id, $business_id, $user_id)
    - update business avg_rating cache
  - calculate_trust_score(int $user_id, array $review_data): int
    implements scoring table from prd.md
  - approve_review(int $id): bool
    - update status, fire do_action('dbp/review/approved', $id)
    - update business avg_rating cache
  - reject_review(int $id, string $reason = ''): bool
  - mark_spam(int $id): bool  // also affects reviewer trust history
  - get_reviews_for_business(int $business_id, array $args): array
    args: page, per_page, orderby (relevance|newest|highest|lowest), status

includes/services/search-service.php
  - class Search_Service
  - Depends on Business_Repository, Geo_Helper
  - search(array $args): array
    args: q, lat, lng, radius_km, category, min_rating, price,
          open_now, orderby, page, per_page
    - apply bounding box pre-filter if lat/lng present
    - full-text LIKE query on name + description if q present
    - filter by category, rating, price
    - open_now: parse hours JSON and compare against current time
    - return { items: [], total: int, pages: int }
    - apply apply_filters('dbp/search/args', $args) before execution
  - autocomplete(string $query): array
    returns top 5 business names + top 3 matching category names

includes/services/user-service.php
  - class User_Service
  - get_user_profile(int $user_id): array
    returns: display_name, avatar_url, bio, review_count,
             photo_count, points, badges, is_elite, member_since
  - get_user_reviews(int $user_id, int $page = 1): array
  - award_points(int $user_id, int $points, string $reason): void
    stores in user_meta 'dbp_points' and 'dbp_points_log'
  - get_user_badges(int $user_id): array
  - get_user_points(int $user_id): int

includes/repositories/business-repository.php
  - class Business_Repository extends Model_Base
  - get_table_name(): string  // {prefix}dbp_businesses
  - find_near(float $lat, float $lng, array $bbox, array $args): array
    bounding box pre-filter + haversine post-sort in PHP for small sets
  - search_fulltext(string $query, array $filters): array
    uses $wpdb->prepare() with LIKE wildcards
  - get_meta(int $business_id, string $key): mixed
  - update_meta(int $business_id, string $key, mixed $value): bool
  - get_all_meta(int $business_id): array

includes/repositories/review-repository.php
  - class Review_Repository extends Model_Base
  - get_table_name(): string  // {prefix}dbp_reviews
  - find_by_business(int $business_id, array $args): array
  - find_by_user(int $user_id, int $page = 1): array
  - find_pending(): array
  - get_average_rating(int $business_id): float
  - get_vote_counts(int $review_id): array  // { helpful: int, not_helpful: int }
  - has_voted(int $review_id, int $user_id): bool
  - insert_vote(int $review_id, int $user_id, string $type): bool
  - user_has_reviewed(int $business_id, int $user_id): bool


──────────────────────────────────────
MODULES
──────────────────────────────────────

Implement all files for each module. No stubs.

── reviews ──

modules/reviews/module.php
  - class Reviews_Module extends Module_Base
  - get_name(): 'reviews'
  - init():
    - new Review_Controller() and register its REST routes on rest_api_init
    - register AJAX handlers via Ajax_Manager:
        dbp_submit_review (nopriv: false)
        dbp_vote_review   (nopriv: false)
        dbp_flag_review   (nopriv: false)

modules/reviews/controllers/review-controller.php
  - class Review_Controller extends Controller_Base
  - register_routes(): void called on rest_api_init
  - GET  /reviews
      params: business_id (required), page, per_page, orderby
      permission_callback: __return_true
  - POST /reviews
      body: business_id, rating, content, photos (array of attachment IDs)
      permission_callback: is_user_logged_in()
      handler calls Review_Service::submit_review()
  - PUT  /reviews/{id}
      permission_callback: own review OR manage_options
  - DELETE /reviews/{id}
      permission_callback: own review OR manage_options

modules/reviews/models/review.php
  - class Review extends Model_Base
  - get_table_name(): string
  - Properties typed: int $id, int $business_id, int $user_id,
    int $rating, string $content, string $status, int $trust_score,
    int $helpful, int $not_helpful, string $created_at

modules/reviews/ajax/review-ajax.php
  - class Review_Ajax
  - handle_submit(): void
      check_ajax_referer('dbp_nonce', 'nonce')
      sanitize all fields, call Review_Service::submit_review()
      wp_send_json_success or wp_send_json_error
  - handle_vote(): void
      check_ajax_referer, current_user_can('read')
      call Review_Repository::insert_vote()
      return updated counts
  - handle_flag(): void
      check_ajax_referer, current_user_can('read')
      insert flag vote, notify admin if threshold reached

modules/reviews/templates/review-list.php
  - Sort control tabs: Most Relevant | Newest | Highest Rated | Lowest Rated
  - Loop through reviews, include review-item.php partial for each
  - "Load more" button: data-page, data-business-id attributes for JS
  - Empty state message when no reviews exist
  - Total count label: "X Reviews"

modules/reviews/templates/review-form.php
  - If not logged in: show login prompt with link, exit early
  - If user already reviewed: show "Edit your review" notice
  - Star picker: 5 SVG stars, role="radiogroup" for a11y
  - Textarea: minlength="25", live counter "X / 25 minimum characters"
  - Photo upload: drag-drop zone, input[type=file] multiple accept="image/*"
    max 5 files, thumbnail previews with × remove button
  - Submit button with spinner on loading state
  - Hidden nonce field: wp_nonce_field('dbp_nonce', 'nonce')

── business ──

modules/business/module.php
  - class Business_Module extends Module_Base
  - get_name(): 'business'
  - init(): register REST routes and AJAX handlers

modules/business/controllers/business-controller.php
  - GET  /businesses
      params: category, lat, lng, radius, page, per_page
      delegates to Search_Service::search()
  - GET  /businesses/{id}
      returns full business object + meta + avg_rating
  - POST /businesses
      permission_callback: current_user_can('manage_options')
  - PUT  /businesses/{id}
      permission_callback: claimed_by === get_current_user_id() OR manage_options

modules/business/models/business.php
  - class Business extends Model_Base
  - get_table_name(): string
  - Typed properties matching dbp_businesses schema from prd.md
  - to_array(): array
  - from_array(array $data): static

modules/business/ajax/business-ajax.php
  - handle_get_hours(): void
      public (nopriv: true), returns parsed hours array as JSON
  - handle_update_meta(): void
      check_ajax_referer, verify owner or admin capability
      sanitize key/value, call Business_Repository::update_meta()

modules/business/templates/business-header.php
  - Photo carousel: wp_get_attachment_image() for each photo, fallback placeholder
  - Business name (h1), overall star rating, review count, price label, category
  - Action buttons: Call (tel: link), Directions (map-service URL),
    Website (external link), Claim (if unclaimed)
  - Claimed badge (if claimed_by is set)
  - Featured badge (if featured = 1)

modules/business/templates/business-about.php
  - Description (wp_kses_post output)
  - Attributes grid: Wi-Fi, Parking, Outdoor Seating, Delivery, etc.
    icons + labels from dbp_business_meta
  - Opening hours table: day | open time | close time, highlight today
  - "Closed" label for days with no hours set
  - Embedded Google Map via Map_Service::get_embed_url()

── search ──

modules/search/module.php
  - class Search_Module extends Module_Base
  - get_name(): 'search'
  - init(): register REST routes and AJAX, add shortcodes:
    [dbp_search_bar]    → renders search-bar.php
    [dbp_search_results] → renders search-results.php

modules/search/controllers/search-controller.php
  - GET /search
      Accepts: q, lat, lng, radius_km, category, min_rating,
               price, open_now, orderby, page, per_page
      Returns: { businesses: [], total: int, pages: int }
      Each business item includes: id, name, slug, avg_rating,
        review_count, price_level, category, distance, thumbnail_url,
        is_claimed, is_featured, is_open
  - GET /autocomplete
      Accepts: q (required, min 2 chars)
      Returns: { suggestions: [{ type: 'business'|'category', label, value }] }

modules/search/ajax/search-ajax.php
  - handle_search(): void
      public (nopriv: true)
      sanitize args, call Search_Service::search()
      render business-card.php partials into output buffer
      return HTML string for non-JS fallback
  - handle_autocomplete(): void
      public (nopriv: true), debounced by JS (300ms)
      sanitize query, call Search_Service::autocomplete()
      return JSON array of suggestions

modules/search/templates/search-bar.php
  - <form> equivalent using divs + JS submit (not HTML form — avoids page reload)
  - "What?" input: placeholder "Restaurants, plumbers, dentists…"
  - "Where?" input: placeholder "City, neighborhood, or zip"
    + geolocation button (navigator.geolocation icon)
  - Search button
  - Autosuggest dropdown panel (hidden by default, shown by JS)
  - data-nonce attribute for AJAX

modules/search/templates/search-results.php
  - Results summary: "X results for 'query' near 'location'"
  - Filter chips row (scrollable on mobile):
    Open Now | $ | $$ | $$$ | $$$$ | 4★+ | 5km | 10km | 25km
  - Sort select: Relevance | Distance | Highest Rated | Most Reviewed | Newest
  - List/Map toggle buttons
  - Results grid: loop business-card.php partials
  - Map container div (initialized by maps.js)
  - Pagination: previous / page numbers / next
  - Empty state: "No results found" with search tips
  - Loading skeleton: shown while AJAX in flight

── maps ──

modules/maps/module.php
  - class Maps_Module extends Module_Base
  - get_name(): 'maps'
  - init():
    - register setting: dbp_google_maps_key in settings page
    - add_filter to asset manager: conditionally enqueue Maps JS
      only when is_singular('dbp_business') or is_post_type_archive('dbp_business')
      or a page contains [dbp_search_results] shortcode

modules/maps/services/map-service.php
  - class Map_Service
  - public static function get_embed_url(
        float $lat, float $lng, string $address = ''
    ): string
    returns Google Maps embed URL with API key
  - public static function build_geojson(array $businesses): array
    converts business array to GeoJSON FeatureCollection
    each Feature: { type, geometry: {type: Point, coordinates: [lng, lat]},
                    properties: { id, name, slug, rating, review_count,
                                  price_level, thumbnail_url, permalink } }
  - public static function get_static_map_url(
        float $lat, float $lng, int $zoom = 15, string $size = '600x300'
    ): string
  - public static function get_directions_url(string $address): string
    returns Google Maps directions URL

── claims ──

modules/claims/module.php
  - class Claims_Module extends Module_Base
  - get_name(): 'claims'
  - init(): register REST routes and AJAX handlers

modules/claims/controllers/claim-controller.php
  - POST /claims
      body: business_id, owner_name, email, phone, verification_method
      permission_callback: is_user_logged_in()
      validate: business exists, not already claimed, user hasn't claimed before
      insert to dbp_claims, send admin notification email
  - GET /claims/{id}
      permission_callback: current_user_can('manage_options')
  - PUT /claims/{id}/approve
      permission_callback: current_user_can('manage_options')
      update claim status, set business claimed_by, notify claimant by email
      fire do_action('dbp/business/claimed', $business_id, $user_id)
  - PUT /claims/{id}/reject
      permission_callback: current_user_can('manage_options')
      body: reason (optional)
      update claim status, store reason, notify claimant by email

modules/claims/ajax/claim-ajax.php
  - handle_submit(): void
      check_ajax_referer('dbp_nonce', 'nonce'), is_user_logged_in()
      sanitize all fields, delegate to REST handler logic
      wp_send_json_success with claim ID on success


──────────────────────────────────────
ADMIN
──────────────────────────────────────

admin/pages/dashboard.php
  - Four stats cards using $wpdb queries:
      Total Businesses (count dbp_businesses WHERE status='active')
      Reviews This Week (count dbp_reviews WHERE created_at >= 7 days ago AND status='approved')
      Pending Claims   (count dbp_claims WHERE status='pending')
      Pending Reviews  (count dbp_reviews WHERE status='pending')
  - Recent Activity table: last 10 rows from dbp_reviews with
    reviewer name, business name, rating, date, status badge
  - Quick action links: "Add Business" | "Moderate Reviews" | "Settings"

admin/pages/settings.php
  - Use WordPress Settings API (register_setting, add_settings_section,
    add_settings_field)
  - Section: Maps
      Field: dbp_google_maps_key (text)
  - Section: Reviews
      Field: dbp_moderation_mode (radio: auto_approve | manual)
      Field: dbp_min_review_length (number, default 25)
      Field: dbp_max_photos_per_review (number, default 5)
  - Section: Search
      Field: dbp_default_radius_km (number, default 10)
      Field: dbp_results_per_page (number, default 12)
      Field: dbp_distance_unit (radio: km | miles)
  - Section: Business
      Field: dbp_allow_user_submissions (checkbox)
  - Save button via standard settings form
  - Settings saved notice on success

admin/views/business-edit.php
  - Hook: add_meta_boxes for dbp_business post type
  - Meta Box: Location
      Fields: address, city, state, zip, country, lat (hidden), lng (hidden)
      Map picker: small Google Map, click to set marker and auto-fill lat/lng
  - Meta Box: Contact
      Fields: phone, website, email
  - Meta Box: Business Details
      Fields: price_level (radio $–$$$$), primary category (select),
      opening hours: table with 7 rows (Mon–Sun), open/close time inputs
      per day + "Closed" checkbox
  - Meta Box: Status
      Fields: status (select: active/inactive/pending),
              featured (checkbox)
  - All fields saved via save_post hook with nonce verification and
    current_user_can('edit_post', $post_id) check

admin/views/review-moderation.php
  - Extend WP_List_Table
  - Columns: Reviewer | Business | Rating | Review Excerpt | Date | Status
  - Row actions: Approve | Reject | Mark as Spam | View | Delete
  - Bulk actions: Approve Selected | Reject Selected | Mark as Spam
  - Filter tabs above table: All | Pending | Approved | Rejected | Spam
    each tab shows count in parentheses
  - Inline rejection: clicking Reject reveals a small textarea for reason
    before confirming
  - All actions POST to admin-post.php with nonce + action + review_id


──────────────────────────────────────
PUBLIC TEMPLATES
──────────────────────────────────────

public/templates/single-business.php
  - Override single-dbp_business.php via template_include filter
  - get_header()
  - Section 1: include business-header.php (modules/business/templates)
  - Section 2: include business-about.php  (modules/business/templates)
  - Section 3: Photos grid
      Query attached photos, show up to 12 thumbnails in CSS grid
      "See all X photos" link if more exist
      Lightbox on click (vanilla JS or small vendored lib in /assets/lib/)
  - Section 4: Reviews
      include review-list.php  (modules/reviews/templates)
      include review-form.php  (modules/reviews/templates)
  - Section 5: Similar businesses
      3 business cards (business-card.php partials) from same category + city
  - JSON-LD: output Schema.org LocalBusiness structured data in <head>
    via wp_head action:
    { "@context": "https://schema.org", "@type": "LocalBusiness",
      name, address, telephone, url, geo, aggregateRating }
  - get_footer()

public/templates/archive-business.php
  - Override archive for dbp_business via template_include filter
  - get_header()
  - include search-bar.php      (modules/search/templates)
  - include search-results.php  (modules/search/templates)
  - Initialize map: output <script> block with GeoJSON from initial results
    stored in a data attribute on the map container
  - get_footer()

public/partials/business-card.php
  - Accepts: $business (array)
  - Thumbnail: get_the_post_thumbnail or dbp_get_placeholder_image_url()
  - Business name: linked to dbp_get_business_permalink()
  - Star rating: dbp_get_star_html($business['avg_rating'])
  - Review count: esc_html(sprintf(_n('%d review','%d reviews',$n,'dbp'),$n))
  - Price label: dbp_get_price_label($business['price_level'])
  - Category: first category term name
  - Distance: dbp_format_distance() if $business['distance'] set
  - Badges: "Claimed" (if claimed_by set), "Featured" (if featured=1),
    "New" (if created_at within last 30 days)
  - All output fully escaped

public/partials/review-item.php
  - Accepts: $review (array), $business_id (int)
  - Avatar: get_avatar($review['user_id'], 40) with fallback
  - Display name + Elite badge (if user has dbp_elite meta = 1)
  - Star rating: dbp_get_star_html($review['rating'], false)
  - Relative date: dbp_time_ago($review['created_at'])
  - Review text: truncated at 300 chars with "Read more" toggle (JS)
  - Photos: up to 3 thumbnails with lightbox, "See X more" link if needed
  - Vote buttons: Helpful (count) | Not Helpful (count)
    disabled if current user has already voted
  - Flag link: "Report" — fires dbp_flag_review AJAX
  - Owner response block (if review has a response stored):
      Owner avatar, "Owner" label, date, response text
  - All output fully escaped


──────────────────────────────────────
ASSETS
──────────────────────────────────────

assets/css/frontend.css
  - CSS custom properties on :root:
    --dbp-primary, --dbp-primary-dark, --dbp-secondary,
    --dbp-text, --dbp-text-light, --dbp-border, --dbp-bg,
    --dbp-radius, --dbp-shadow, --dbp-star-color
  - Business card grid: CSS Grid, 1 col mobile / 2 col tablet / 3 col desktop
  - Star rating component: SVG-based, filled / half / empty states
  - Filter chips bar: flex, scrollable, active state, clear button
  - Split view: 60% list / 40% map on ≥ 1024px, stacked on mobile
  - Map container: min-height 400px, sticky within split view
  - Review form: star picker hover states, photo upload drag-drop zone style,
    character counter color change on min met
  - Review item: avatar + content flex layout, vote button states
  - Loading skeleton: pulsing grey blocks animation for cards + reviews
  - All breakpoints mobile-first using min-width media queries

assets/css/admin.css
  - Stats card grid: 4 columns on ≥ 1200px, 2 on tablet, 1 on mobile
  - Card styles: white bg, border, shadow, icon + number + label
  - Moderation table: status badge colors (pending=yellow, approved=green,
    rejected=red, spam=grey)
  - Settings page: section headers, field descriptions, input widths
  - Map picker in meta box: 300px height Google Map container

assets/js/frontend.js
  - 'use strict', IIFE or ES module
  - Receives window.dbpData (localized by Asset_Manager)
  - Imports/initializes: ReviewsModule, SearchModule, MapsModule
  - Exposes window.dbp = { reviews, search, maps } for debugging

assets/js/admin.js
  - Initializes Google Maps picker in business meta box
    (click on map → set hidden lat/lng fields + address)
  - Review moderation: AJAX for row actions (approve/reject/spam)
    with optimistic UI + rollback on error
  - Settings page: tab switching if settings has multiple tab sections

assets/js/modules/reviews.js
  - Star picker:
    click handler sets data-rating, updates visual filled state
    hover preview restores on mouseout unless value set
    keyboard: ArrowLeft/ArrowRight to change, Enter to confirm
  - Review form:
    submit handler: collect rating + content + photo IDs, POST to REST
    or AJAX, show loading state on button, display success/error message
    validation: rating > 0 and content.length >= minLength before submit
  - Photo upload:
    drag-drop events on zone, FileReader previews, remove button per thumb
    enforce max 5 files and 5MB per file with user-visible error messages
  - Voting:
    click Helpful/Not Helpful → POST to REST /reviews/{id}/vote
    optimistic count update, disable both buttons after vote
  - Load more:
    click → fetch next page from REST, append rendered HTML,
    hide button if no more pages

assets/js/modules/search.js
  - Autocomplete:
    input event on both fields with 300ms debounce
    fetch /autocomplete?q=, render dropdown, keyboard nav (↑↓ Enter Esc)
    click suggestion → fill field + submit search
  - Filter chips:
    click chip → toggle active class + update internal filter state
    trigger new search with updated args
  - Full filter drawer:
    open/close animation, Apply Filters → update chips + trigger search
    Reset → clear all filters + trigger search
  - Search execution:
    build query params from current filter state
    fetch /search, render business-card HTML, update result count
    update URL via history.pushState for shareable links
  - List↔map sync:
    mouseenter on card → dispatch event to maps.js to highlight marker
    marker click → scroll corresponding card into view, add highlight class
  - Load more / infinite scroll:
    IntersectionObserver on sentinel element at bottom of list
    fetch next page and append cards
  - Near Me:
    navigator.geolocation.getCurrentPosition success → reverse geocode
    using Maps Geocoding API, fill Where field, trigger search

assets/js/modules/maps.js
  - Initialize Google Map in #dbp-map container
  - Load GeoJSON from data-geojson attribute on map container
  - Plot markers from GeoJSON; use MarkerClusterer for dense sets
    (load MarkerClusterer from /assets/lib/)
  - Marker click:
    open Google Maps InfoWindow with: name, rating, review count, permalink
    dispatch custom event for search.js to highlight + scroll card
  - On map idle (drag/zoom end):
    get visible bounds, filter markers, dispatch event to update list
    (optional: re-query /search with bbox params)
  - Single business page:
    place single marker at business lat/lng
    open InfoWindow immediately with business name + "Get Directions" link


──────────────────────────────────────
ROOT FILES
──────────────────────────────────────

uninstall.php
  - if (!defined('WP_UNINSTALL_PLUGIN')) exit
  - global $wpdb
  - $tables = ['dbp_businesses','dbp_business_meta','dbp_reviews',
                'dbp_review_votes','dbp_claims','dbp_checkins']
  - foreach $tables: $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}")
  - delete_option('dbp_db_version')
  - delete_option('dbp_settings')
  - delete_option('dbp_google_maps_key')
  - flush_rewrite_rules()

README.md
  - Plugin name, version, license (GPLv2+), description
  - Requirements: WordPress 6.0+, PHP 8.0+, MySQL 5.7+
  - Installation: upload → activate → set Google Maps API key in Settings
  - Configuration:
    - Google Maps API Key (required for maps and geolocation)
    - Moderation mode (auto-approve vs manual)
    - Distance unit (km vs miles)
  - Shortcodes:
    [dbp_search_bar]         — renders the search bar
    [dbp_search_results]     — renders the results grid + map
    [dbp_review_form id="X"] — renders the review form for business X
  - Template tags:
    dbp_get_star_html(float $rating): string
    dbp_get_business_permalink(int $post_id): string
    dbp_format_distance(float $meters, string $unit): string
    dbp_is_business_open(array $hours): bool
  - Action hooks:
    dbp/review/submitted     (review_id, business_id, user_id)
    dbp/review/approved      (review_id)
    dbp/business/claimed     (business_id, user_id)
    dbp/checkin/recorded     (checkin_id, business_id, user_id)
  - Filter hooks:
    dbp/search/args          (args array)
    dbp/review/trust_score   (score, user_id, review_data)
    dbp/business/card_html   (html, business_id)
    dbp/settings/defaults    (defaults array)
  - Module list: reviews, business, search, maps, claims
  - Changelog: 1.0.0 — Initial release


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 4 — QUALITY & COMPLETION CHECKS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

After all files are written, run through this checklist:

1. ACTIVATION SAFETY
   - Fresh WordPress install: activate plugin → no PHP errors
   - All 6 custom tables created correctly (verify with SHOW TABLES)
   - dbp_business CPT and taxonomies registered

2. DEACTIVATION / UNINSTALL
   - Deactivation: rewrite rules flushed, no errors
   - Uninstall: all 6 tables dropped, all options deleted

3. SECURITY AUDIT — verify every item:
   □ Every AJAX handler calls check_ajax_referer()
   □ Every REST endpoint has a permission_callback (not __return_true
     on write endpoints)
   □ Every $_POST / $_GET value is sanitized before use
   □ Every output value is escaped (esc_html, esc_attr, esc_url,
     wp_kses_post as appropriate)
   □ No string interpolation in SQL — all queries use $wpdb->prepare()
   □ File uploads: MIME type validated, size limited, not stored in
     web-accessible location without sanitization

4. INTERNATIONALISATION
   □ Every user-facing string uses __() or _e() with 'directories-builder-pro'
   □ Plurals use _n()
   □ .pot file exists at /languages/directories-builder-pro.pot

5. CODING STANDARDS
   □ declare(strict_types=1) in every PHP file
   □ All classes namespaced under DirectoriesBuilderPro\
   □ No PHP short tags
   □ No inline SQL outside repository classes
   □ WP_List_Table used for moderation table (not custom HTML table)

6. FUNCTIONAL SMOKE TEST — trace these flows manually:
   □ Search: q="pizza" + lat/lng → returns businesses sorted by distance
   □ Review: submit review → appears in moderation queue → approve →
     business avg_rating updates → review visible on front end
   □ Claim: submit claim → admin approves → business claimed_by set →
     owner can edit business via REST PUT
   □ Maps: archive page loads → markers plotted → clicking marker
     highlights card → Near Me fills Where field


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CONSTRAINTS & STYLE RULES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

- NO external Composer/npm dependencies beyond what is in /assets/lib/
  (vendored, committed). Pure WordPress APIs only.
- PHP 8.0+ syntax throughout. Use typed properties, match, named args,
  nullsafe operator, union types, fibers if appropriate.
- All classes under DirectoriesBuilderPro\ namespace.
- All database access through repository classes only.
- All module-to-module communication via WordPress actions/filters,
  not direct method calls across module boundaries.
- Plugin prefix for all options, meta keys, AJAX actions,
  REST namespaces, CPT slugs, and CSS classes: dbp_
- Do NOT generate placeholder or stub files. Every file must contain
  complete, working, production-ready code.
```
