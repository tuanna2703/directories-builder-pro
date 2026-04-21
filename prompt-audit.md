# Agent Prompt — Architectural Audit & Completion
## Directories Builder Pro

> Paste this prompt into Claude Code, Cursor, Aider, or any agentic coding tool.

---

You are a senior WordPress plugin architect, system designer, and codebase auditor.

Your task is to perform a **complete architectural audit** of the plugin
**Directories Builder Pro**, then **fix every gap** you find.

Read every file in the project before writing a single line. Pay particular
attention to the reference documents in the project root:

- `prd.md` — product requirements, database schema, REST API table, security rules
- `structure.md` — canonical folder layout and layer responsibilities
- `prompt.md` — full original build specification for the core plugin
- `prompt-template-module.md` — full specification for the Template Module

These documents are the **source of truth**. Any file in the codebase that
contradicts them must be corrected. Any component they specify that is absent
from the codebase must be created.

---

## ⚙️ ARCHITECTURAL RULES — NON-NEGOTIABLE

Every finding, fix, and new file must conform to these rules. Flag any
existing code that violates them and correct it as part of this task.

### Rule 1 — Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| PHP Classes | `PascalCase`, no prefix | `Module_Manager`, `Review_Controller` |
| PHP functions (global helpers) | `dbp_` prefix, `snake_case` | `dbp_get_star_html()` |
| WordPress hooks | `dbp/` namespace, `/` separator | `dbp/review/submitted` |
| CSS classes | `dbp-` prefix, `kebab-case` | `dbp-business-card` |
| Database tables | `{prefix}dbp_` | `wp_dbp_reviews` |
| Constants | `DBP_` prefix, `UPPER_SNAKE` | `DBP_VERSION`, `DBP_PATH` |
| JS variables/objects | `dbp` prefix, `camelCase` | `dbpData`, `dbpSearch` |

Reject and correct any file that deviates from these conventions.

### Rule 2 — Separation of Concerns

Every class must belong to exactly one layer and must not assume
responsibilities from another layer:

| Layer | Owns | Must NOT |
|---|---|---|
| **Module** (`modules/*/module.php`) | Wires its own components together; registers itself with Managers | Execute logic directly; register global hooks independently |
| **Controller** (`controllers/`) | Maps HTTP requests to service calls; returns responses | Query the database; contain business logic |
| **Service** (`includes/services/` or `modules/*/services/`) | Business logic, calculations, workflows | Query `$wpdb` directly; render HTML |
| **Repository** (`includes/repositories/`) | All `$wpdb` queries for one domain | Contain business logic; be called from templates |
| **Model** (`modules/*/models/`) | Data shape and table mapping | Perform queries; contain rendering logic |
| **Template** (`templates/`) | Render HTML from `$args`; escape output | Call services or repositories; run queries |
| **Manager** (`core/managers/`) | Orchestrate: initialize, register hooks, control execution flow | Implement feature logic |

Any violation of this table is an architectural defect — flag it and fix it.

### Rule 3 — Manager-Driven Execution

- **All** hook registrations (`add_action`, `add_filter`, `register_rest_route`,
  `add_menu_page`) must be triggered by a Manager or delegated through one.
- Modules **register themselves** with a Manager; they do not self-execute.
- A module's `init()` method sets up its own internal wiring (controllers,
  services, AJAX handlers) — it does NOT add global WordPress hooks directly
  unless that hook registration is itself orchestrated by a Manager calling
  `init()`.

### Rule 4 — Template Rendering

- **All** HTML output anywhere in the plugin must go through `Template_Manager::render()`.
- No raw `echo`, `?>...<?php`, or `include` of HTML-producing files outside
  the `/templates/` directory.
- Template files must never query the database or call services directly.
  All data is injected via `$args`.
- The helper `dbp_render()` (defined in `core/helpers/functions.php`) is the
  only permitted shorthand for template files calling partials. All class-based
  code uses `$this->render_template()`.

### Rule 5 — No Stubs

Every file produced by this task must be complete and production-ready.
No `// TODO` placeholders, no empty method bodies, no `return null` stubs.

---

## 🔍 PHASE 1 — FULL CODEBASE SCAN & INVENTORY

Before doing anything else, scan every file in the plugin and produce an
inventory report. Output this report as `AUDIT-REPORT.md` in the project root.

The report must contain the following sections:

---

### Section 1.1 — Module Inventory Table

For every directory under `modules/`, produce a row:

