## 📐 PHASE 2 — ARCHITECTURE BLUEPRINT

### Section 2.1 — Layer Diagram

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

### Section 2.2 — Module Specification Table
*Defined manually per prompt.*

### Section 2.3 — Manager Specification Table
*Defined manually per prompt.*

### Section 2.4 — Execution Flow for Key Features
*Defined manually per prompt.*

### Section 2.5 — Hook Registry
*Defined manually per prompt.*


### Section 2.2 — Module Specification Table

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
├── module.php               — Wires Business_Controller, Business_Ajax
├── controllers/
│   └── business-controller.php — REST CRUD for /businesses; delegates to Business_Service
├── models/
│   └── business.php           — Data shape; maps to dbp_businesses table
├── ajax/
│   └── business-ajax.php      — AJAX: get hours, update meta
└── (no module-level service — Business_Service lives in includes/services/)

Module: search
├── module.php               — Wires Search_Controller, Search_Ajax
├── controllers/
│   └── search-controller.php — REST CRUD for /search; delegates to Search_Service
├── models/
│   └── search-result.php      — Data shape; plain value object
├── ajax/
│   └── search-ajax.php      — AJAX: autocomplete
└── (no module-level service — Search_Service lives in includes/services/)

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

### Section 2.3 — Manager Specification Table

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

### Section 2.4 — Execution Flow for Key Features

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
WordPress hook: wp_ajax_dbp_submit_review
→ Ajax_Manager::handle()
→ Review_Ajax::handle_submit()
→ Review_Service::submit_review()
  → Validate inputs, nonce, logic
  → Review_Repository::insert() (saves to dbp_reviews)
  → dbp/review/submitted action
→ Response (JSON success)

Feature: Perform a search (REST)
WordPress hook: rest_api_init
→ Search_Controller::register_routes()
→ GET /wp-json/directories-builder-pro/v1/search
→ Search_Controller::get_items()
  → Search_Service::search()
  → Returns WP_REST_Response with items array
→ Response (JSON output)

Feature: Save plugin settings (Form Module REST)
WordPress hook: rest_api_init
→ Form_Controller::register_routes()
→ POST /wp-json/directories-builder-pro/v1/forms/{id}
→ Form_Controller::save_item()
  → Form_Manager::get($id)
  → Form_Base::save()
    → Field validation
    → Storage_Adapter::save()
→ Response (JSON success)

Feature: Render search results page (archive template)
WordPress hook: template_include
→ Plugin singleton (template_include filter)
→ Template_Manager::render('business/archive', $args)
→ Template_Loader::locate('business/archive')
→ Template_Renderer::render()
→ templates/business/archive.php
  → dbp_render('search/bar')
  → dbp_render('search/results')
→ HTML output
```

### Section 2.5 — Hook Registry

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
| dbp/business/claimed | action | Claim_Service | — | fired by service |
| dbp/checkin/recorded | action | Checkin_Service | — | fired by service |
| dbp/search/args | filter | Search_Service | — | Template_Manager |
| dbp/review/trust_score | filter | Review_Service | — | Template_Manager |
| dbp/business/card_html | filter | Template_Renderer | — | Template_Manager |
| dbp/settings/defaults | filter | Option_Storage | — | Form_Manager |
```
