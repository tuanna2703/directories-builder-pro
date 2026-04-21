# Agent Prompt — Template Module
## Directories Builder Pro

> Paste this prompt into Claude Code, Cursor, Aider, or any agentic coding tool.

---

```
You are a senior WordPress plugin architect. You are extending the
Directories Builder Pro plugin with a new, self-contained Template Module.

Read prd.md, structure.md, and prompt.md in full before writing any code.
They define the existing plugin conventions (namespace DBP prefix, base
classes, module pattern, file naming) that this new module must conform
to exactly. Every file you produce must feel native to the existing codebase.


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CONTEXT & MOTIVATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

The plugin currently renders HTML in several inconsistent ways:

  - PHP logic and HTML markup are mixed inside service and controller files.
  - Each module owns its own /templates/ folder with no shared resolution
    logic, no theme-override support, and no stable data contracts.
  - There is no central place for themes to override plugin output.
  - Partials are duplicated across modules (e.g., star ratings, pagination,
    badges are copy-pasted rather than shared).

The goal is to build a Template Module that becomes the single, authoritative
rendering layer for the entire plugin — covering frontend templates, admin
templates, and shared partials — while providing:

  1. A clean PHP API: Template::render('business/card', $args)
  2. A three-level path resolution: child theme → parent theme → plugin default
  3. A stable data contract per template (documented $args spec)
  4. A per-request path cache to avoid repeated filesystem lookups
  5. Before/after action hooks on every render call
  6. Filters for path customisation (multi-site, white-label, third-party addons)
  7. Security: slug validation, no user-input in file paths, escaping owned
     by the template itself

Once the Template Module is built, ALL existing inline HTML across the plugin
must be migrated into template files and rendered through the new API.


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 1 — RESEARCH & ARCHITECTURE DESIGN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Before writing any code, produce a written architecture document that covers
the following six areas. Output this document as a code comment block at the
top of modules/template/module.php before the class declaration.

1. TEMPLATE TYPE TAXONOMY
   Define the three template categories and their rules:

   a) Frontend Templates
      - Theme-aware (inherit typography, colors from active theme)
      - Override-able by child theme and parent theme
      - Strict $args data contract per template
      - All dynamic output escaped in context

   b) Admin Templates
      - Follow core WordPress admin UI patterns (WP_List_Table, metaboxes,
        notices, help tabs)
      - NOT override-able by themes (admin output must stay stable)
      - Modular and reusable across plugin admin pages

   c) Partial Templates (components)
      - Narrow, well-defined inputs ($label, $url, $variant style)
      - Shared by both frontend and admin
      - Optionally override-able for frontend use; not for admin use
      - Must never contain heavy plugin logic — presentational only

2. RESOLUTION STRATEGY
   Describe the three-level search order and explain why each level exists:
     Level 1: wp-content/themes/{child-theme}/directories-builder-pro/{slug}.php
     Level 2: wp-content/themes/{parent-theme}/directories-builder-pro/{slug}.php
     Level 3: wp-content/plugins/directories-builder-pro/templates/{slug}.php
   Explain how admin templates are excluded from theme override (Level 3 only).

3. CACHING STRATEGY
   Describe the static per-request cache:
     private static array $located_cache = []
   Explain why transient/object caching is not appropriate for path resolution.
   Explain when the cache must be bypassed (e.g., unit tests).

4. DATA CONTRACT PATTERN
   Describe how each template declares its expected $args as a docblock,
   and how the renderer validates required keys before include (dev mode only,
   guarded by WP_DEBUG).

5. HOOKS SPECIFICATION
   List every action and filter the system fires, in order, for a single
   render() call:
     Filter:  dbp/template/paths           — alter base search directories
     Filter:  dbp/template/candidates      — alter candidate filenames per slug
     Filter:  dbp/template/locate          — override the final resolved path
     Filter:  dbp/template/args            — modify $args before template include
     Action:  dbp/template/before          — fires before include (slug, path, args)
     Action:  dbp/template/before/{slug}   — slug-specific before hook
     Action:  dbp/template/after           — fires after include
     Action:  dbp/template/after/{slug}    — slug-specific after hook
     Action:  dbp/template/missing         — fires when no file is found

6. MIGRATION PLAN
   List every existing file in the plugin that contains inline HTML and must
   be refactored. For each file, state:
     - Current file path
     - The template slug(s) that will replace its inline HTML
     - The $args the new template will expect


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 2 — FOLDER STRUCTURE & PLANNING
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Extend structure.md with the new Template Module layout.
Output the complete updated tree. The module must follow existing plugin
conventions; its entry point is modules/template/module.php.

New paths to create (expand as needed):

modules/template/
  module.php                        ← Template_Module extends Module_Base
  loader/
    template-loader.php             ← Path resolution + per-request cache
  renderer/
    template-renderer.php           ← ob_start / include / hooks / return
  contracts/
    contract-validator.php          ← Dev-mode $args contract checker

templates/                          ← Plugin-default template files (new root)
  business/
    card.php                        ← Replaces public/partials/business-card.php
    single.php                      ← Replaces public/templates/single-business.php
    archive.php                     ← Replaces public/templates/archive-business.php
    header.php                      ← Replaces modules/business/templates/business-header.php
    about.php                       ← Replaces modules/business/templates/business-about.php
  reviews/
    list.php                        ← Replaces modules/reviews/templates/review-list.php
    item.php                        ← Replaces public/partials/review-item.php
    form.php                        ← Replaces modules/reviews/templates/review-form.php
  search/
    bar.php                         ← Replaces modules/search/templates/search-bar.php
    results.php                     ← Replaces modules/search/templates/search-results.php
  forms/
    form.php                        ← Generic form shell used by Form Module renderer
    group.php                       ← Renders a single field group with its tab
    field.php                       ← Dispatches to field-type partials
  admin/
    dashboard.php                   ← Replaces admin/pages/dashboard.php HTML
    settings.php                    ← Replaces admin/pages/settings.php HTML wrapper
    moderation.php                  ← Replaces admin/views/review-moderation.php HTML
    business-edit.php               ← Replaces admin/views/business-edit.php HTML
    user-profile.php                ← Admin user profile page HTML
  partials/
    star-rating.php                 ← Shared star rating HTML component
    pagination.php                  ← Shared pagination links
    badge.php                       ← Shared badge (Claimed / Featured / New / Elite)
    price-label.php                 ← Shared price level ($–$$$$) display
    avatar.php                      ← Shared user avatar with fallback
    button.php                      ← Shared CTA button with variants
    notice.php                      ← Shared success / error / info notice
    empty-state.php                 ← Shared "no results" / "nothing here" block
    loading-skeleton.php            ← Shared pulsing placeholder cards

Also produce:
  - Complete file-by-file implementation order
  - Full list of $args contracts per template (slug → required keys → optional keys)
  - Security checklist: slug validation, path traversal prevention,
    escaping rules per template context


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 3 — BUILD
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Implement every file. No stubs. Complete, production-ready code.
Follow this exact order:


──────────────────────────────────────
MODULE ENTRY POINT
──────────────────────────────────────

modules/template/module.php
  - declare(strict_types=1)
  - Namespace: DirectoriesBuilderPro\Modules\Template
  - class Template_Module extends Module_Base
  - get_name(): 'template'
  - init():
      - Instantiate Template_Loader and Template_Renderer; store on module
      - Register template_redirect hook to override CPT templates:
          add_filter('template_include', [$this, 'override_cpt_templates'])
      - Register shortcodes:
          [dbp_search_bar]      → Template::render('search/bar', $args)
          [dbp_search_results]  → Template::render('search/results', $args)
          [dbp_review_form id="X"] → Template::render('reviews/form', $args)
  - override_cpt_templates(string $template): string
      if is_singular('dbp_business'): return templates/business/single.php path
      if is_post_type_archive('dbp_business'): return templates/business/archive.php path
      return $template unchanged
  - Static facade method for convenience (mirrors WC's wc() pattern):
      public static function render(
          string $slug,
          array $args = [],
          bool $echo = true
      ): string
      delegates to Template_Renderer::render()

  Also expose a global function as a plugin-level helper:
    function dbp_template(string $slug, array $args = [], bool $echo = true): string
    This allows any file in the plugin to call dbp_template('partials/badge', $args)
    without injecting the module.


──────────────────────────────────────
LOADER
──────────────────────────────────────

modules/template/loader/template-loader.php
  - declare(strict_types=1)
  - Namespace: DirectoriesBuilderPro\Modules\Template\Loader
  - class Template_Loader
  - private static array $located_cache = []
  - public function locate(string $slug): ?string
      1. Validate slug:
           - Strip leading/trailing slashes
           - Reject if preg_match('/\.\./', $slug) — no directory traversal
           - Reject if contains characters outside [a-z0-9/_-]
           - If invalid: trigger_error() and return null
      2. Return cached result if isset(self::$located_cache[$slug])
      3. Build candidate filename: $slug . '.php'
      4. Build base directories via get_base_paths($slug):
           apply_filters('dbp/template/paths', $this->resolve_base_paths($slug), $slug)
      5. Build candidate list:
           apply_filters('dbp/template/candidates', [$candidate], $slug)
      6. Search: foreach base_path × candidates, check file_exists()
         return first match
      7. Allow full override:
           apply_filters('dbp/template/locate', $found_path, $slug)
      8. Cache result (including null as false) and return
  - private function resolve_base_paths(string $slug): array
      if slug starts with 'admin/':
        return [ DBP_PATH . 'templates/' ]   ← admin slugs: plugin only, no theme
      return [
        get_stylesheet_directory() . '/directories-builder-pro/',
        get_template_directory()   . '/directories-builder-pro/',
        DBP_PATH . 'templates/',
      ]
  - public static function flush_cache(): void
      self::$located_cache = []
      (called in unit tests and on plugin update)


──────────────────────────────────────
RENDERER
──────────────────────────────────────

modules/template/renderer/template-renderer.php
  - declare(strict_types=1)
  - Namespace: DirectoriesBuilderPro\Modules\Template\Renderer
  - class Template_Renderer
  - public function __construct(private Template_Loader $loader) {}
  - public function render(
        string $slug,
        array  $args = [],
        bool   $echo = true
    ): string
      1. $path = $this->loader->locate($slug)
         if null: do_action('dbp/template/missing', $slug, $args)
                  if WP_DEBUG: return html comment <!-- DBP: template not found: {slug} -->
                  return ''
      2. $args = apply_filters('dbp/template/args', $args, $slug, $path)
      3. if WP_DEBUG: Contract_Validator::check($slug, $args)
      4. ob_start()
         do_action('dbp/template/before', $slug, $path, $args)
         do_action("dbp/template/before/{$slug}", $path, $args)
         $this->include_template($path, $args)
         do_action('dbp/template/after', $slug, $path, $args)
         do_action("dbp/template/after/{$slug}", $path, $args)
         $output = ob_get_clean()
      5. if $echo: echo $output
         return $output
  - private function include_template(string $path, array $args): void
      // Deliberate non-extract: pass $args as single variable
      // Templates access data as $args['key'] or use extract selectively
      // with allow-list. Document this convention in every template docblock.
      include $path


──────────────────────────────────────
CONTRACT VALIDATOR
──────────────────────────────────────

modules/template/contracts/contract-validator.php
  - declare(strict_types=1)
  - Namespace: DirectoriesBuilderPro\Modules\Template\Contracts
  - class Contract_Validator
  - private static array $contracts = []
    // slug => [ 'required' => [], 'optional' => [] ]
  - public static function register(string $slug, array $contract): void
      self::$contracts[$slug] = $contract
  - public static function check(string $slug, array $args): void
      // Only runs when WP_DEBUG is true — zero cost in production
      if !isset(self::$contracts[$slug]): return
      foreach $contract['required'] as $key:
        if !array_key_exists($key, $args):
          trigger_error(
            "DBP Template '{$slug}' missing required arg: '{$key}'",
            E_USER_NOTICE
          )
  - public static function register_all(): void
      // Register contracts for all built-in templates
      // Called from Template_Module::init()
      // Example:
      self::register('business/card', [
          'required' => ['business'],
          'optional' => ['show_distance', 'show_badges'],
      ]);
      // ... register all templates listed in Phase 2


──────────────────────────────────────
TEMPLATE FILES — PARTIALS
──────────────────────────────────────

Implement all 9 partial templates. Each file must:
  - Begin with a docblock: @slug, @description, @args (required + optional), @version 1.0.0
  - Use $args['key'] to access data (no extract(), no globals)
  - Escape all output with the correct function for its context
  - Be entirely presentational — no database queries, no service calls

templates/partials/star-rating.php
  @args required: rating (float 0–5)
        optional: show_number (bool, default true), count (int)
  - Render 5 SVG star icons: filled / half / empty based on $rating
  - Filled: full star SVG path with class dbp-star--filled
  - Half: half star with class dbp-star--half (at x.5 values)
  - Empty: outline star with class dbp-star--empty
  - If show_number: append <span class="dbp-rating__number">4.2</span>
  - If count provided: append <span class="dbp-rating__count">(42)</span>
  - Wrap in <div class="dbp-star-rating" aria-label="Rated X out of 5 stars">

templates/partials/badge.php
  @args required: type (string: 'claimed'|'featured'|'new'|'elite'|'pending'|'spam')
        optional: label (string override)
  - Map type to default label: Claimed | Featured | New | Elite | Pending | Spam
  - Map type to CSS modifier: dbp-badge--claimed | dbp-badge--featured | etc.
  - Render: <span class="dbp-badge dbp-badge--{type}">{label}</span>

templates/partials/price-label.php
  @args required: level (int 1–4)
  - level 1 → <span class="dbp-price dbp-price--1" aria-label="Inexpensive">$</span>
  - level 2 → $$ etc.
  - levels outside 1–4: render nothing

templates/partials/avatar.php
  @args required: user_id (int)
        optional: size (int, default 40), alt (string)
  - get_avatar_url($user_id, ['size' => $size])
  - If no avatar (default mystery person): use DBP_URL . 'assets/images/avatar-placeholder.png'
  - Render: <img class="dbp-avatar" src="{url}" alt="{alt}" width="{size}" height="{size}">

templates/partials/button.php
  @args required: label (string), url (string)
        optional: variant ('primary'|'secondary'|'ghost', default 'primary'),
                  icon (string CSS class), target ('_blank'|'_self', default '_self'),
                  extra_classes (string)
  - Render: <a class="dbp-button dbp-button--{variant} {extra_classes}"
               href="{esc_url(url)}" target="{target}" rel="noopener">
               optional <span class="dbp-button__icon {icon}"></span>
               <span class="dbp-button__label">{esc_html(label)}</span>
            </a>

templates/partials/notice.php
  @args required: message (string), type ('success'|'error'|'warning'|'info')
        optional: dismissible (bool, default false)
  - Render WP admin-style notice for admin context, or custom styled div for frontend
  - Detect context: is_admin() → use <div class="notice notice-{type}">
    Otherwise: <div class="dbp-notice dbp-notice--{type}">
  - If dismissible: add is-dismissible class and × button

templates/partials/pagination.php
  @args required: total_pages (int), current_page (int)
        optional: base_url (string), query_var (string, default 'paged')
  - If total_pages <= 1: render nothing
  - Render prev link, up to 7 page number links (with ellipsis), next link
  - Use esc_url() on all hrefs
  - Active page: <span class="dbp-pagination__current">{n}</span>

templates/partials/empty-state.php
  @args required: title (string)
        optional: message (string), icon_class (string), action_label (string),
                  action_url (string)
  - Render centered: icon (if provided), title, message, optional CTA button
  - Use dbp_template('partials/button', ...) for the CTA if action provided

templates/partials/loading-skeleton.php
  @args optional: count (int, default 3), type ('card'|'list', default 'card')
  - Render {count} skeleton placeholder elements
  - type 'card': mimics business-card layout with pulsing grey blocks
  - type 'list': mimics review-item layout


──────────────────────────────────────
TEMPLATE FILES — BUSINESS
──────────────────────────────────────

templates/business/card.php
  @args required: business (array — keys: id, name, permalink, avg_rating,
                  review_count, price_level, category_name, thumbnail_url,
                  is_claimed, is_featured, created_at)
        optional: distance (float in km/miles), show_distance (bool),
                  distance_unit (string)
  - Outer: <article class="dbp-business-card" data-business-id="{id}">
  - Thumbnail: <img> with esc_attr on src/alt; fallback to placeholder
  - Business name: <h3><a href="{permalink}">{name}</a></h3>
  - Star rating: dbp_template('partials/star-rating', [rating, count])
  - Price level: dbp_template('partials/price-label', [level])
  - Category: <span class="dbp-business-card__category">{category}</span>
  - Distance: if show_distance and distance set: dbp_format_distance()
  - Badges row: loop and call dbp_template('partials/badge', ...) for each
    applicable badge (claimed, featured, new)
  - Close article

templates/business/header.php
  @args required: business (array — same as card.php plus: phone, website,
                  address, city, state, is_open)
        optional: show_claim_button (bool)
  - Photo carousel: <div class="dbp-business-header__photos" data-carousel>
      loop gallery images, fallback to placeholder
  - Heading block: name (h1), star rating partial, price label partial,
    category, address line, open/closed status badge
  - CTA buttons row: Call, Directions, Website (all via button partial),
    Claim button if show_claim_button and not claimed

templates/business/about.php
  @args required: business (array — keys: description, hours, meta (assoc array))
  - Description: <div class="dbp-business-about__description">
      wp_kses_post($args['business']['description'])
  - Attributes grid: loop over known attribute keys (wifi, parking, etc.)
    show icon + label for each that is truthy in meta array
  - Hours table: 7-row table (Mon–Sun) with open/close times
    Highlight today's row with dbp-hours__row--today CSS class
    Show "Closed" for days with no hours or closed toggle set
  - Map embed: <iframe> using Map_Service::get_embed_url()

templates/business/single.php
  @args required: business (array), reviews (array), similar_businesses (array)
        optional: review_form_visible (bool, default true)
  - get_header()
  - JSON-LD <script type="application/ld+json">: LocalBusiness schema
    Output via wp_head action registered in Template_Module::init()
  - dbp_template('business/header', ['business' => $business])
  - dbp_template('business/about',  ['business' => $business])
  - Photos grid section: up to 12 images, "See all X photos" link
  - Reviews section:
      dbp_template('reviews/list', ['reviews' => $reviews, 'business_id' => $id])
      if review_form_visible: dbp_template('reviews/form', ['business_id' => $id])
  - Similar businesses section:
      foreach similar: dbp_template('business/card', ['business' => $b])
  - get_footer()

templates/business/archive.php
  @args optional: initial_results (array), search_args (array)
  - get_header()
  - dbp_template('search/bar', [])
  - dbp_template('search/results', ['businesses' => $initial_results])
  - Output <script> block with GeoJSON in data attribute for maps.js
  - get_footer()


──────────────────────────────────────
TEMPLATE FILES — REVIEWS
──────────────────────────────────────

templates/reviews/item.php
  @args required: review (array — keys: id, user_id, rating, content, created_at,
                  helpful, not_helpful, photos, owner_response)
        optional: current_user_has_voted (bool), is_business_owner (bool)
  - dbp_template('partials/avatar', ['user_id' => $user_id])
  - Display name + Elite badge (dbp_template('partials/badge', [type:'elite']))
    if get_user_meta($user_id, 'dbp_is_elite', true)
  - dbp_template('partials/star-rating', ['rating' => $rating])
  - Relative date: dbp_time_ago($created_at)
  - Review text: truncated at 300 chars with "Read more" toggle
    data-full-text attribute holds full text; JS toggles visibility
  - Photos: up to 3 thumbnails with lightbox data attributes; "See X more" link
  - Vote buttons: Helpful ({count}) | Not Helpful ({count})
    disabled if current_user_has_voted; data-review-id for JS
  - Flag link: <button class="dbp-review__flag" data-review-id="{id}">Report</button>
  - Owner response block (if owner_response not empty):
      <div class="dbp-review__owner-response">
        dbp_template('partials/avatar', ['user_id' => owner_user_id])
        <strong>Owner</strong>, date, response text (wp_kses_post)
      </div>

templates/reviews/list.php
  @args required: reviews (array), business_id (int)
        optional: total (int), current_page (int), orderby (string)
  - Total count: <h2>X Reviews</h2>
  - Sort control tabs: Most Relevant | Newest | Highest Rated | Lowest Rated
    data-orderby attributes for JS; active class on current orderby
  - If reviews empty: dbp_template('partials/empty-state', [title, message])
  - Loop: foreach review → dbp_template('reviews/item', ['review' => $r, ...])
  - Load more button: <button class="dbp-reviews__load-more"
                              data-page="{next_page}"
                              data-business-id="{id}">
                        Load more reviews
                      </button>
    Hide button if current_page >= ceil(total / per_page)

templates/reviews/form.php
  @args required: business_id (int)
        optional: existing_review (array — pre-populate for edit mode)
  - If !is_user_logged_in():
      dbp_template('partials/notice', [type:'info', message:'Login to review…'])
      return early
  - If user has already reviewed and not in edit mode:
      show "Edit your review" notice with link
  - Star picker: 5 <button> elements with SVG stars, role="radiogroup",
    data-value="1"–"5", aria-label per star
  - <textarea> minlength="25", data-min-length="25"
    Live counter: <span class="dbp-review-form__counter">0 / 25 minimum</span>
  - Photo upload zone: drag-drop div + hidden file input
    accept="image/jpeg,image/png,image/webp", multiple, max 5
    Preview container: <div class="dbp-review-form__previews">
  - wp_nonce_field('dbp_nonce', 'nonce')
  - Hidden: <input type="hidden" name="business_id" value="{id}">
  - Submit: <button class="dbp-button dbp-button--primary dbp-review-form__submit">
              <span class="dbp-button__label">Submit Review</span>
              <span class="dbp-spinner" hidden></span>
            </button>


──────────────────────────────────────
TEMPLATE FILES — SEARCH
──────────────────────────────────────

templates/search/bar.php
  @args optional: default_query (string), default_location (string)
  - Outer: <div class="dbp-search-bar" data-nonce="{wp_create_nonce('dbp_nonce')}">
  - What field: <input type="text" class="dbp-search-bar__what"
                       placeholder="Restaurants, plumbers, dentists…"
                       value="{esc_attr(default_query)}">
  - Where field: <input type="text" class="dbp-search-bar__where"
                        placeholder="City, neighborhood, or zip"
                        value="{esc_attr(default_location)}">
  - Geolocation button: <button class="dbp-search-bar__geo" aria-label="Use my location">
      SVG location pin icon
    </button>
  - Autosuggest panel: <div class="dbp-search-bar__suggestions" hidden></div>
  - Search button: dbp_template('partials/button', [label:'Search', variant:'primary'])

templates/search/results.php
  @args optional: businesses (array), total (int), current_page (int),
                  search_args (array)
  - Results summary: <p class="dbp-results__summary">
      if total: "X results" / "No results found"
  - Filter chips bar:
      <div class="dbp-filter-chips" role="group">
        Chip: Open Now   (data-filter="open_now" data-value="1")
        Chip: $          (data-filter="price" data-value="1")
        Chip: $$         (data-filter="price" data-value="2")
        Chip: $$$        (data-filter="price" data-value="3")
        Chip: $$$$       (data-filter="price" data-value="4")
        Chip: 4★+        (data-filter="min_rating" data-value="4")
        Chip: 5km        (data-filter="radius_km" data-value="5")
        Chip: 10km       (data-filter="radius_km" data-value="10")
        Chip: 25km       (data-filter="radius_km" data-value="25")
        Chip: More Filters (opens filter drawer)
      </div>
  - Sort + toggle row:
      Sort <select class="dbp-results__sort">: Relevance | Distance |
          Highest Rated | Most Reviewed | Newest
      List/Map toggle buttons: data-view="list" / data-view="map"
  - Split-view container:
      <div class="dbp-results__split">
        <div class="dbp-results__list">
          if businesses: foreach → dbp_template('business/card', ...)
          else: dbp_template('partials/empty-state', ...)
        </div>
        <div class="dbp-results__map" id="dbp-map"
             data-geojson="{esc_attr(json_encode(geojson))}">
        </div>
      </div>
  - Pagination: dbp_template('partials/pagination', [total_pages, current_page])
  - Loading skeleton overlay: dbp_template('partials/loading-skeleton', [count:6])
    hidden by default; shown by JS while AJAX in flight


──────────────────────────────────────
TEMPLATE FILES — FORMS
──────────────────────────────────────

templates/forms/form.php
  @args required: form_name (string), form_title (string),
                  groups (array), object_id (int|null)
        optional: tabs (array of tab IDs + labels), has_tabs (bool)
  - Outer: <div class="dbp-form" data-form-name="{name}" data-object-id="{id}">
  - If has_tabs: render tab bar <ul class="dbp-form__tabs">
      foreach tab: <li><button data-tab="{id}">{label}</button></li>
  - foreach group:
      dbp_template('forms/group', ['group' => $group, 'values' => $values])
  - Save bar: <div class="dbp-form__save-bar">
      wp_nonce_field('dbp_form_nonce', 'nonce')
      dbp_template('partials/button', [label:'Save Changes', variant:'primary'])
      spinner: <span class="dbp-spinner" hidden></span>
    </div>
  - Status: <div class="dbp-form__status" aria-live="polite" aria-atomic="true"></div>

templates/forms/group.php
  @args required: group (array — keys: id, label, description, tab, fields)
                  values (array — current field values)
  - <div class="dbp-form__group" data-group-id="{id}" data-tab="{tab}">
  - <div class="dbp-form__group-header"><h2>{label}</h2><p>{description}</p></div>
  - foreach field in group: dbp_template('forms/field', [field, value])
  - </div>

templates/forms/field.php
  @args required: field (array — full field definition from Form_Base schema)
                  value (mixed — current stored value or default)
  - Outer: <div class="dbp-field dbp-field--{type}"
                id="dbp-field-{id}"
                data-field-id="{id}"
                data-required="{required ? 'true' : 'false'}"
                data-condition="{esc_attr(json_encode(condition) or '')}">
  - For HEADING type: render heading block only (no label/input structure)
  - For all other types:
      <label class="dbp-field__label" for="dbp_{id}">
        {esc_html(label)}
        if required: <span class="dbp-field__required" aria-hidden="true">*</span>
      </label>
      <div class="dbp-field__control">
        delegate to Fields_Manager to render the input HTML
        (Fields_Manager::make($type)->render($field, $value))
      </div>
      if description: <p class="dbp-field__description">{esc_html(description)}</p>
      <div class="dbp-field__error" role="alert" hidden></div>
  - </div>


──────────────────────────────────────
TEMPLATE FILES — ADMIN
──────────────────────────────────────

Note: Admin templates are resolved from plugin path only (no theme override).
They render inside WordPress admin pages and must use WP admin markup patterns.

templates/admin/dashboard.php
  @args required: stats (array — keys: total_businesses, reviews_this_week,
                  pending_claims, pending_reviews),
                  recent_activity (array of review rows)
  - <div class="wrap dbp-dashboard">
  - <h1 class="wp-heading-inline">{Plugin Name} Dashboard</h1>
  - Stats grid: 4 × <div class="dbp-stat-card">
      icon + number (esc_html) + label (esc_html)
  - Recent Activity <table class="wp-list-table widefat striped">
      columns: Reviewer | Business | Rating | Excerpt | Date | Status
      foreach recent_activity row: output escaped <tr>
  - Quick links: three admin_url() links as standard WP action buttons

templates/admin/settings.php
  @args required: form_html (string — pre-rendered by Form_Renderer)
  - <div class="wrap dbp-settings">
  - <h1>{esc_html__('Settings', 'directories-builder-pro')}</h1>
  - Echo $args['form_html'] (already escaped by renderer — safe to echo raw)
  - </div>

templates/admin/moderation.php
  @args required: reviews (array), status_counts (array), current_status (string)
  - <div class="wrap dbp-moderation">
  - <h1>Review Moderation</h1>
  - Status tabs: <ul class="subsubsub"> — All | Pending | Approved | Rejected | Spam
    each with count and active class on current_status
  - Bulk action form: <form method="post">
      wp_nonce_field('dbp_bulk_moderation')
      Bulk select + action <select> + Apply button
      <table class="wp-list-table widefat fixed striped">
        thead: checkbox | Reviewer | Business | Rating | Excerpt | Date | Status | Actions
        foreach review:
          <tr data-review-id="{id}">
            escaped cells
            row actions: Approve | Reject | Spam | Delete
            inline reject textarea (hidden by default, shown by JS on Reject click)
    </form>

templates/admin/business-edit.php
  @args required: form_html (string), post_id (int)
  - Render inside meta box callback — no wrap/h1 needed
  - Echo $args['form_html']
  - Include nonce for REST save (separate from Settings API nonce)

templates/admin/user-profile.php
  @args required: form_html (string), user_id (int)
  - <div class="wrap dbp-user-profile">
  - <h1>Edit Profile</h1>
  - Echo $args['form_html']
  - </div>


──────────────────────────────────────
MIGRATION — REPLACE EXISTING INLINE HTML
──────────────────────────────────────

After all template files are implemented, update the following existing
files to remove inline HTML and route through dbp_template() instead.
Do not leave any raw echo '<div>' or ?><html>... in non-template files.

1. public/templates/single-business.php
   REMOVE all HTML output.
   REPLACE with:
     $business = Business_Service::get_business(get_the_ID());
     $reviews  = Review_Service::get_reviews_for_business(get_the_ID(), []);
     $similar  = Business_Service::get_similar_businesses(get_the_ID());
     dbp_template('business/single', compact('business', 'reviews', 'similar'));

2. public/templates/archive-business.php
   REMOVE all HTML output.
   REPLACE with: dbp_template('business/archive', [])

3. public/partials/business-card.php
   REMOVE all HTML output.
   REPLACE with: dbp_template('business/card', ['business' => $business])
   (Callers pass $business directly; this file becomes a thin redirect.)

4. public/partials/review-item.php
   REMOVE all HTML output.
   REPLACE with: dbp_template('reviews/item', ['review' => $review])

5. modules/reviews/templates/review-list.php
   REMOVE all HTML.
   REPLACE with: dbp_template('reviews/list', compact('reviews', 'business_id', 'total'))

6. modules/reviews/templates/review-form.php
   REMOVE all HTML.
   REPLACE with: dbp_template('reviews/form', compact('business_id'))

7. modules/search/templates/search-bar.php
   REMOVE all HTML.
   REPLACE with: dbp_template('search/bar', [])

8. modules/search/templates/search-results.php
   REMOVE all HTML.
   REPLACE with: dbp_template('search/results', compact('businesses', 'total'))

9. modules/business/templates/business-header.php
   REMOVE. Logic moved to templates/business/header.php.
   Delete file and remove include from single-business.php.

10. modules/business/templates/business-about.php
    REMOVE. Logic moved to templates/business/about.php.
    Delete file and remove include.

11. admin/pages/dashboard.php
    REMOVE all HTML.
    REPLACE with:
      $stats = [ /* query from DB */ ];
      $recent_activity = [ /* query from DB */ ];
      dbp_template('admin/dashboard', compact('stats', 'recent_activity'));

12. admin/pages/settings.php
    REMOVE Settings API HTML form.
    REPLACE with:
      $form = Form_Manager::get_instance()->get('plugin_settings');
      ob_start(); $form->render_form(); $form_html = ob_get_clean();
      dbp_template('admin/settings', compact('form_html'));

13. admin/views/business-edit.php
    REMOVE meta box HTML.
    REPLACE with:
      $form = Form_Manager::get_instance()->get('business_settings');
      ob_start(); $form->render_form($post->ID); $form_html = ob_get_clean();
      dbp_template('admin/business-edit', ['form_html' => $form_html, 'post_id' => $post->ID]);

14. admin/views/review-moderation.php
    REMOVE WP_List_Table HTML subclass.
    REPLACE with:
      $reviews = Review_Repository::find_pending();
      $status_counts = [ /* count per status */ ];
      dbp_template('admin/moderation', compact('reviews', 'status_counts', 'current_status'));

Also delete the now-empty module template folders:
  - modules/reviews/templates/
  - modules/search/templates/
  - modules/business/templates/
  - public/templates/
  - public/partials/
(These are superseded by the root /templates/ directory.)


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE 4 — QUALITY & COMPLETION CHECKS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

After all files are written and migration is complete, verify:

1. RESOLUTION CORRECTNESS
   □ A file at {child-theme}/directories-builder-pro/business/card.php
     is served instead of the plugin default when present
   □ A file at {parent-theme}/directories-builder-pro/business/card.php
     is served when no child-theme override exists
   □ Admin slugs (admin/*) always resolve to plugin path regardless of theme
   □ An invalid slug (containing '..') returns null and triggers_error

2. CACHE BEHAVIOUR
   □ A second call to Template_Loader::locate('business/card') returns
     the cached path without calling file_exists() again
   □ Template_Loader::flush_cache() correctly resets the static array

3. HOOK COVERAGE
   □ dbp/template/before fires for every render() call
   □ dbp/template/before/business/card fires only for business/card renders
   □ dbp/template/missing fires when a slug resolves to null
   □ A listener on dbp/template/args can successfully modify $args
     before the template include (verify value is changed inside template)
   □ A listener on dbp/template/locate can substitute a different file path

4. CONTRACT VALIDATION (WP_DEBUG = true)
   □ Calling dbp_template('business/card', []) without required 'business' key
     triggers E_USER_NOTICE in debug mode
   □ The same call in production (WP_DEBUG = false) silently proceeds

5. SECURITY AUDIT
   □ Slug 'business/../../../wp-config' → rejected, returns null
   □ Slug with uppercase or spaces → rejected or normalised
   □ No user-supplied string reaches file_exists() or include without passing
     through Template_Loader::locate() validation
   □ Every template uses esc_html(), esc_attr(), esc_url(), wp_kses_post()
     as appropriate — zero unescaped output
   □ Admin templates never expose editable content to frontend users

6. REGRESSION
   □ All existing plugin pages render correctly after migration:
       Single business page   (/business/{slug})
       Business archive page  (/business/)
       Admin dashboard        (/wp-admin/admin.php?page=dbp-dashboard)
       Admin settings         (/wp-admin/admin.php?page=dbp-settings)
       Review moderation      (/wp-admin/admin.php?page=dbp-moderation)
       Business edit meta box (post edit screen for dbp_business)
   □ Review form submits correctly after HTML moved to new template
   □ Search bar autosuggest still fires after HTML moved to new template
   □ Form Module renders via dbp_template('forms/form', ...) without errors

7. CODING STANDARDS
   □ declare(strict_types=1) in every PHP file
   □ Full DirectoriesBuilderPro\ namespacing throughout
   □ No inline SQL anywhere in template files
   □ No database queries inside template files — all data passed via $args
   □ No use of extract() — all templates access data via $args['key']


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CONSTRAINTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

- No React, no Vue, no npm build step. The Template Module is pure PHP
  with vanilla JS only where needed for UI behaviour.
- Templates must NEVER query the database directly. All data is injected
  via $args. If a template needs data that is not in $args, fix the
  caller — do not add a query to the template.
- No extract() calls anywhere. Use $args['key'] consistently.
  This prevents variable collisions and makes data contracts explicit.
- Admin templates are plugin-internal; mark them clearly with a docblock
  comment: @internal Not intended for theme override.
- The /templates/ root directory is the plugin's public template API.
  Treat it like a public API: changes to $args keys in any frontend or
  partial template are breaking changes and must be versioned.
- Do NOT generate placeholder or stub files. Every file must contain
  complete, working, production-ready code.
```
