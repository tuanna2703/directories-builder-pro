import os

plugin_dir = '/Volumes/DATA/Workspace/Development/MAMP/htdocs/wordpress-plugins/wp-content/plugins/directories-builder-pro'

def append_to_file(filepath, text):
    with open(filepath, 'a', encoding='utf-8') as f:
        f.write(text)

text = """
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
"""

append_to_file(os.path.join(plugin_dir, 'ARCHITECTURE.md'), text)

