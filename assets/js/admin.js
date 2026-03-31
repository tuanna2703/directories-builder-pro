/**
 * Directories Builder Pro – Admin JavaScript
 *
 * Map picker, hours editor, moderation actions.
 *
 * @package DirectoriesBuilderPro
 */
(function () {
    'use strict';
    // ── Map Picker (Business Edit) ──
    function initAdminMap() {
        const mapEl = document.getElementById('dbp-admin-map');
        if (!mapEl || !window.google?.maps) return;
        const lat = parseFloat(mapEl.dataset.lat) || 40.7128;
        const lng = parseFloat(mapEl.dataset.lng) || -74.0060;
        const center = { lat, lng };
        const map = new google.maps.Map(mapEl, {
            center,
            zoom: 15,
            disableDefaultUI: false,
        });
        const marker = new google.maps.Marker({
            position: center,
            map,
            draggable: true,
        });
        // Update hidden fields on marker drag
        google.maps.event.addListener(marker, 'dragend', function (e) {
            document.getElementById('dbp_lat').value = e.latLng.lat().toFixed(7);
            document.getElementById('dbp_lng').value = e.latLng.lng().toFixed(7);
        });
        // Update marker on map click
        google.maps.event.addListener(map, 'click', function (e) {
            marker.setPosition(e.latLng);
            document.getElementById('dbp_lat').value = e.latLng.lat().toFixed(7);
            document.getElementById('dbp_lng').value = e.latLng.lng().toFixed(7);
        });
        // Geocode address fields
        const addressFields = ['dbp_address', 'dbp_city', 'dbp_state', 'dbp_zip', 'dbp_country'];
        addressFields.forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('change', geocodeAddress);
            }
        });
        function geocodeAddress() {
            if (!window.google?.maps?.Geocoder) return;
            const address = addressFields.map(id => {
                const el = document.getElementById(id);
                return el ? el.value : '';
            }).filter(Boolean).join(', ');
            if (!address) return;
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    const loc = results[0].geometry.location;
                    map.setCenter(loc);
                    marker.setPosition(loc);
                    document.getElementById('dbp_lat').value = loc.lat().toFixed(7);
                    document.getElementById('dbp_lng').value = loc.lng().toFixed(7);
                }
            });
        }
    }
    // ── Hours Editor ──
    function initHoursEditor() {
        document.querySelectorAll('.dbp-closed-toggle').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const day = this.dataset.day;
                const row = this.closest('tr');
                const timeInputs = row.querySelectorAll('input[type="time"]');
                timeInputs.forEach(input => {
                    input.disabled = this.checked;
                });
            });
        });
    }
    // ── Moderation Actions ──
    function initModeration() {
        // Reject with reason (prompt)
        document.querySelectorAll('.dbp-reject-link').forEach(link => {
            link.addEventListener('click', function (e) {
                const reason = prompt('Enter rejection reason (optional):');
                if (reason !== null) {
                    const url = new URL(this.href);
                    url.searchParams.set('reason', reason);
                    window.location.href = url.toString();
                }
                e.preventDefault();
            });
        });
    }
    // ── Init ──
    document.addEventListener('DOMContentLoaded', () => {
        initAdminMap();
        initHoursEditor();
        initModeration();
    });
})();