```
| Module     | module.php | Controllers | Services | Models | Repositories | AJAX | Templates | Notes |
|------------|------------|-------------|----------|--------|--------------|------|-----------|-------|
| business   | ✅ exists  | ✅ 1 file   | ❌ none  | ✅ 1   | ❌ none      | ✅   | ✅ 2 files | service lives in /includes/services/ |
| reviews    | …          | …           | …        | …      | …            | …    | …         | …     |
| search     | …          | …           | …        | …      | …            | …    | …         | …     |
| maps       | …          | …           | …        | …      | …            | …    | …         | …     |
| claims     | …          | …           | …        | …      | …            | …    | …         | …     |
| template   | …          | …           | …        | …      | …            | …    | …         | …     |
| form       | …          | …           | …        | …      | …            | …    | …         | …     |
```

---

### Section 1.2 — Manager Inventory Table

For every file under `core/managers/`, produce a row:

```
| Manager            | File exists | Responsibilities match spec | Violations found |
|--------------------|-------------|----------------------------|------------------|
| Module_Manager     | ✅          | ✅                         | none             |
| Asset_Manager      | ✅          | ⚠️ partial                 | enqueues CSS before Template_Manager is ready |
| Ajax_Manager       | …           | …                          | …                |
| Template_Manager   | …           | …                          | …                |
| Form_Manager       | …           | …                          | …                |
```

---

### Section 1.3 — Naming Convention Violations

List every file where a class name, function name, hook name, or constant
deviates from Rule 1:

```
| File | Type | Found | Expected | Severity |
|------|------|-------|----------|----------|
| modules/reviews/ajax/review-ajax.php | Hook | wp_ajax_submit_review | wp_ajax_dbp_submit_review | High |
| … | … | … | … | … |
```

---

### Section 1.4 — Separation of Concerns Violations

List every location where a class performs work outside its assigned layer:

```
| File | Violation | Rule Broken | Fix Required |
|------|-----------|-------------|--------------|
| modules/reviews/controllers/review-controller.php | Direct $wpdb query on line 42 | Controller queries DB directly | Move query to Review_Repository |
| … | … | … | … |
```

---

### Section 1.5 — Manager Execution Violations

List every `add_action`, `add_filter`, `register_rest_route`, or
`add_menu_page` call that is NOT triggered through a Manager:

```
| File | Line | Hook | Violation | Fix |
|------|------|------|-----------|-----|
| modules/search/module.php | 18 | add_action('wp_enqueue_scripts', …) | Module registers hook directly | Route through Asset_Manager |
| … | … | … | … | … |
```

---

### Section 1.6 — Template Rendering Violations

List every `echo`, inline HTML block, `include`, or `require` of an
HTML-producing file that bypasses `Template_Manager::render()`:

```
| File | Line | Violation | Fix |
|------|------|-----------|-----|
| modules/business/controllers/business-controller.php | 67 | echo '<div class="dbp-business">' | Move to template file, call via render_template() |
| … | … | … | … |
```

---

### Section 1.7 — Missing Components

List every component that is required by `prd.md`, `structure.md`, or the
module specifications but does not exist as a file:

```
| Required Component | Required By | Missing File | Priority |
|--------------------|-------------|--------------|----------|
| Claim Repository | prd.md §7, claims module | modules/claims/repositories/claim-repository.php | High |
| Maps Controller | structure.md | modules/maps/controllers/map-controller.php | Medium |
| … | … | … | … |
```

---

### Section 1.8 — Template Module Completeness Check

For the Template Module specifically, verify every component listed in
`prompt-template-module.md` exists and is fully implemented:

```
| Component | File | Exists | Fully Implemented | Issues |
|-----------|------|--------|-------------------|--------|
| Template_Loader | modules/template/loader/template-loader.php | ✅ | ⚠️ | cache flush method missing |
| Template_Renderer | modules/template/renderer/template-renderer.php | ✅ | ✅ | none |
| Contract_Validator | modules/template/contracts/contract-validator.php | ❌ | — | file absent |
| Template_Manager | core/managers/template-manager.php | … | … | … |
| Storage adapters | modules/form/storage/*.php | … | … | … |
| templates/business/card.php | templates/business/card.php | … | … | … |
| (all 30+ template files) | … | … | … | … |
```

---

## 📐 PHASE 2 — ARCHITECTURE BLUEPRINT

After completing the inventory, produce a **definitive architecture blueprint**
as `ARCHITECTURE.md` in the project root.

