import os

plugin_dir = '/Volumes/DATA/Workspace/Development/MAMP/htdocs/wordpress-plugins/wp-content/plugins/directories-builder-pro'

def append_to_file(filepath, text):
    with open(filepath, 'a', encoding='utf-8') as f:
        f.write(text)

text = """
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
"""

append_to_file(os.path.join(plugin_dir, 'ARCHITECTURE.md'), text)

print("ARCHITECTURE.md draft appended.")
