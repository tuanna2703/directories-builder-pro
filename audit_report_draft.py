import os

plugin_dir = '/Volumes/DATA/Workspace/Development/MAMP/htdocs/wordpress-plugins/wp-content/plugins/directories-builder-pro'

content = """## 🔍 PHASE 1 — FULL CODEBASE SCAN & INVENTORY

### Section 1.1 — Module Inventory Table

| Module | module.php | Controllers | Services | Models | Repositories | AJAX | Templates | Notes |
|---|---|---|---|---|---|---|---|---|
| business | ✅ exists | ✅ 1 file | ❌ none | ✅ 1 file | ❌ none | ✅ 1 file | ✅ 2 files | Service is in /includes/services |
| claims | ✅ exists | ✅ 1 file | ❌ none | ❌ none | ❌ none | ✅ 1 file | ❌ none | Repository missing; Model missing |
| form | ✅ exists | ✅ 1 file | ❌ none | ❌ none | ❌ none | ✅ 1 file | ❌ none | |
| maps | ✅ exists | ❌ none | ✅ 1 file | ❌ none | ❌ none | ❌ none | ❌ none | Controller missing |
| reviews | ✅ exists | ✅ 1 file | ❌ none | ✅ 1 file | ❌ none | ✅ 1 file | ✅ 2 files | Service is in /includes/services |
| search | ✅ exists | ✅ 1 file | ❌ none | ❌ none | ❌ none | ✅ 1 file | ✅ 2 files | Model missing; Service is in /includes |
| template | ✅ exists | ❌ none | ❌ none | ❌ none | ❌ none | ❌ none | ❌ none | Core template module |

### Section 1.2 — Manager Inventory Table

| Manager | File exists | Responsibilities match spec | Violations found |
|---|---|---|---|
| Module_Manager | ✅ | ✅ | none |
| Asset_Manager | ✅ | ⚠️ partial | none |
| Ajax_Manager | ✅ | ✅ | none |
| Template_Manager | ❌ | ❌ | file missing |
| Form_Manager | ✅ | ✅ | none |

### Section 1.3 — Naming Convention Violations

| File | Type | Found | Expected | Severity |
|---|---|---|---|---|
| (None found) | | | | |

### Section 1.4 — Separation of Concerns Violations

| File | Violation | Rule Broken | Fix Required |
|---|---|---|---|
| `modules/business/controllers/business-controller.php` | Direct `$wpdb` query within controller (lines 137-159) | Controller queries DB directly | Move queries to `Business_Repository` |
| `modules/claims/controllers/claim-controller.php` | Direct `$wpdb` query within controller (lines 48-173) | Controller queries DB directly | Move queries to `Claim_Repository` |
| `includes/services/business-service.php` | Direct `$wpdb` query within service (lines 289-293) | Service queries DB directly | Move query to `Business_Repository` |
| `includes/services/review-service.php` | Direct `$wpdb` query within service (lines 244-248)| Service queries DB directly | Move query to `Review_Repository` |

### Section 1.5 — Manager Execution Violations

| File | Line | Hook | Violation | Fix |
|---|---|---|---|---|
| `modules/maps/maps-module.php` | 18 | `add_action('admin_init', ...)` | Module registers hook directly | Route through Manager |
| `modules/form/form-module.php` | 33 | `add_action('rest_api_init', ...)` | Module registers REST route directly | Call from Manager |
| `modules/form/form-module.php` | 54 | `add_action('admin_enqueue_scripts', ...)` | Module registers asset directly | Route through Asset_Manager |
| `modules/business/business-module.php` | 28 | `add_action('rest_api_init', ...)` | Module registers REST route directly | Call from Manager |
| `modules/template/template-module.php` | 177 | `add_filter('template_include', ...)` | Module registers filter directly | Move to Plugin singleton |
| `modules/search/search-module.php` | 20 | `add_action('rest_api_init', ...)` | Module registers REST route directly | Call from Manager |
| `modules/claims/claims-module.php` | 20 | `add_action('rest_api_init', ...)` | Module registers REST route directly | Call from Manager |
| `modules/reviews/reviews-module.php` | 42 | `add_action('rest_api_init', ...)` | Module registers REST route directly | Call from Manager |

### Section 1.6 — Template Rendering Violations

| File | Line | Violation | Fix |
|---|---|---|---|
| (None found in services/controllers) | | | |

### Section 1.7 — Missing Components

| Required Component | Required By | Missing File | Priority |
|---|---|---|---|
| Claim Repository | prd.md §7, claims module | modules/claims/repositories/claim-repository.php | High |
| Maps Controller | structure.md | modules/maps/controllers/map-controller.php | Medium |
| Claim Model | prd.md §7, claims module | modules/claims/models/claim.php | High |
| Search Result Model | prompt-audit.md | modules/search/models/search-result.php | High |
| Contract Validator | prompt-template-module.md | modules/template/contracts/contract-validator.php | High |

### Section 1.8 — Template Module Completeness Check

| Component | File | Exists | Fully Implemented | Issues |
|---|---|---|---|---|
| Template_Loader | `modules/template/loader/template-loader.php` | ✅ | ✅ | none |
| Template_Renderer | `modules/template/renderer/template-renderer.php` | ✅ | ✅ | none |
| Contract_Validator | `modules/template/contracts/contract-validator.php` | ❌ | — | file absent |
| Template_Manager | `core/managers/template-manager.php` | ❌ | — | file absent |
| Template Files | `templates/*` | ❌ | — | Directory missing entirely |
"""

with open(os.path.join(plugin_dir, 'AUDIT-REPORT.md'), 'w') as f:
    f.write(content)

print("AUDIT-REPORT.md generated.")