This document is the single authoritative reference for how the plugin is
structured. It supersedes any inconsistencies found during the Phase 1 audit.

The blueprint must cover the following sections:

---

### Section 2.1 — Layer Diagram

Produce an ASCII diagram showing every layer, what it contains, and the
permitted call directions between layers:

```
┌─────────────────────────────────────────────────────────┐
│                    WordPress Core                        │
└──────────────────────┬──────────────────────────────────┘
                       │ hooks
┌──────────────────────▼──────────────────────────────────┐
│                 Plugin Singleton                         │
│            (includes/plugin.php)                        │
└──────┬───────────┬───────────┬──────────────────────────┘
       │           │           │
  ┌────▼───┐  ┌───▼────┐  ┌───▼────────┐
  │Module  │  │Asset   │  │Template    │  … other Managers
  │Manager │  │Manager │  │Manager     │
  └────┬───┘  └────────┘  └────────────┘
       │ init()
  ┌────▼──────────────────────┐
  │       Modules             │
  │  business / reviews /     │
  │  search / maps / claims / │
  │  form / template          │
  └────┬──────────────────────┘
       │ calls
  ┌────▼──────┐    ┌────────────┐    ┌─────────────┐
  │Controllers│───▶│  Services  │───▶│Repositories │
  └───────────┘    └────────────┘    └─────────────┘
                         │
                   ┌─────▼──────┐
                   │  Templates │  (via Template_Manager)
                   └────────────┘
```

---

### Section 2.2 — Module Specification Table

For every module, define the **complete and authoritative** list of files
it must contain, with a one-line description of each file's responsibility:

```
Module: reviews
├── module.php               — Wires Review_Controller, Review_Ajax; registers with managers
├── controllers/
│   └── review-controller.php — REST CRUD for /reviews; delegates to Review_Service
├── models/
│   └── review.php           — Data shape; maps to dbp_reviews table
├── ajax/
│   └── review-ajax.php      — AJAX: dbp_submit_review, dbp_vote_review, dbp_flag_review
└── (no module-level service — Review_Service lives in includes/services/)

Module: business
…

Module: search
…

Module: maps
├── module.php               — Wires Map_Controller, Map_Service; registers asset with Asset_Manager
├── controllers/
│   └── map-controller.php   — REST: GET /map/config, GET /map/geojson
└── services/
    └── map-service.php      — Embed URL, GeoJSON builder, static map, directions URL

Module: claims
├── module.php               — Wires Claim_Controller, Claim_Ajax
├── controllers/
│   └── claim-controller.php — REST CRUD for /claims, approve, reject
├── models/
│   └── claim.php            — Data shape; maps to dbp_claims table
├── repositories/
│   └── claim-repository.php — All $wpdb queries for dbp_claims table
└── ajax/
    └── claim-ajax.php       — dbp_submit_claim, dbp_approve_claim, dbp_reject_claim

Module: template
├── module.php               — Registers Template_Manager with Plugin singleton
├── loader/
│   └── template-loader.php  — Path resolution: child theme → parent theme → plugin
├── renderer/
│   └── template-renderer.php — ob_start wrapper, hook firing, $args injection
├── contracts/
│   └── contract-validator.php — WP_DEBUG-only $args key validation
└── (no module-level templates — all template files live under /templates/)

Module: form
├── module.php               — Registers Form_Manager, built-in forms, Form_Controller
├── controllers/
│   └── form-controller.php  — REST: GET schema, POST save
├── ajax/
│   └── form-ajax.php        — dbp_save_form AJAX fallback
├── renderer/
│   └── form-renderer.php    — Renders form HTML from schema
├── storage/
│   ├── storage-interface.php
│   ├── options-storage.php
│   ├── post-meta-storage.php
│   └── user-meta-storage.php
├── forms/
│   ├── plugin-settings-form.php
│   ├── business-settings-form.php
│   └── user-profile-form.php
└── assets/
    ├── form-engine.js
    └── form-engine.css
```

---

### Section 2.3 — Manager Specification Table

For every Manager in `core/managers/`, define its complete responsibility,
every WordPress hook it registers, and every module/class it orchestrates:

