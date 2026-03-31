# Product Requirements Document
## Directories Builder Pro — WordPress Plugin

> A Yelp-like local business discovery and review platform, built as a modular WordPress plugin inspired by Elementor's internal architecture.

---

## Table of Contents

1. [Product Overview](#1-product-overview)
2. [Goals & Non-Goals](#2-goals--non-goals)
3. [User Roles](#3-user-roles)
4. [Core Features](#4-core-features)
5. [User Journeys](#5-user-journeys)
6. [UI Patterns & Components](#6-ui-patterns--components)
7. [Database Schema](#7-database-schema)
8. [REST API Endpoints](#8-rest-api-endpoints)
9. [Security Requirements](#9-security-requirements)
10. [Internationalisation](#10-internationalisation)
11. [MVP Scope vs. Phase 2](#11-mvp-scope-vs-phase-2)
12. [Technical Constraints](#12-technical-constraints)

---

## 1. Product Overview

**Plugin name:** Directories Builder Pro
**Text domain:** `directories-builder-pro`
**Namespace:** `DirectoriesBuilderPro\`
**Minimum requirements:** WordPress 6.0+, PHP 8.0+, MySQL 5.7+

Directories Builder Pro enables WordPress site owners to run a fully featured local business directory and review platform — similar to Yelp — directly within WordPress. The plugin uses an Elementor-inspired modular architecture: a singleton Plugin class, a Module Manager, abstract base classes, and hook-based registration throughout.

---

## 2. Goals & Non-Goals

### Goals
- Allow visitors to search, discover, and review local businesses
- Allow business owners to claim and manage their listings
- Allow site admins to moderate reviews and manage the directory
- Provide geospatial search (radius, "near me") powered by Google Maps
- Ship a clean, extensible codebase that third-party developers can extend via actions and filters

### Non-Goals (Phase 1)
- In-app payments, reservations, or food ordering integrations
- Machine-learning-based recommendation engine
- Native mobile app
- Advertising / sponsored listing monetization stack
- Full social graph (messaging, friend requests)

---

## 3. User Roles

| Role | Description |
|---|---|
| **Visitor** | Unauthenticated user; can search, browse, and read reviews |
| **Registered User** | Logged-in WordPress user; can write reviews, vote, flag, check in |
| **Business Owner** | Registered user who has claimed a listing; can edit their business and respond to reviews |
| **Administrator** | Full access; manages all listings, reviews, claims, and plugin settings |

---

## 4. Core Features

### 4.1 Business Listings

- Custom Post Type `dbp_business` with dedicated single and archive templates
- Fields: name, slug, description, address, city, state, zip, country, lat/lng, phone, website, email, price level (1–4), opening hours (per day), status
- Extended attributes stored in `dbp_business_meta`: outdoor seating, Wi-Fi, parking, delivery, takeout, reservations, accessibility
- User-uploadable photos attached to listings
- Taxonomies: `dbp_category`, `dbp_neighborhood`
- Schema.org `LocalBusiness` JSON-LD injected in `<head>` on single business pages

### 4.2 Reviews & Ratings

- 1–5 star ratings with free-text review (minimum 25 characters)
- Up to 5 photos per review
- Reviews have a **status**: `pending`, `approved`, `rejected`, `spam`
- Moderation mode configurable: auto-approve or manual approval
- One review per user per business (enforced server-side)
- Helpful / Not Helpful voting (one vote per user per review)
- Flagging for admin review
- Business owners can post a public response to any review
- Reviews display: avatar, display name, Elite badge (if applicable), rating stars, relative date, truncated text with "Read more", photo thumbnails, vote counts, owner response

### 4.3 Trust Scoring

Basic heuristic trust score per reviewer (stored on review submission):

| Signal | Points |
|---|---|
| Account age > 30 days | +10 |
| Account age > 180 days | +20 |
| Prior approved reviews ≥ 5 | +15 |
| Profile photo set | +10 |
| Review length ≥ 100 chars | +10 |
| Prior spam flags on account | −30 |

Reviews with trust score below threshold are held for manual moderation regardless of moderation mode setting.

### 4.4 Search & Filtering

- Full-text keyword search on business name, description, category
- Location search: city/neighborhood text field, or "Near Me" (browser geolocation)
- Geospatial radius query (haversine formula with bounding-box pre-filter)
- Filters: category, minimum rating (1–5★), price level ($–$$$$), open now, distance radius
- Sort options: Relevance | Distance | Highest Rated | Most Reviewed | Newest
- Autocomplete: top 5 matching business names + categories (debounced, 300ms)
- Results returned as paginated JSON (REST) or HTML partial (AJAX fallback)

### 4.5 Maps Integration

- Google Maps JS API (API key configurable in admin settings)
- Split-view on search results: list pane + synchronized map pane
- Marker clustering for dense result sets
- Marker click → highlight corresponding card in list + open info window
- Map idle event → refresh visible businesses in list
- Business detail page: single marker with info window + embed map
- Static map URL generator for email notifications and non-JS fallback
- "Near Me" button: `navigator.geolocation` → reverse geocode → populate Where field

### 4.6 Business Claiming

- Any registered user can submit a claim for an unclaimed business
- Claim fields: owner name, email, phone, verification method (phone/email/document)
- Claim statuses: `pending`, `approved`, `rejected`
- Email notifications at each stage (admin on submission; claimant on approval/rejection)
- Once approved: business `claimed_by` set to user ID; user gains Business Owner role capabilities for that listing

### 4.7 Admin Dashboard

- Stats overview: total businesses, reviews this week, pending claims, pending reviews
- Recent activity feed: last 10 review submissions
- Quick links: Add Business | Moderate Reviews | Settings
- Business edit screen: custom meta boxes for location (with map picker), contact, details, status
- Review moderation table: filter by status, bulk approve/reject/spam, inline rejection reason
- Settings page: Maps API key, moderation mode, review constraints, search defaults, distance unit

### 4.8 User Profiles

- Public profile page per user: avatar, bio, review history, photo count, badge display
- Points system: awarded for approved reviews, photos uploaded, check-ins, helpful votes received
- Elite badge: manually awarded by admin to high-quality contributors
- Review history tab, Photos tab

### 4.9 Check-ins

- Logged-in users can check in to a business (once per day per business)
- Check-in count displayed on business card and detail page
- Check-ins stored in `dbp_checkins` table

### 4.10 Extensibility

Plugin exposes the following hooks for third-party developers:

**Action hooks:**
- `dbp/review/submitted` — fires after a review is inserted (args: review_id, business_id, user_id)
- `dbp/review/approved` — fires after a review status changes to approved
- `dbp/business/claimed` — fires after a claim is approved (args: business_id, user_id)
- `dbp/checkin/recorded` — fires after a check-in is saved

**Filter hooks:**
- `dbp/search/args` — modify search query args before execution
- `dbp/review/trust_score` — override calculated trust score (args: score, user_id, review_data)
- `dbp/business/card_html` — filter the rendered HTML of a business card
- `dbp/settings/defaults` — override default plugin settings

---

## 5. User Journeys

### 5.1 Searching for a Business

1. Visitor lands on the archive/search page
2. Enters keyword in "What?" field and location in "Where?" field (or clicks "Near Me")
3. Autosuggest dropdown shows matching business names and categories
4. Submits search → sees paginated results list with synchronized map
5. Applies filter chips (Open Now, $$, 4★+) to narrow results
6. Clicks a business card → lands on single business page

### 5.2 Writing a Review

1. Logged-in user opens a business detail page
2. Clicks "Write a Review" CTA
3. Selects star rating via interactive star picker
4. Types review text (live character counter, minimum 25)
5. Optionally uploads up to 5 photos (drag-drop zone, thumbnail previews)
6. Submits → review enters moderation queue or appears immediately (depending on mode)
7. User may receive notifications when the business owner responds

### 5.3 Claiming a Business

1. Registered user visits an unclaimed business page
2. Clicks "Claim This Business"
3. Fills claim form: owner name, contact details, verification method
4. Submits → admin receives email notification
5. Admin reviews claim in dashboard, approves or rejects with optional reason
6. Claimant receives email with outcome
7. If approved: owner can edit business details and respond to reviews

### 5.4 Moderating Reviews (Admin)

1. Admin navigates to Dashboard → Moderate Reviews
2. Sees table filtered to "Pending" by default
3. Reads review excerpt, clicks "Approve" or "Reject"
4. On reject: enters reason (stored, not shown publicly)
5. Can bulk-select and bulk-action multiple reviews
6. Can mark reviews as Spam (affects reviewer trust score)

---

## 6. UI Patterns & Components

### Search Bar
- Two-field layout: "What?" (keyword) | "Where?" (location text or geolocation)
- Autosuggest dropdown below each field
- "Near Me" icon button on Where field
- Full-width on mobile, inline on desktop

### Business Card
- Thumbnail image (150×150, placeholder fallback)
- Business name (linked)
- Star rating (filled/empty/half SVG stars)
- Review count ("42 reviews")
- Price level ($–$$$$)
- Primary category
- Distance from search origin (if available)
- Badges: "Claimed" | "Featured" | "New"

### Filter Bar
- Horizontal scrollable chip row: All | Open Now | $–$$$$ | 4★+ | Distance radius
- "More Filters" chip opens a full filter drawer
- Active filters shown with × to clear individually
- "Clear All" resets to defaults

### Business Detail Page (section order)
1. Hero: photo carousel, name, overall rating, review count, price, category, primary CTA buttons (Call, Directions, Website, Claim)
2. About: description, attributes grid, opening hours table
3. Photos grid: up to 12 most recent, "See all X photos" link
4. Reviews: sort controls + review list + review form
5. Location: embedded Google Map + formatted address
6. Similar Businesses: 3 cards from same category + city

### Review Item
- Avatar (32px), display name, Elite badge, star rating, relative date
- Review text (truncated at 300 chars, "Read more" toggle)
- Photo thumbnails (up to 3, lightbox on click)
- Helpful / Not Helpful buttons with counts
- Owner response block (avatar, "Owner", date, response text)
- Flag link

### Star Picker (form)
- 5 stars, click to select, hover preview
- Selected state persists on re-hover
- Accessible via keyboard (arrow keys)

---

## 7. Database Schema

### `dbp_businesses`
```sql
CREATE TABLE dbp_businesses (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    wp_post_id    BIGINT UNSIGNED NOT NULL,
    name          VARCHAR(255)    NOT NULL,
    slug          VARCHAR(255)    NOT NULL,
    description   LONGTEXT,
    address       VARCHAR(255),
    city          VARCHAR(100),
    state         VARCHAR(100),
    zip           VARCHAR(20),
    country       VARCHAR(100)    DEFAULT 'US',
    lat           DECIMAL(10,7),
    lng           DECIMAL(10,7),
    phone         VARCHAR(30),
    website       VARCHAR(255),
    email         VARCHAR(100),
    price_level   TINYINT         DEFAULT 1,
    hours         JSON,
    status        ENUM('active','inactive','pending') DEFAULT 'active',
    claimed_by    BIGINT UNSIGNED DEFAULT NULL,
    featured      TINYINT(1)      DEFAULT 0,
    avg_rating    DECIMAL(3,2)    DEFAULT 0.00,
    review_count  INT UNSIGNED    DEFAULT 0,
    created_at    DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY slug (slug),
    KEY wp_post_id (wp_post_id),
    KEY lat_lng (lat, lng),
    KEY status (status)
);
```

### `dbp_business_meta`
```sql
CREATE TABLE dbp_business_meta (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_id BIGINT UNSIGNED NOT NULL,
    meta_key    VARCHAR(100)    NOT NULL,
    meta_value  LONGTEXT,
    PRIMARY KEY (id),
    KEY business_id (business_id),
    KEY meta_key (meta_key)
);
```

### `dbp_reviews`
```sql
CREATE TABLE dbp_reviews (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_id  BIGINT UNSIGNED NOT NULL,
    user_id      BIGINT UNSIGNED NOT NULL,
    rating       TINYINT UNSIGNED NOT NULL,
    content      LONGTEXT        NOT NULL,
    status       ENUM('pending','approved','rejected','spam') DEFAULT 'pending',
    trust_score  SMALLINT        DEFAULT 0,
    helpful      INT UNSIGNED    DEFAULT 0,
    not_helpful  INT UNSIGNED    DEFAULT 0,
    created_at   DATETIME        DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY business_id (business_id),
    KEY user_id (user_id),
    KEY status (status),
    UNIQUE KEY one_per_user (business_id, user_id)
);
```

### `dbp_review_votes`
```sql
CREATE TABLE dbp_review_votes (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    review_id  BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    vote_type  ENUM('helpful','not_helpful','flag') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY one_vote (review_id, user_id, vote_type)
);
```

### `dbp_claims`
```sql
CREATE TABLE dbp_claims (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_id         BIGINT UNSIGNED NOT NULL,
    user_id             BIGINT UNSIGNED NOT NULL,
    owner_name          VARCHAR(255)    NOT NULL,
    email               VARCHAR(100)    NOT NULL,
    phone               VARCHAR(30),
    verification_method ENUM('phone','email','document') DEFAULT 'email',
    status              ENUM('pending','approved','rejected') DEFAULT 'pending',
    rejection_reason    TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY business_id (business_id),
    KEY user_id (user_id),
    KEY status (status)
);
```

### `dbp_checkins`
```sql
CREATE TABLE dbp_checkins (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_id BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY business_id (business_id),
    KEY user_id (user_id)
);
```

---

## 8. REST API Endpoints

Base namespace: `/wp-json/directories-builder-pro/v1/`

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/businesses` | Public | List/search businesses |
| GET | `/businesses/{id}` | Public | Single business detail |
| POST | `/businesses` | Admin | Create a business |
| PUT | `/businesses/{id}` | Owner/Admin | Update a business |
| GET | `/search` | Public | Full search with all filters |
| GET | `/autocomplete` | Public | Autosuggest for search bar |
| GET | `/reviews` | Public | Reviews for a business |
| POST | `/reviews` | Logged in | Submit a review |
| PUT | `/reviews/{id}` | Owner/Admin | Edit a review |
| DELETE | `/reviews/{id}` | Owner/Admin | Delete a review |
| POST | `/reviews/{id}/vote` | Logged in | Vote helpful/not helpful |
| POST | `/reviews/{id}/flag` | Logged in | Flag a review |
| POST | `/claims` | Logged in | Submit a claim |
| GET | `/claims/{id}` | Admin | View a claim |
| PUT | `/claims/{id}/approve` | Admin | Approve a claim |
| PUT | `/claims/{id}/reject` | Admin | Reject a claim |
| POST | `/checkins` | Logged in | Record a check-in |

---

## 9. Security Requirements

- Every AJAX handler must call `check_ajax_referer()` or verify `current_user_can()`
- Every REST endpoint must define a non-trivial `permission_callback`
- All user input sanitized: `sanitize_text_field()`, `absint()`, `floatval()`, `wp_kses_post()`
- All output escaped: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- No raw SQL string interpolation outside repository classes; use `$wpdb->prepare()`
- Nonce localized to JS as `dbpData.nonce` and verified server-side on every AJAX call
- Rate limiting on review submission: max 3 reviews per user per hour (checked in service layer)
- File upload validation: MIME type check, max size 5MB per photo, max 5 photos per review

---

## 10. Internationalisation

- All user-facing strings wrapped in `__()` or `_e()` with text domain `directories-builder-pro`
- `declare(strict_types=1)` in every PHP file
- `.pot` file generated at `/languages/directories-builder-pro.pot`
- Dates and numbers formatted respecting `get_locale()` and WordPress locale settings

---

## 11. MVP Scope vs. Phase 2

### Phase 1 (MVP) — Build Now
- Business listings CPT with full CRUD
- Review submission, display, voting, flagging, moderation
- Geospatial search with Google Maps
- Business claiming workflow
- Admin dashboard: stats, moderation, settings
- User profiles (basic: review history, badges)
- Check-ins
- REST API for all core resources

### Phase 2 — Defer
- Advanced ML-based trust scoring and review filtering
- Elite reviewer program with community events
- Advertising and sponsored listing placement stack
- Reservation and ordering integrations
- Full social graph: friends, messaging, follower feed
- Personalized discovery feed
- A/B testing and experimentation framework
- Elasticsearch integration for advanced full-text search

---

## 12. Technical Constraints

- **No external Composer dependencies** — pure WordPress APIs (`$wpdb`, WP REST API, WP Hooks, WP AJAX)
- **PHP 8.0+** — typed properties, named arguments, match expressions, nullsafe operator, union types, `declare(strict_types=1)`
- **Namespace:** `DirectoriesBuilderPro\` throughout
- **No inline SQL** outside repository classes
- **WordPress Coding Standards** compliant
- **No PHP short tags**
- Prefer `do_action()` / `apply_filters()` over tight coupling between modules
