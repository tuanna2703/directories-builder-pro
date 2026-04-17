# Folder Structure
## Directories Builder Pro — WordPress Plugin

> Modular architecture inspired by Elementor. Every module is self-contained with its own controller, model, AJAX handler, and templates.

---

```
wp-content/plugins/directories-builder-pro/
│
├── directories-builder-pro.php          # Bootstrap: constants, autoloader, Plugin singleton
├── uninstall.php                        # Drop tables and options on uninstall
├── README.md                            # Developer documentation
│
│
├── /core/                               # Abstract base system (Elementor-inspired)
│   │
│   ├── base/
│   │   ├── module-base.php              # Abstract Module — enforces get_name(), init()
│   │   ├── controller-base.php         # Abstract Controller — REST route registration helpers
│   │   └── model-base.php              # Abstract Model — $wpdb CRUD helpers
│   │
│   ├── managers/
│   │   ├── module-manager.php          # Auto-discovers and loads all /modules/
│   │   ├── asset-manager.php           # Conditional CSS/JS enqueueing + JS localization
│   │   └── ajax-manager.php            # Central registry for wp_ajax_* handlers
│   │
│   ├── database/
│   │   ├── schema.php                  # SQL definitions for all custom tables
│   │   └── migrations.php             # dbDelta() runner; version-tracked upgrades
│   │
│   └── helpers/
│       ├── functions.php               # dbp_get_star_html(), dbp_format_distance(), etc.
│       └── geo-helper.php             # Haversine formula, bounding-box calculator
│
│
├── /includes/                          # Core runtime services
│   │
│   ├── plugin.php                      # Singleton Plugin class — wires all managers + modules
│   ├── autoloader.php                  # PSR-4 autoloader → DirectoriesBuilderPro\ namespace
│   │
│   ├── post-types/
│   │   └── business.php               # Registers dbp_business CPT + dbp_category taxonomy
│   │
│   ├── services/
│   │   ├── business-service.php        # Business CRUD, avg rating, featured logic
│   │   ├── review-service.php          # Review CRUD, trust scoring, approval workflow
│   │   ├── search-service.php          # Full-text + geospatial search, autocomplete
│   │   └── user-service.php            # User profiles, points, badges
│   │
│   └── repositories/
│       ├── business-repository.php     # All $wpdb queries for dbp_businesses + dbp_business_meta
│       └── review-repository.php       # All $wpdb queries for dbp_reviews + dbp_review_votes
│
│
├── /modules/                           # Feature modules — each is fully self-contained
│   │
│   ├── reviews/
│   │   ├── module.php                  # Entry class; registers controller + AJAX
│   │   ├── controllers/
│   │   │   └── review-controller.php   # REST: GET/POST/PUT/DELETE /reviews, /reviews/{id}/vote|flag
│   │   ├── models/
│   │   │   └── review.php              # Maps to dbp_reviews table
│   │   ├── ajax/
│   │   │   └── review-ajax.php         # dbp_submit_review, dbp_vote_review, dbp_flag_review
│   │   └── templates/
│   │       ├── review-list.php         # Paginated list, sort controls, load more
│   │       └── review-form.php         # Star picker, textarea, photo upload zone
│   │
│   ├── business/
│   │   ├── module.php                  # Entry class; registers controller + AJAX
│   │   ├── controllers/
│   │   │   └── business-controller.php # REST: GET/POST/PUT /businesses, /businesses/{id}
│   │   ├── models/
│   │   │   └── business.php            # Maps to dbp_businesses + dbp_business_meta
│   │   ├── ajax/
│   │   │   └── business-ajax.php       # dbp_get_business_hours, dbp_update_business_meta
│   │   └── templates/
│   │       ├── business-header.php     # Hero: photo carousel, name, rating, CTA buttons
│   │       └── business-about.php      # Description, attributes, hours table, map embed
│   │
│   ├── search/
│   │   ├── module.php                  # Entry class; registers controller + AJAX
│   │   ├── controllers/
│   │   │   └── search-controller.php   # REST: GET /search, GET /autocomplete
│   │   ├── ajax/
│   │   │   └── search-ajax.php         # dbp_search (HTML partial), dbp_autocomplete (JSON)
│   │   └── templates/
│   │       ├── search-bar.php          # Dual-field bar, autosuggest dropdown, Near Me button
│   │       └── search-results.php      # Results count, filter chips, list/map toggle, pagination
│   │
│   ├── maps/
│   │   ├── module.php                  # Entry class; registers Maps API key setting + asset
│   │   └── services/
│   │       └── map-service.php         # Embed URL, GeoJSON builder, static map, directions URL
│   │
│   ├── template/
│   │   ├── template-module.php         # Entry: dbp_template() global, CPT override, shortcodes
│   │   ├── loader/
│   │   │   └── template-loader.php     # 3-level path resolution, slug validation, static cache
│   │   ├── renderer/
│   │   │   └── template-renderer.php   # Output buffering, hook lifecycle, template inclusion
│   │   └── contracts/
│   │       └── contract-validator.php  # Dev-mode required/optional arg validation (27 contracts)
│   │
│   └── claims/
│       ├── module.php                  # Entry class; registers controller + AJAX
│       ├── controllers/
│       │   └── claim-controller.php    # REST: POST /claims, GET/PUT /claims/{id}/approve|reject
│       └── ajax/
│           └── claim-ajax.php          # dbp_submit_claim, dbp_approve_claim, dbp_reject_claim
│
│
├── /assets/                            # Compiled frontend and admin assets
│   │
│   ├── css/
│   │   ├── frontend.css                # CSS vars, card grid, stars, filters, map, review form
│   │   └── admin.css                   # Dashboard cards, moderation table, settings, map picker
│   │
│   ├── js/
│   │   ├── frontend.js                 # Entry: boots all frontend modules, receives dbpData
│   │   ├── admin.js                    # Entry: map picker, moderation AJAX, settings tabs
│   │   └── modules/
│   │       ├── reviews.js              # Star picker, form submit, photo upload, voting, load more
│   │       ├── search.js               # Autocomplete, filter chips, filter drawer, infinite scroll
│   │       └── maps.js                 # Google Maps init, markers, clustering, map↔list sync
│   │
│   └── lib/                            # Vendored third-party JS/CSS (e.g., lightbox, cluster)
│
│
├── /admin/                             # WordPress admin UI
│   │
│   ├── pages/
│   │   ├── dashboard.php               # Stats cards, recent activity feed, quick links
│   │   └── settings.php                # Maps key, moderation mode, search defaults, distance unit
│   │
│   └── views/
│       ├── business-edit.php           # Meta boxes: location, contact, details, status
│       └── review-moderation.php       # WP_List_Table: filter tabs, bulk actions, inline reject
│
│
├── /templates/                         # Centralized template root (Template Module)
│   │
│   ├── partials/                       # Shared micro-components
│   │   ├── star-rating.php            # SVG star display (filled/half/empty)
│   │   ├── badge.php                  # Type-based badge (claimed/featured/new/elite)
│   │   ├── price-label.php            # Price level indicator ($–$$$$)
│   │   ├── avatar.php                 # User avatar with fallback
│   │   ├── button.php                 # CTA button/link with variants
│   │   ├── notice.php                 # Alert/notice (admin + frontend)
│   │   ├── pagination.php             # Page navigation with ellipsis
│   │   ├── empty-state.php            # No-results placeholder with CTA
│   │   └── loading-skeleton.php       # Pulsing placeholder (card/list)
│   │
│   ├── business/                       # Business templates (theme-overridable)
│   │   ├── card.php                   # Business card component
│   │   ├── header.php                 # Hero section with carousel + CTAs
│   │   ├── about.php                  # Description, attributes, hours, map
│   │   ├── single.php                 # Full single business page
│   │   └── archive.php                # Archive/search page
│   │
│   ├── reviews/                        # Review templates (theme-overridable)
│   │   ├── item.php                   # Single review display
│   │   ├── list.php                   # Paginated review list with sort
│   │   └── form.php                   # Review submission form
│   │
│   ├── search/                         # Search templates (theme-overridable)
│   │   ├── bar.php                    # Dual-field search with autosuggest
│   │   └── results.php                # Results grid, filters, map toggle
│   │
│   ├── forms/                          # Form Engine templates (theme-overridable)
│   │   ├── form.php                   # Form shell with tabs + save
│   │   ├── group.php                  # Field group with header
│   │   └── field.php                  # Field wrapper (delegates to type)
│   │
│   └── admin/                          # Admin templates (NOT theme-overridable)
│       ├── dashboard.php              # Stats cards, activity, quick links
│       ├── settings.php               # Settings page wrapper
│       ├── moderation.php             # Review moderation with WP_List_Table
│       ├── business-edit.php          # Business editor metabox
│       └── user-profile.php           # User profile settings
│
│
├── /public/                            # Legacy template layer (redirect wrappers)
│   │
│   ├── templates/
│   │   ├── single-business.php         # @deprecated → templates/business/single.php
│   │   └── archive-business.php        # @deprecated → templates/business/archive.php
│   │
│   └── partials/
│       ├── business-card.php           # @deprecated → templates/business/card.php
│       └── review-item.php             # @deprecated → templates/reviews/item.php
│
│
└── /languages/
    └── directories-builder-pro.pot     # Translation template
```