```
Manager: Module_Manager
  Responsibility: Instantiate all modules; inject dependencies; call init()
  Hooks registered: none (called by Plugin singleton on plugins_loaded)
  Orchestrates: All Module_Base subclasses

Manager: Asset_Manager
  Responsibility: Enqueue all CSS/JS; localize data to JS
  Hooks registered:
    - wp_enqueue_scripts → enqueue_frontend()
    - admin_enqueue_scripts → enqueue_admin()
  Orchestrates: asset registration for all modules

Manager: Ajax_Manager
  Responsibility: Central registry for all wp_ajax_* handlers
  Hooks registered: wp_ajax_{action} and wp_ajax_nopriv_{action} per registration
  Orchestrates: All AJAX handler classes

Manager: Template_Manager
  Responsibility: Resolve template slugs to files; render templates via Template_Renderer
  Hooks registered: none (called by modules and controllers)
  Orchestrates: Template_Loader, Template_Renderer, Contract_Validator

Manager: Form_Manager
  Responsibility: Register and retrieve Form_Base subclasses
  Hooks registered: none (called during module init)
  Orchestrates: All Form_Base subclasses
```

---

### Section 2.4 — Execution Flow for Key Features

Trace the full call chain for each of these five features, from WordPress
hook to HTML output:

```
Feature: Render a business detail page
WordPress hook: template_include
→ Plugin singleton (template_include filter)
→ Template_Manager::render('business/single', $args)
→ Template_Loader::locate('business/single')
→ Template_Renderer::render() (ob_start, include, ob_get_clean)
→ templates/business/single.php ($args injected)
  → dbp_render('business/header', ['business' => $b])
  → dbp_render('reviews/list', ['reviews' => $r, 'business_id' => $id])
  → dbp_render('partials/star-rating', ['rating' => $avg])
→ HTML output

Feature: Submit a review (AJAX)
Feature: Perform a search (REST)
Feature: Save plugin settings (Form Module REST)
Feature: Render search results page (archive template)
```

---

### Section 2.5 — Hook Registry

A complete table of every action and filter the plugin registers,
which class registers it, and which Manager orchestrates that registration:

```
| Hook | Type | Class | Method | Orchestrated by |
|------|------|-------|--------|-----------------|
| plugins_loaded | action | Plugin | instance() | Bootstrap file |
| init | action | Plugin | on_init() | Plugin singleton |
| wp_enqueue_scripts | action | Asset_Manager | enqueue_frontend() | Asset_Manager |
| admin_enqueue_scripts | action | Asset_Manager | enqueue_admin() | Asset_Manager |
| rest_api_init | action | [each controller] | register_routes() | Module_Manager via module init() |
| wp_ajax_dbp_submit_review | action | Review_Ajax | handle_submit() | Ajax_Manager |
| wp_ajax_dbp_vote_review | action | Review_Ajax | handle_vote() | Ajax_Manager |
| template_include | filter | Plugin | override_cpt_templates() | Plugin singleton |
| dbp/template/paths | filter | Template_Loader | — | Template_Manager |
| dbp/review/submitted | action | Review_Service | — | fired by service |
| … (all hooks) | … | … | … | … |
```

---

## 🏗️ PHASE 3 — FIX ALL VIOLATIONS

Working through the violations and gaps identified in Phase 1, fix every
issue. Apply fixes in dependency order so later fixes can rely on earlier ones.

Follow this execution order:

```
Step 1:  Fix all naming convention violations (Rule 1)
Step 2:  Fix all separation of concerns violations (Rule 2)
Step 3:  Fix all manager execution violations (Rule 3)
Step 4:  Fix all template rendering violations (Rule 4)
Step 5:  Create all missing Manager files (Section 1.2 gaps)
Step 6:  Create all missing Module components (Section 1.7 gaps)
Step 7:  Complete the Template Module (Section 1.8 gaps)
Step 8:  Verify and complete all template files under /templates/
Step 9:  Update includes/autoloader.php with all new class paths
Step 10: Update includes/plugin.php to wire all new components
```

For each fix or new file, follow the detailed specifications below.

---

### STEP 1 — Fix Naming Convention Violations

For every violation in Section 1.3 of the audit report:
- Rename the class, function, constant, or hook to conform to Rule 1
- Update every reference to the old name across the entire codebase
- Do not break any existing working code while renaming

---

### STEP 2 — Fix Separation of Concerns Violations

For every violation in Section 1.4:

**Controller queries DB directly:**
- Extract the query to the appropriate Repository
- Update the Controller to call the Repository method

**Service renders HTML:**
- Extract the HTML to a template file under `/templates/`
- Replace the inline rendering with `$this->render_template()` or `dbp_render()`

