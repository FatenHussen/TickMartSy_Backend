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
                const latInput = document.querySelector('[name=\'' + this.latitudeField + '\']');
                const lngInput = document.querySelector('[name=\'' + this.longitudeField + '\']');

                const currentLat = latInput?.value ? parseFloat(latInput.value) : this.defaultLat;
                const currentLng = lngInput?.value ? parseFloat(lngInput.value) : this.defaultLng;

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

                setTimeout(() => {
                    this.map.invalidateSize();
                }, 100);
            },

            updateFields(lat, lng) {
                const latInput = document.querySelector('[name=\'' + this.latitudeField + '\']');
                const lngInput = document.querySelector('[name=\'' + this.longitudeField + '\']');

                if (latInput) {
                    latInput.value = lat.toFixed(6);
                    latInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (lngInput) {
                    lngInput.value = lng.toFixed(6);
                    lngInput.dispatchEvent(new Event('input', { bubbles: true }));
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