---

## Responsibilities at a Glance

| Layer | Purpose |
|---|---|
| `/core/base/` | Abstract contracts every module, controller, and model must implement |
| `/core/managers/` | Central wiring — loads modules, enqueues assets, registers AJAX |
| `/core/database/` | Schema definitions and versioned migration runner |
| `/core/helpers/` | Stateless utility functions available globally |
| `/includes/plugin.php` | Singleton that boots the entire plugin on `plugins_loaded` |
| `/includes/services/` | Business logic — trust scoring, search, rating calculation |
| `/includes/repositories/` | All database queries, isolated from business logic |
| `/modules/*/` | Self-contained feature domains: each owns its REST, AJAX, and templates |
| `/modules/template/` | Centralized rendering: `dbp_template()` API, path resolution, contracts |
| `/templates/` | All plugin templates: partials, business, reviews, search, forms, admin |
| `/admin/` | WordPress admin pages and meta box views |
| `/public/` | Legacy template redirect wrappers (backward compatibility) |
| `/assets/` | Compiled CSS/JS served to visitors and admins |

---

## WordPress Hook Integration Points

| Hook | File | Purpose |
|---|---|---|
| `plugins_loaded` | `directories-builder-pro.php` | Boot Plugin singleton |
| `init` | `includes/plugin.php` | Register CPT, taxonomies, REST routes |
| `wp_enqueue_scripts` | `core/managers/asset-manager.php` | Enqueue frontend assets |
| `admin_enqueue_scripts` | `core/managers/asset-manager.php` | Enqueue admin assets |
| `admin_menu` | `includes/plugin.php` | Register admin dashboard pages |
| `template_include` | `modules/template/template-module.php` | Override templates for dbp_business CPT |
| `dbp/template/before` | `modules/template/renderer/` | Action: fires before template include |
| `dbp/template/after` | `modules/template/renderer/` | Action: fires after template include |
| `dbp/template/args` | `modules/template/renderer/` | Filter: modify template args |
| `dbp/template/locate` | `modules/template/loader/` | Filter: override resolved path |
| `the_content` | `public/templates/` | Inject rendered output into content area |
| `register_activation_hook` | `directories-builder-pro.php` | Run DB migrations |
| `register_deactivation_hook` | `directories-builder-pro.php` | Flush rewrite rules |