**Template calls a service or queries DB:**
- Move the data retrieval to the calling Controller or Service
- Pass the result as an `$args` key to the template

---

### STEP 3 — Fix Manager Execution Violations

For every violation in Section 1.5:

**Module registers `add_action('wp_enqueue_scripts', ...)` directly:**
- Remove the direct `add_action` call from the module
- Register the asset with `Asset_Manager` instead:
  ```php
  // In module's init(), store asset definition for Asset_Manager to pick up:
  $this->asset_manager->register_frontend_script(
      'dbp-maps',
      DBP_URL . 'assets/js/modules/maps.js',
      ['dbp-frontend']
  );
  ```

**Module calls `register_rest_route()` directly:**
- The REST registration must happen inside a Controller's `register_routes()` method
- The Controller's `register_routes()` must be called from the module's `init()`
- The module's `init()` must be triggered by `Module_Manager`
- Module_Manager calls all `init()` methods inside the `rest_api_init` action
  registered by the Plugin singleton

**Module calls `add_menu_page()` directly:**
- Move to `Plugin::register_admin_pages()` or a dedicated Admin_Manager

---

### STEP 4 — Fix Template Rendering Violations

For every violation in Section 1.6:
- Create the appropriate template file under `/templates/` if it does not exist
- Move the HTML from the violating file into the template file
- Replace the inline HTML with the correct call pattern (see Rule 4 above)
- Ensure the template file has a complete `@args` docblock

---

### STEP 5 — Complete the Manager System

Verify every Manager in Section 2.3 of the blueprint exists as a complete file.
For any that are missing or incomplete, implement them in full.

**`core/managers/module-manager.php`** must:
- Hold a `private array $modules = []`
- Have `register_modules(array $classes): void` that instantiates each module,
  injects dependencies (Template_Manager, Form_Manager, Ajax_Manager), then
  calls `$module->init()`
- Have `get_module(string $name): ?Module_Base`
- Have `set_template_manager(Template_Manager $tm): void`
- Have `set_ajax_manager(Ajax_Manager $am): void`

**`core/managers/asset-manager.php`** must:
- Register and enqueue `frontend.css`, `frontend.js` on `wp_enqueue_scripts`
- Register and enqueue `admin.css`, `admin.js` on `admin_enqueue_scripts`
- Localize `dbpData` to `frontend.js`: `{ ajaxurl, nonce, pluginUrl, mapsKey, distanceUnit }`
- Enqueue `wp-color-picker` and `wp-media-utils` on DBP admin pages only
- Enqueue Google Maps JS API (with API key) only on pages that render a map
- Accept module-specific asset registrations via:
  `register_frontend_script(string $handle, string $src, array $deps): void`
  `register_admin_script(string $handle, string $src, array $deps): void`

**`core/managers/ajax-manager.php`** must:
- Have `register(string $action, callable $callback, bool $nopriv = false): void`
- Register `wp_ajax_{action}` and optionally `wp_ajax_nopriv_{action}`
- Store all registrations in `private array $handlers = []`
- Be callable before `init` hook fires (registrations queue up and flush on `init`)

**`core/managers/template-manager.php`** must match the complete spec in
`prompt-template-manager.md` exactly. Verify against every requirement in
that document. Add any missing method, property, or behaviour.

**`core/managers/form-manager.php`** must:
- Have `register(Form_Base $form): void` — calls `register_fields()` on the form
- Have `get(string $name): ?Form_Base`
- Have `get_all(): array`
- Fire `do_action('dbp/form/init', $this)` after all built-in forms are registered
  so third-party code can add forms

---

### STEP 6 — Create All Missing Module Components

For every gap in Section 1.7, create the missing file in full.
Key gaps that are commonly missing in this architecture:

**`modules/claims/models/claim.php`** — must:
- Extend `Model_Base`
- Implement `get_table_name(): string` → `$wpdb->prefix . 'dbp_claims'`
- Declare typed properties matching the `dbp_claims` schema from `prd.md`:
  `int $id`, `int $business_id`, `int $user_id`, `string $owner_name`,
  `string $email`, `string $phone`, `string $verification_method`,
  `string $status`, `string $rejection_reason`, `string $created_at`

**`modules/claims/repositories/claim-repository.php`** — must:
- Extend `Model_Base`
- Implement `get_table_name(): string`
- Methods: `find_by_business(int $id): array`, `find_by_user(int $id): array`,
  `find_pending(): array`, `approve(int $id, int $user_id): bool`,
  `reject(int $id, string $reason): bool`
