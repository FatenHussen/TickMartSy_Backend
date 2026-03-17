<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            map: null,
            marker: null,
            latitudeField: @js($getLatitudeField()),
            longitudeField: @js($getLongitudeField()),
            defaultLat: @js($getDefaultLatitude()),
            defaultLng: @js($getDefaultLongitude()),
            defaultZoom: @js($getDefaultZoom()),
            mapInitialized: false,
            isUpdatingFromMap: false,
            lastLat: null,
            lastLng: null,

            init() {
                if (typeof L === 'undefined') {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);

                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => this.initMap();
                    document.head.appendChild(script);
                } else {
                    this.initMap();
                }

                setInterval(() => {
                    if (!this.isUpdatingFromMap) {
                        this.checkFieldChanges();
                    }
                }, 500);
            },

            initMap() {
                if (this.mapInitialized) return;

                const currentLat = this.getFieldValue(this.latitudeField) || this.defaultLat;
                const currentLng = this.getFieldValue(this.longitudeField) || this.defaultLng;

                this.lastLat = currentLat;
                this.lastLng = currentLng;

                this.map = L.map(this.$refs.mapContainer).setView([currentLat, currentLng], this.defaultZoom);
                this.mapInitialized = true;

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(this.map);

                this.marker = L.marker([currentLat, currentLng], {
                    draggable: true
                }).addTo(this.map);

                this.marker.on('dragend', (e) => {
                    const position = e.target.getLatLng();
                    this.updateFields(position.lat, position.lng);
                });

                this.map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    this.marker.setLatLng([lat, lng]);
                    this.updateFields(lat, lng);
                });

                setTimeout(() => {
                    this.map.invalidateSize();
                }, 100);
            },

            getFieldValue(fieldName) {
                const selector = `input[name=${JSON.stringify(fieldName)}]`;
                const input = document.querySelector(selector);
                return input && input.value ? parseFloat(input.value) : null;
            },

            checkFieldChanges() {
                const lat = this.getFieldValue(this.latitudeField);
                const lng = this.getFieldValue(this.longitudeField);

                if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                    if (lat !== this.lastLat || lng !== this.lastLng) {
                        this.lastLat = lat;
                        this.lastLng = lng;
                        this.updateMarkerFromInputs(lat, lng);
                    }
                }
            },

            updateFields(lat, lng) {
                this.isUpdatingFromMap = true;

                this.lastLat = lat;
                this.lastLng = lng;

                const latSelector = `input[name=${JSON.stringify(this.latitudeField)}]`;
                const lngSelector = `input[name=${JSON.stringify(this.longitudeField)}]`;

                const latInput = document.querySelector(latSelector);
                const lngInput = document.querySelector(lngSelector);

                if (latInput) {
                    latInput.value = lat.toFixed(6);
                    latInput.dispatchEvent(new Event('input', { bubbles: true }));
                    latInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                if (lngInput) {
                    lngInput.value = lng.toFixed(6);
                    lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                    lngInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                setTimeout(() => {
                    this.isUpdatingFromMap = false;
                }, 300);
            },

            updateMarkerFromInputs(lat, lng) {
                if (this.marker && this.map) {
                    this.marker.setLatLng([lat, lng]);
                    this.map.setView([lat, lng], this.map.getZoom());
                }
            }
        }"
        x-init="init()"
        class="w-full"
    >
        <div
            x-ref="mapContainer"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600"
            style="height: 400px; z-index: 0;"
        ></div>

        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ __('custom.shops.map_picker_help') }}
        </div>
    </div>
</x-dynamic-component>
