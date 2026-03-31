/**
 * Directories Builder Pro – Frontend JavaScript
 *
 * Handles search, autocomplete, reviews, voting, photo upload, map, lightbox.
 *
 * @package DirectoriesBuilderPro
 */
(function () {
    'use strict';
    const DBP = window.dbp_data || {};
    const AJAX_URL = DBP.ajax_url || '/wp-admin/admin-ajax.php';
    const NONCE = DBP.nonce || '';
    const REST_URL = DBP.rest_url || '/wp-json/dbp/v1/';
    // ── Utility ──
    function debounce(fn, ms) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), ms);
        };
    }
    function ajaxPost(action, data) {
        const body = new FormData();
        body.append('action', action);
        body.append('nonce', NONCE);
        for (const [key, value] of Object.entries(data)) {
            if (Array.isArray(value)) {
                value.forEach(v => body.append(key + '[]', v));
            } else {
                body.append(key, value);
            }
        }
        return fetch(AJAX_URL, { method: 'POST', body, credentials: 'same-origin' })
            .then(r => r.json());
    }
    function restGet(endpoint, params) {
        const url = new URL(REST_URL + endpoint, window.location.origin);
        for (const [key, value] of Object.entries(params || {})) {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.set(key, value);
            }
        }
        return fetch(url, { credentials: 'same-origin' }).then(r => r.json());
    }
    // ── Search ──
    const searchState = {
        page: 1,
        params: {},
        filters: {},
    };
    function initSearch() {
        const submitBtn = document.getElementById('dbp-search-submit');
        const whatInput = document.getElementById('dbp-search-what');
        const whereInput = document.getElementById('dbp-search-where');
        const sortSelect = document.getElementById('dbp-sort-select');
        const geoBtn = document.getElementById('dbp-geo-btn');
        if (!submitBtn) return;
        submitBtn.addEventListener('click', () => {
            searchState.page = 1;
            searchState.params = {
                q: whatInput?.value || '',
                location: whereInput?.value || '',
                lat: document.getElementById('dbp-search-lat')?.value || '',
                lng: document.getElementById('dbp-search-lng')?.value || '',
            };
            executeSearch();
        });
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                searchState.params.orderby = sortSelect.value;
                searchState.page = 1;
                executeSearch();
            });
        }
        // Geolocation
        if (geoBtn && navigator.geolocation) {
            geoBtn.addEventListener('click', () => {
                geoBtn.classList.add('loading');
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        document.getElementById('dbp-search-lat').value = pos.coords.latitude;
                        document.getElementById('dbp-search-lng').value = pos.coords.longitude;
                        if (whereInput) whereInput.value = 'Current Location';
                        geoBtn.classList.remove('loading');
                    },
                    () => {
                        geoBtn.classList.remove('loading');
                        alert(DBP.i18n?.geo_error || 'Could not get your location.');
                    }
                );
            });
        }
        // Filter chips
        document.querySelectorAll('.dbp-chip[data-filter]').forEach(chip => {
            chip.addEventListener('click', () => {
                const filter = chip.dataset.filter;
                const value = chip.dataset.value;
                if (chip.classList.contains('dbp-chip--active')) {
                    chip.classList.remove('dbp-chip--active');
                    delete searchState.filters[filter];
                } else {
                    // Deactivate siblings with same filter
                    document.querySelectorAll(`.dbp-chip[data-filter="${filter}"]`).forEach(s => {
                        s.classList.remove('dbp-chip--active');
                    });
                    chip.classList.add('dbp-chip--active');
                    searchState.filters[filter] = value;
                }
                const clearBtn = document.getElementById('dbp-clear-filters');
                if (clearBtn) {
                    clearBtn.style.display = Object.keys(searchState.filters).length ? '' : 'none';
                }
                searchState.page = 1;
                executeSearch();
            });
        });
        // Clear all filters
        const clearBtn = document.getElementById('dbp-clear-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchState.filters = {};
                document.querySelectorAll('.dbp-chip--active').forEach(c => c.classList.remove('dbp-chip--active'));
                clearBtn.style.display = 'none';
                searchState.page = 1;
                executeSearch();
            });
        }
        // View toggle
        document.querySelectorAll('.dbp-view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.dbp-view-btn').forEach(b => b.classList.remove('dbp-view-btn--active'));
                btn.classList.add('dbp-view-btn--active');
                const splitView = document.getElementById('dbp-split-view');
                if (splitView) {
                    splitView.classList.toggle('dbp-split-view--map-visible', btn.dataset.view === 'map');
                }
            });
        });
        // Pagination
        document.getElementById('dbp-prev-page')?.addEventListener('click', () => {
            if (searchState.page > 1) {
                searchState.page--;
                executeSearch();
            }
        });
        document.getElementById('dbp-next-page')?.addEventListener('click', () => {
            searchState.page++;
            executeSearch();
        });
        // Autocomplete
        if (whatInput) {
            whatInput.addEventListener('input', debounce(() => {
                const val = whatInput.value.trim();
                if (val.length < 2) {
                    hideDropdown('dbp-suggest-what');
                    return;
                }
                restGet('autocomplete', { q: val }).then(data => {
                    if (data.data?.suggestions) {
                        renderSuggestions('dbp-suggest-what', data.data.suggestions, whatInput);
                    }
                });
            }, 300));
        }
    }
    function executeSearch() {
        const grid = document.getElementById('dbp-business-grid');
        const skeleton = document.getElementById('dbp-results-skeleton');
        const emptyState = document.getElementById('dbp-results-empty');
        if (skeleton) skeleton.style.display = '';
        if (grid) grid.style.display = 'none';
        if (emptyState) emptyState.style.display = 'none';
        const params = {
            ...searchState.params,
            ...searchState.filters,
            page: searchState.page,
        };
        ajaxPost('dbp_search', params).then(res => {
            if (skeleton) skeleton.style.display = 'none';
            if (res.success && res.data) {
                const { html, total, pages } = res.data;
                if (grid) {
                    grid.innerHTML = html;
                    grid.style.display = total > 0 ? '' : 'none';
                }
                if (emptyState) {
                    emptyState.style.display = total === 0 ? '' : 'none';
                }
                // Update count
                const countEl = document.getElementById('dbp-result-count');
                if (countEl) countEl.textContent = total;
                // Update pagination
                updatePagination(searchState.page, pages);
            }
        });
    }
    function updatePagination(page, totalPages) {
        const nav = document.getElementById('dbp-pagination');
        if (!nav) return;
        nav.style.display = totalPages > 1 ? '' : 'none';
        const prev = document.getElementById('dbp-prev-page');
        const next = document.getElementById('dbp-next-page');
        const info = document.getElementById('dbp-page-info');
        if (prev) prev.disabled = page <= 1;
        if (next) next.disabled = page >= totalPages;
        if (info) info.textContent = `Page ${page} of ${totalPages}`;
    }
    function renderSuggestions(containerId, suggestions, input) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        suggestions.forEach(s => {
            const item = document.createElement('div');
            item.className = 'dbp-suggest__item';
            item.setAttribute('role', 'option');
            item.textContent = s.label;
            item.addEventListener('click', () => {
                input.value = s.label;
                hideDropdown(containerId);
            });
            container.appendChild(item);
        });
        container.style.display = suggestions.length ? '' : 'none';
    }
    function hideDropdown(containerId) {
        const el = document.getElementById(containerId);
        if (el) el.style.display = 'none';
    }
    // Close suggestions on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dbp-search-bar__field')) {
            document.querySelectorAll('.dbp-suggest').forEach(el => { el.style.display = 'none'; });
        }
    });
    // ── Reviews ──
    function initReviews() {
        // Star picker
        const starPicker = document.getElementById('dbp-star-picker');
        if (starPicker) {
            const stars = starPicker.querySelectorAll('.dbp-star-picker__star');
            const ratingInput = document.getElementById('dbp-rating-value');
            const ratingText = document.getElementById('dbp-rating-text');
            const labels = ['', 'Not good', 'Could be better', 'OK', 'Good', 'Great!'];
            stars.forEach((star, idx) => {
                star.addEventListener('click', () => {
                    const value = idx + 1;
                    if (ratingInput) ratingInput.value = value;
                    if (ratingText) ratingText.textContent = labels[value] || '';
                    stars.forEach((s, i) => {
                        s.classList.toggle('dbp-star-picker__star--active', i <= idx);
                        s.setAttribute('aria-checked', i === idx ? 'true' : 'false');
                    });
                });
                star.addEventListener('mouseenter', () => {
                    stars.forEach((s, i) => {
                        s.classList.toggle('dbp-star-picker__star--hover', i <= idx);
                    });
                });
            });
            starPicker.addEventListener('mouseleave', () => {
                stars.forEach(s => s.classList.remove('dbp-star-picker__star--hover'));
            });
        }
        // Character counter
        const textarea = document.getElementById('dbp-review-content');
        const counter = document.getElementById('dbp-char-count');
        if (textarea && counter) {
            textarea.addEventListener('input', () => {
                counter.textContent = textarea.value.length;
            });
        }
        // Submit review
        const submitBtn = document.getElementById('dbp-submit-review');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => {
                const rating = document.getElementById('dbp-rating-value')?.value;
                const content = document.getElementById('dbp-review-content')?.value;
                const businessId = submitBtn.dataset.businessId;
                if (!rating || rating === '0') {
                    showMessage('dbp-review-messages', DBP.i18n?.select_rating || 'Please select a rating.', 'error');
                    return;
                }
                submitBtn.disabled = true;
                submitBtn.querySelector('.dbp-spinner').style.display = '';
                submitBtn.querySelector('.dbp-btn__text').style.display = 'none';
                // Collect photo IDs
                const photoInputs = document.querySelectorAll('input[name="dbp_photos[]"]');
                const photos = Array.from(photoInputs).map(i => i.value);
                ajaxPost('dbp_submit_review', {
                    business_id: businessId,
                    rating,
                    content,
                    'photos': photos,
                }).then(res => {
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.dbp-spinner').style.display = 'none';
                    submitBtn.querySelector('.dbp-btn__text').style.display = '';
                    if (res.success) {
                        showMessage('dbp-review-messages', res.data.message, 'success');
                        // Reset form
                        if (textarea) textarea.value = '';
                        if (counter) counter.textContent = '0';
                        document.querySelectorAll('.dbp-star-picker__star').forEach(s => {
                            s.classList.remove('dbp-star-picker__star--active');
                        });
                        document.getElementById('dbp-rating-value').value = '0';
                    } else {
                        showMessage('dbp-review-messages', res.data?.message || 'Error', 'error');
                    }
                });
            });
        }
        // Sort reviews
        document.querySelectorAll('.dbp-reviews__sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.dbp-reviews__sort-btn').forEach(b => {
                    b.classList.remove('dbp-reviews__sort-btn--active');
                    b.setAttribute('aria-selected', 'false');
                });
                btn.classList.add('dbp-reviews__sort-btn--active');
                btn.setAttribute('aria-selected', 'true');
                loadReviews(btn.dataset.sort);
            });
        });
        // Load more reviews
        const loadMoreBtn = document.getElementById('dbp-load-more-reviews');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                const page = parseInt(loadMoreBtn.dataset.page, 10) + 1;
                const businessId = loadMoreBtn.dataset.businessId;
                loadMoreBtn.dataset.page = page;
                loadMoreBtn.querySelector('.dbp-spinner').style.display = '';
                restGet('reviews', {
                    business_id: businessId,
                    page,
                    per_page: 10,
                }).then(data => {
                    loadMoreBtn.querySelector('.dbp-spinner').style.display = 'none';
                    const list = document.getElementById('dbp-reviews-list');
                    if (data.data?.reviews) {
                        data.data.reviews.forEach(r => {
                            list.insertAdjacentHTML('beforeend', buildReviewHTML(r));
                        });
                        if (list.children.length >= parseInt(loadMoreBtn.dataset.total, 10)) {
                            loadMoreBtn.style.display = 'none';
                        }
                    }
                });
            });
        }
        // Read more
        document.addEventListener('click', e => {
            const btn = e.target.closest('.dbp-review-item__read-more');
            if (btn) {
                const id = btn.dataset.reviewId;
                const truncated = document.getElementById('dbp-review-text-' + id);
                const full = document.getElementById('dbp-review-full-' + id);
                if (truncated) truncated.style.display = 'none';
                if (full) full.style.display = '';
                btn.style.display = 'none';
            }
        });
        // Voting
        document.addEventListener('click', e => {
            const voteBtn = e.target.closest('.dbp-vote-btn');
            if (voteBtn && !voteBtn.disabled) {
                voteBtn.disabled = true;
                ajaxPost('dbp_vote_review', {
                    review_id: voteBtn.dataset.reviewId,
                    vote_type: voteBtn.dataset.vote,
                }).then(res => {
                    if (res.success && res.data.counts) {
                        const rid = voteBtn.dataset.reviewId;
                        const helpfulEl = document.getElementById('dbp-helpful-' + rid);
                        const notHelpfulEl = document.getElementById('dbp-not-helpful-' + rid);
                        if (helpfulEl) helpfulEl.textContent = '(' + res.data.counts.helpful + ')';
                        if (notHelpfulEl) notHelpfulEl.textContent = '(' + res.data.counts.not_helpful + ')';
                        // Disable all vote buttons for this review
                        const reviewEl = document.getElementById('dbp-review-' + rid);
                        if (reviewEl) {
                            reviewEl.querySelectorAll('.dbp-vote-btn').forEach(b => {
                                b.disabled = true;
                                b.classList.add('dbp-vote-btn--disabled');
                            });
                        }
                    }
                });
            }
        });
        // Flag
        document.addEventListener('click', e => {
            const flagBtn = e.target.closest('.dbp-flag-btn');
            if (flagBtn) {
                if (!confirm(DBP.i18n?.confirm_flag || 'Are you sure you want to report this review?')) return;
                ajaxPost('dbp_flag_review', {
                    review_id: flagBtn.dataset.reviewId,
                }).then(res => {
                    if (res.success) {
                        flagBtn.textContent = DBP.i18n?.reported || 'Reported';
                        flagBtn.disabled = true;
                    }
                });
            }
        });
    }
    function loadReviews(sort) {
        const reviewsContainer = document.getElementById('dbp-reviews');
        if (!reviewsContainer) return;
        const businessId = reviewsContainer.dataset.businessId;
        const list = document.getElementById('dbp-reviews-list');
        const skeleton = document.getElementById('dbp-reviews-skeleton');
        if (skeleton) skeleton.style.display = '';
        if (list) list.style.display = 'none';
        restGet('reviews', {
            business_id: businessId,
            orderby: sort,
            per_page: 10,
        }).then(data => {
            if (skeleton) skeleton.style.display = 'none';
            if (list) {
                list.innerHTML = '';
                list.style.display = '';
                if (data.data?.reviews) {
                    data.data.reviews.forEach(r => {
                        list.insertAdjacentHTML('beforeend', buildReviewHTML(r));
                    });
                }
            }
        });
    }
    function buildReviewHTML(r) {
        const stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
        return `
            <div class="dbp-review-item" id="dbp-review-${r.id}" data-review-id="${r.id}">
                <div class="dbp-review-item__header">
                    <div class="dbp-review-item__avatar">
                        <img src="${r.author_avatar || ''}" alt="${r.author_name || ''}" width="40" height="40" class="dbp-review-item__avatar-img">
                    </div>
                    <div class="dbp-review-item__author-info">
                        <span class="dbp-review-item__author-name">${r.author_name || 'Anonymous'}</span>
                        ${r.is_elite ? '<span class="dbp-badge dbp-badge--elite">Elite</span>' : ''}
                        <div class="dbp-review-item__meta">
                            <span class="dbp-stars">${stars}</span>
                            <span class="dbp-review-item__date">${r.time_ago || ''}</span>
                        </div>
                    </div>
                </div>
                <div class="dbp-review-item__content">
                    <p class="dbp-review-item__text">${r.content || ''}</p>
                </div>
                <div class="dbp-review-item__actions">
                    <button type="button" class="dbp-vote-btn dbp-vote-btn--helpful" data-review-id="${r.id}" data-vote="helpful">
                        👍 Helpful <span class="dbp-vote-btn__count" id="dbp-helpful-${r.id}">(${r.helpful || 0})</span>
                    </button>
                    <button type="button" class="dbp-vote-btn dbp-vote-btn--not-helpful" data-review-id="${r.id}" data-vote="not_helpful">
                        👎 <span class="dbp-vote-btn__count" id="dbp-not-helpful-${r.id}">(${r.not_helpful || 0})</span>
                    </button>
                    <button type="button" class="dbp-flag-btn" data-review-id="${r.id}">Report</button>
                </div>
            </div>
        `;
    }
    // ── Photo Upload ──
    function initPhotoUpload() {
        const dropzone = document.getElementById('dbp-dropzone');
        const input = document.getElementById('dbp-photo-input');
        const previews = document.getElementById('dbp-photo-previews');
        if (!dropzone || !input) return;
        dropzone.addEventListener('click', () => input.click());
        dropzone.addEventListener('dragover', e => {
            e.preventDefault();
            dropzone.classList.add('dbp-photo-upload__dropzone--active');
        });
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dbp-photo-upload__dropzone--active');
        });
        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.classList.remove('dbp-photo-upload__dropzone--active');
            handleFiles(e.dataTransfer.files);
        });
        input.addEventListener('change', () => handleFiles(input.files));
        function handleFiles(files) {
            const maxPhotos = DBP.max_photos || 5;
            const existing = previews?.children.length || 0;
            Array.from(files).slice(0, maxPhotos - existing).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'dbp-photo-preview';
                    wrapper.innerHTML = `
                        <img src="${e.target.result}" alt="" class="dbp-photo-preview__img">
                        <button type="button" class="dbp-photo-preview__remove">&times;</button>
                    `;
                    wrapper.querySelector('.dbp-photo-preview__remove').addEventListener('click', () => {
                        wrapper.remove();
                    });
                    previews?.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
                // Upload to WordPress media library
                const formData = new FormData();
                formData.append('async-upload', file);
                formData.append('action', 'upload-attachment');
                formData.append('_wpnonce', DBP.upload_nonce || '');
                fetch(AJAX_URL, { method: 'POST', body: formData, credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.data?.id) {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'dbp_photos[]';
                            hidden.value = data.data.id;
                            previews?.appendChild(hidden);
                        }
                    });
            });
        }
    }
    // ── Map ──
    function initMap() {
        const mapEl = document.getElementById('dbp-business-map');
        if (!mapEl || !window.google?.maps) return;
        const lat = parseFloat(mapEl.dataset.lat) || 0;
        const lng = parseFloat(mapEl.dataset.lng) || 0;
        if (!lat || !lng) return;
        const map = new google.maps.Map(mapEl, {
            center: { lat, lng },
            zoom: 15,
            styles: getMapStyles(),
            disableDefaultUI: true,
            zoomControl: true,
        });
        new google.maps.Marker({
            position: { lat, lng },
            map,
            title: mapEl.dataset.name || '',
        });
    }
    function initSearchMap() {
        const mapEl = document.getElementById('dbp-map');
        if (!mapEl || !window.google?.maps) return;
        const map = new google.maps.Map(mapEl, {
            center: { lat: 40.7128, lng: -74.0060 },
            zoom: 12,
            styles: getMapStyles(),
            disableDefaultUI: true,
            zoomControl: true,
        });
        // Load GeoJSON if provided
        const geojsonStr = mapEl.dataset.geojson;
        if (geojsonStr) {
            try {
                const geojson = JSON.parse(geojsonStr);
                if (geojson.features?.length) {
                    const bounds = new google.maps.LatLngBounds();
                    geojson.features.forEach(f => {
                        const [lng, lat] = f.geometry.coordinates;
                        const marker = new google.maps.Marker({
                            position: { lat, lng },
                            map,
                            title: f.properties.name,
                        });
                        bounds.extend(marker.getPosition());
                        const info = new google.maps.InfoWindow({
                            content: `
                                <div style="max-width:200px">
                                    <strong>${f.properties.name}</strong><br>
                                    ${'★'.repeat(Math.round(f.properties.rating))} (${f.properties.review_count})<br>
                                    <a href="${f.properties.permalink}">View</a>
                                </div>
                            `,
                        });
                        marker.addListener('click', () => info.open(map, marker));
                    });
                    map.fitBounds(bounds);
                }
            } catch (e) { /* invalid geojson */ }
        }
        window.dbpSearchMap = map;
    }
    function getMapStyles() {
        return [
            { featureType: 'poi.business', stylers: [{ visibility: 'off' }] },
            { featureType: 'transit', elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
        ];
    }
    // ── Lightbox ──
    function initLightbox() {
        document.addEventListener('click', e => {
            const trigger = e.target.closest('.dbp-lightbox-trigger');
            if (!trigger) return;
            e.preventDefault();
            const src = trigger.dataset.full || trigger.href;
            const lightbox = document.getElementById('dbp-lightbox');
            const img = document.getElementById('dbp-lightbox-img');
            if (lightbox && img) {
                img.src = src;
                lightbox.style.display = 'flex';
            }
        });
        document.getElementById('dbp-lightbox-close')?.addEventListener('click', () => {
            const lightbox = document.getElementById('dbp-lightbox');
            if (lightbox) lightbox.style.display = 'none';
        });
        document.getElementById('dbp-lightbox')?.addEventListener('click', e => {
            if (e.target.id === 'dbp-lightbox') {
                e.target.style.display = 'none';
            }
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                const lightbox = document.getElementById('dbp-lightbox');
                if (lightbox) lightbox.style.display = 'none';
            }
        });
    }
    // ── Claim Button ──
    function initClaim() {
        const claimBtn = document.getElementById('dbp-claim-btn');
        if (!claimBtn) return;
        claimBtn.addEventListener('click', () => {
            const businessId = claimBtn.dataset.businessId;
            const name = prompt(DBP.i18n?.claim_name || 'Enter your full name (business owner):');
            if (!name) return;
            const email = prompt(DBP.i18n?.claim_email || 'Enter your business email:');
            if (!email) return;
            claimBtn.disabled = true;
            ajaxPost('dbp_submit_claim', {
                business_id: businessId,
                owner_name: name,
                email: email,
                verification_method: 'email',
            }).then(res => {
                if (res.success) {
                    claimBtn.textContent = DBP.i18n?.claim_submitted || 'Claim Submitted';
                    claimBtn.disabled = true;
                    alert(res.data.message);
                } else {
                    claimBtn.disabled = false;
                    alert(res.data?.message || 'Error submitting claim.');
                }
            });
        });
    }
    // ── Check-in ──
    function initCheckin() {
        document.querySelectorAll('.dbp-checkin-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.disabled = true;
                restGet('../checkins', {}).then(() => {}); // placeholder
                // Use REST for check-in
                fetch(REST_URL + 'checkins', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': DBP.wp_rest_nonce || '' },
                    body: JSON.stringify({ business_id: parseInt(btn.dataset.businessId, 10) }),
                    credentials: 'same-origin',
                }).then(r => r.json()).then(data => {
                    if (data.data?.message) {
                        btn.textContent = '✓ ' + (DBP.i18n?.checked_in || 'Checked In');
                    } else {
                        btn.disabled = false;
                    }
                });
            });
        });
    }
    // ── Helper ──
    function showMessage(containerId, message, type) {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.innerHTML = `<div class="dbp-message dbp-message--${type}">${message}</div>`;
        el.style.display = '';
        setTimeout(() => { el.style.display = 'none'; }, 5000);
    }
    // ── Init ──
    document.addEventListener('DOMContentLoaded', () => {
        initSearch();
        initReviews();
        initPhotoUpload();
        initMap();
        initSearchMap();
        initLightbox();
        initClaim();
        initCheckin();
    });
})();