- All queries use `$wpdb->prepare()`

**`modules/maps/controllers/map-controller.php`** — must:
- Extend `Controller_Base`
- `GET /map/config` → returns `{ apiKey, defaultLat, defaultLng, defaultZoom }`
  permission_callback: `__return_true`
- `GET /map/geojson` → accepts `?business_ids[]=` query param, returns GeoJSON
  FeatureCollection; delegates to `Map_Service::build_geojson()`

**`modules/search/models/search-result.php`** — must:
- Not extend `Model_Base` (no DB table)
- A plain value object: typed properties for id, name, slug, avg_rating,
  review_count, price_level, category_name, distance, thumbnail_url,
  is_claimed, is_featured, is_open, permalink
- `static function from_array(array $row): self`
- `to_array(): array`

**`includes/repositories/claim-repository.php`** OR
**`modules/claims/repositories/claim-repository.php`** — one location only;
decide based on whether claims data is shared across modules (use `includes/`)
or fully owned by the claims module (use `modules/claims/`). Justify the choice
in a comment at the top of the file.

---

### STEP 7 — Complete the Template Module

Verify every component in `prompt-template-module.md` exists and is complete.
The Template Module must have all of the following; create or complete any
that are absent or partial:

**`modules/template/module.php`** — must:
- Register `Template_Manager` with the Plugin singleton on module init
- Register the `template_include` filter for CPT template overrides
- Register shortcodes: `[dbp_search_bar]`, `[dbp_search_results]`, `[dbp_review_form]`
- Call `Contract_Validator::register_all()` to load all `$args` contracts
- Provide the static facade: `public static function render(string $slug, array $args, bool $echo): string`

**`modules/template/loader/template-loader.php`** — must:
- Validate slugs (reject `..`, uppercase, spaces, chars outside `[a-z0-9/_-]`)
- Implement three-level resolution:
  1. `get_stylesheet_directory() . '/directories-builder-pro/' . $slug . '.php'`
  2. `get_template_directory()   . '/directories-builder-pro/' . $slug . '.php'`
  3. `DBP_PATH . 'templates/' . $slug . '.php'`
- Admin slugs (starting with `admin/`) skip levels 1 and 2
- Apply filter `dbp/template/paths` on the base directories array
- Apply filter `dbp/template/candidates` on the candidate filenames
- Apply filter `dbp/template/locate` on the final resolved path
- Cache resolved paths in `private static array $located_cache = []`
- Provide `public static function flush_cache(): void` for tests

**`modules/template/renderer/template-renderer.php`** — must:
- Accept `Template_Loader` via constructor injection
- In `render()`: apply `dbp/template/args` filter → ob_start →
  fire `dbp/template/before` action → fire `dbp/template/before/{slug}` action →
  include template (passing `$args`, no `extract()`) →
  fire `dbp/template/after/{slug}` action → fire `dbp/template/after` action →
  ob_get_clean → echo if `$echo` → return string
- If slug not found: fire `dbp/template/missing` action →
  return HTML comment in `WP_DEBUG`, empty string in production

**`modules/template/contracts/contract-validator.php`** — must:
- Have `private static array $contracts = []`
- `register(string $slug, array $contract): void`
- `check(string $slug, array $args): void` — only runs when `WP_DEBUG === true`
  triggers `E_USER_NOTICE` for missing required keys
- `register_all(): void` — registers contracts for all 30+ built-in template slugs
  using the `$args` specs defined in `prompt-template-module.md`

---

### STEP 8 — Verify and Complete All Template Files

Verify that every template file specified in `prompt-template-module.md`
exists under `/templates/` with its full implementation. The complete
required set is:

