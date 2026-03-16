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
            isUpdatingFromMap: false,

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
            },

            initMap() {
                const currentLat = this.getFieldValue(this.latitudeField) || this.defaultLat;
                const currentLng = this.getFieldValue(this.longitudeField) || this.defaultLng;

                this.map = L.map(this.$refs.mapContainer).setView([currentLat, currentLng], this.defaultZoom);

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

                // Watch for changes in form fields
                this.$watch('$wire.' + this.latitudeField, (value) => {
                    if (!this.isUpdatingFromMap) {
                        this.updateMarkerFromInputs();
                    }
                });

                this.$watch('$wire.' + this.longitudeField, (value) => {
                    if (!this.isUpdatingFromMap) {
                        this.updateMarkerFromInputs();
                    }
                });

                setTimeout(() => {
                    this.map.invalidateSize();
                }, 100);
            },

            getFieldValue(fieldName) {
                try {
                    return this.$wire.get(fieldName);
                } catch (e) {
                    return null;
                }
            },

            updateFields(lat, lng) {
                this.isUpdatingFromMap = true;

                try {
                    this.$wire.set(this.latitudeField, lat.toFixed(6));
                    this.$wire.set(this.longitudeField, lng.toFixed(6));
                } catch (e) {
                    console.error('Error updating fields:', e);
                }

                setTimeout(() => {
                    this.isUpdatingFromMap = false;
                }, 100);
            },

            updateMarkerFromInputs() {
                const lat = this.getFieldValue(this.latitudeField);
                const lng = this.getFieldValue(this.longitudeField);

                if (lat && lng && !isNaN(lat) && !isNaN(lng) && this.marker && this.map) {
                    this.marker.setLatLng([parseFloat(lat), parseFloat(lng)]);
                    this.map.setView([parseFloat(lat), parseFloat(lng)], this.map.getZoom());
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
