<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            map: null,
            marker: null,
            latitude: @entangle($getLatitudeField()),
            longitude: @entangle($getLongitudeField()),
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
                const currentLat = this.latitude || this.defaultLat;
                const currentLng = this.longitude || this.defaultLng;

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
                    this.updateFromMap(position.lat, position.lng);
                });

                this.map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    this.marker.setLatLng([lat, lng]);
                    this.updateFromMap(lat, lng);
                });

                // Watch for changes from form inputs
                this.$watch('latitude', (value) => {
                    if (!this.isUpdatingFromMap && value && this.longitude) {
                        this.updateMarkerPosition();
                    }
                });

                this.$watch('longitude', (value) => {
                    if (!this.isUpdatingFromMap && value && this.latitude) {
                        this.updateMarkerPosition();
                    }
                });

                setTimeout(() => {
                    this.map.invalidateSize();
                }, 100);
            },

            updateFromMap(lat, lng) {
                this.isUpdatingFromMap = true;
                this.latitude = parseFloat(lat.toFixed(6));
                this.longitude = parseFloat(lng.toFixed(6));

                setTimeout(() => {
                    this.isUpdatingFromMap = false;
                }, 100);
            },

            updateMarkerPosition() {
                const lat = parseFloat(this.latitude);
                const lng = parseFloat(this.longitude);

                if (!isNaN(lat) && !isNaN(lng) && this.marker && this.map) {
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