```
templates/
  business/
    card.php        ← @args: business (required), distance, show_distance, distance_unit
    single.php      ← @args: business, reviews, similar_businesses, review_form_visible
    archive.php     ← @args: initial_results, search_args
    header.php      ← @args: business, show_claim_button
    about.php       ← @args: business
  reviews/
    list.php        ← @args: reviews, business_id, total, current_page, orderby
    item.php        ← @args: review, current_user_has_voted, is_business_owner
    form.php        ← @args: business_id, existing_review
  search/
    bar.php         ← @args: default_query, default_location
    results.php     ← @args: businesses, total, current_page, search_args
  forms/
    form.php        ← @args: form_name, form_title, groups, object_id, tabs, has_tabs
    group.php       ← @args: group, values
    field.php       ← @args: field, value
  admin/
    dashboard.php   ← @args: stats, recent_activity
    settings.php    ← @args: form_html
    moderation.php  ← @args: reviews, status_counts, current_status
    business-edit.php ← @args: form_html, post_id
    user-profile.php  ← @args: form_html, user_id
  partials/
    star-rating.php   ← @args: rating, show_number, count
    badge.php         ← @args: type, label
    price-label.php   ← @args: level
    avatar.php        ← @args: user_id, size, alt
    button.php        ← @args: label, url, variant, icon, target, extra_classes
    notice.php        ← @args: message, type, dismissible
    pagination.php    ← @args: total_pages, current_page, base_url, query_var
    empty-state.php   ← @args: title, message, icon_class, action_label, action_url
    loading-skeleton.php ← @args: count, type
```

For every missing or incomplete template file:
- Create the complete file with full HTML, correct `$args` usage,
  all output properly escaped, and the `@args` docblock
- Call partials via `dbp_render()` not `dbp_template()`
- Never query the database or call services inside a template

---

### STEP 9 — Update the Autoloader

Open `includes/autoloader.php` and ensure every class created or renamed
in Steps 1–8 is present in the class map with the correct file path.

The autoloader must map every class in the format:
```php
'DirectoriesBuilderPro\Full\Namespace\ClassName' => DBP_PATH . 'relative/path/to/file.php',
```

After updating, verify that no class is mapped to a non-existent file.

---

### STEP 10 — Update Plugin Singleton

Open `includes/plugin.php` and verify that the singleton:

1. Instantiates managers in this exact order (dependencies first):
   ```
   1. Fields_Manager     (field types for Form Module)
   2. Form_Manager       (form definitions)
   3. Template_Loader    (needed by Template_Manager)
   4. Template_Renderer  (needed by Template_Manager)
   5. Template_Manager   (needed by all modules)
   6. Ajax_Manager       (needed by all modules with AJAX)
   7. Asset_Manager      (enqueues assets)
   8. Module_Manager     (loads all modules last, after all managers ready)
   ```

2. Injects managers into `Module_Manager` before calling `register_modules()`:
   ```php
   $this->module_manager->set_template_manager($this->template_manager);
   $this->module_manager->set_ajax_manager($this->ajax_manager);
   $this->module_manager->set_form_manager($this->form_manager);
   ```

3. Registers the following WordPress hooks directly from the singleton
   (not from modules):
   ```php
   add_action('init',            [$this, 'on_init']);
   add_action('admin_menu',      [$this, 'register_admin_pages']);
   add_filter('template_include',[$this, 'override_cpt_templates']);
   ```

4. `on_init()` performs:
   - Register `dbp_business` CPT (via Business_Post_Type class)
   - Register `dbp_category` and `dbp_neighborhood` taxonomies
   - Call `Module_Manager::register_modules()` which triggers all `init()` calls

5. Exposes typed getters for every manager:
   ```php
   public function get_template_manager(): Template_Manager
   public function get_form_manager(): Form_Manager
   public function get_ajax_manager(): Ajax_Manager
   public function get_asset_manager(): Asset_Manager
   public function get_module_manager(): Module_Manager
   ```

---

## ✅ PHASE 4 — VALIDATION

After all fixes are applied, run this complete validation checklist.
Every item must pass before the task is complete.

### 4.1 — Naming Convention Compliance
```
□ grep -rn "class [^D]" modules/ core/ includes/      → only DirectoriesBuilderPro\ classes
□ grep -rn "function [^d]" core/helpers/              → only dbp_ prefixed functions
□ grep -rn "add_action\|add_filter" modules/           → zero results (hooks via managers only)
□ grep -rn "do_action\|apply_filters" modules/         → only dbp/ namespaced hook names
□ All database table references use {prefix}dbp_ format
```

### 4.2 — Separation of Concerns
```
□ grep -rn "\$wpdb" modules/                           → zero results (queries in repositories only)
□ grep -rn "\$wpdb" includes/services/                 → zero results (queries in repositories only)
□ grep -rn "echo\|<html\|<div\|<span" includes/services/ → zero results
□ grep -rn "echo\|<html\|<div\|<span" modules/*/controllers/ → zero results
□ grep -rn "get_option\|update_option" modules/        → only in storage adapters
□ Every template file: no service or repository class instantiation present
```

