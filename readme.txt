=== Directories Builder Pro ===
Contributors: directoriesbuilderpro
Tags: directory, business listings, reviews, maps, local search
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
A Yelp-like local business discovery and review platform. Full-featured directory with search, reviews, maps, business claims, and gamification.
== Description ==
**Directories Builder Pro** transforms any WordPress site into a powerful, Yelp-like local business discovery and review platform.
= Key Features =
* **Business Listings** — Custom post type with location, hours, contact info, photos, and rich metadata.
* **Star Reviews & Trust Scoring** — 5-star reviews with trust scoring, automatic spam detection, and moderation workflows.
* **Full-Text + Geospatial Search** — Search by keyword, category, location, radius, price, and rating. Autocomplete with live suggestions.
* **Google Maps Integration** — Interactive map with business markers, info windows, directions, and draggable admin picker.
* **Business Claims** — Owner verification workflow with email/phone/document verification and admin approval.
* **User Profiles & Gamification** — Points, badges, elite status, check-ins, and review milestones.
* **Admin Dashboard** — Stats overview, review moderation with WP_List_Table, settings panel, business editor meta boxes.
* **SEO Ready** — JSON-LD structured data, clean URLs, proper heading hierarchy.
* **Developer Friendly** — Modular architecture, action/filter hooks, namespaced PHP 8.0+ code, zero external dependencies.
= Architecture =
Built with an Elementor-inspired modular architecture:
* **Core**: Base classes, managers (Module, Asset, AJAX), database schema, helpers.
* **Modules**: Reviews, Business, Search, Maps, Claims — each self-contained with controllers, models, templates.
* **Services**: Business, Review, Search, User — business logic layer.
* **Repositories**: Data access layer with prepared queries.
= Shortcodes =
* `[dbp_search_bar]` — Render the dual-field search bar with autocomplete.
* `[dbp_search_results]` — Render the search results grid with filters and map.
== Installation ==
1. Upload the `directories-builder-pro` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Directory → Settings** to configure your Google Maps API key.
4. Start adding businesses via **Businesses → Add New**.
== Frequently Asked Questions ==
= Do I need a Google Maps API key? =
Yes. The Maps JavaScript API key is required for map features. Enter it in **Directory → Settings → Maps**.
= Can users submit businesses? =
Enable "Allow User Submissions" in **Directory → Settings → Business** to let registered users submit listings.
= How does review moderation work? =
Choose between Manual (admin approval required) or Auto-approve mode in Settings. Reviews with low trust scores always go to moderation regardless of the mode.
= What is the trust scoring system? =
Trust scores are calculated based on account age, prior approved reviews, profile completeness, review length, and spam history. Higher scores increase the likelihood of auto-approval.
== Changelog ==
= 1.0.0 =
* Initial release.
* Business listings with CPT, categories, and neighborhoods.
* 5-star review system with trust scoring.
* Full-text and geospatial search with autocomplete.
* Google Maps integration.
* Business ownership claims.
* User profiles with points and badges.
* Admin dashboard, review moderation, and settings.
== Upgrade Notice ==
= 1.0.0 =
Initial release.