### 4.3 — Manager-Driven Execution
```
□ grep -rn "add_action\|add_filter\|register_rest_route\|add_menu_page" modules/
  → zero results (all hooks via managers)
□ Module_Manager::register_modules() is the only place module init() is called
□ Ajax_Manager is the only place wp_ajax_* hooks are registered
□ Asset_Manager is the only place wp_enqueue_scripts callbacks are registered
```

### 4.4 — Template Rendering
```
□ grep -rn "dbp_template\b" .                          → zero results (fully removed)
□ grep -rn "dbp_render" .                              → only in /templates/ files
□ grep -rn "render_template" .                         → only in Module_Base / Controller_Base subclasses
□ grep -rn "include\|require" modules/ includes/services/ → zero results for HTML-producing files
□ All 30+ template files exist under /templates/
□ Every template file has an @args docblock
```

### 4.5 — Template Module
```
□ Template_Loader: slug validator rejects '..' traversal
□ Template_Loader: admin/ slugs resolve to plugin path only
□ Template_Loader: frontend slugs check child theme → parent theme → plugin
□ Template_Loader: static cache returns same path on second call without file_exists()
□ Template_Renderer: fires dbp/template/before and dbp/template/after for every render
□ Template_Renderer: fires dbp/template/missing when slug not found
□ Contract_Validator: triggers E_USER_NOTICE for missing required $args key in WP_DEBUG
□ Contract_Validator: silent in production (WP_DEBUG = false)
□ Template_Manager: get_render_log() returns entries only in WP_DEBUG mode
```

### 4.6 — Plugin Boots Without Errors
```
□ Fresh WordPress install: activate plugin → no PHP errors or warnings
□ All 6 custom tables created (dbp_businesses, dbp_business_meta,
  dbp_reviews, dbp_review_votes, dbp_claims, dbp_checkins)
□ dbp_business CPT and taxonomies registered on init
□ wp-admin → Dashboard loads without errors
□ /business/ archive page loads without errors
□ /business/{slug} single page loads without errors
□ REST endpoint GET /wp-json/directories-builder-pro/v1/businesses returns 200
□ REST endpoint GET /wp-json/directories-builder-pro/v1/search returns 200
```

### 4.7 — Autoloader
```
□ Every class in core/, includes/, and modules/ is registered in autoloader
□ No class is mapped to a non-existent file path
□ No class instantiation triggers a "Class not found" fatal error
```

### 4.8 — Security Baseline
```
□ Every AJAX handler: check_ajax_referer() present
□ Every REST write endpoint: permission_callback is non-trivial
□ Every $wpdb query uses $wpdb->prepare()
□ Every template output uses esc_html(), esc_attr(), esc_url(), or wp_kses_post()
□ No user-supplied value reaches a file path (grep for $_GET / $_POST near include/require)
```

---

## 📄 DELIVERABLES

When this task is complete, the following must exist:

1. **`AUDIT-REPORT.md`** — the Phase 1 inventory and violation tables
2. **`ARCHITECTURE.md`** — the Phase 2 authoritative blueprint
3. **All fixed files** — every violation corrected, every missing file created
4. **Updated `AUDIT-REPORT.md`** — a "Resolution" column added to every table,
   showing what was done to fix each issue
5. **Updated `README.md`** — changelog entry for this audit/completion task:
   ```markdown
   ## [1.2.0] — Architectural Audit & Completion
   ### Fixed
   - [list every naming violation corrected]
   - [list every separation of concerns violation corrected]
   - [list every manager execution violation corrected]
   - [list every template rendering violation corrected]
   ### Added
   - [list every missing component created]
   ### Architecture
   - AUDIT-REPORT.md and ARCHITECTURE.md added to project root
   ```

---

## ⚠️ CONSTRAINTS

- **Read every existing file before changing it.** Do not overwrite working
  code based on assumptions. If a file already correctly implements what
  this prompt requires, leave it unchanged and note it as passing in the
  audit report.
- **No slug, schema, or API contract changes.** This task fixes structure
  and wiring only. Feature behaviour must remain identical before and after.
- **No new features.** If the audit reveals a missing feature from `prd.md`,
  document it in `AUDIT-REPORT.md` under a "Future Work" section but do not
  implement it here.
- **No React, no Vue, no build step.** All code is PHP and vanilla JS.
- **Do NOT generate placeholder or stub files.** Every file must contain
  complete, working, production-ready code.
