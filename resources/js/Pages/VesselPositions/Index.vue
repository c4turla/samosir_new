<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix for default marker icons using CDN to avoid Vite import issues with binaries
const iconUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
const shadowUrl = 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png';

const DefaultIcon = L.icon({
    iconUrl,
    shadowUrl,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
});

L.Marker.prototype.options.icon = DefaultIcon;

defineOptions({
    layout: AppLayout
});

const props = defineProps({
    positions: Array
});

const mapContainer = ref(null);
let map = null;

onMounted(async () => {
    // Wait for Inertia transition and DOM rendering
    await nextTick();
    
    if (!mapContainer.value) return;

    // Default center (Samosir / Sibolga area)
    let center = [1.724178, 98.790000];
    let zoom = 18;

    // Filter out sites without valid coordinates
    const validPositions = props.positions.filter(p => p.latitude && p.longitude);

    if (validPositions.length > 0) {
        center = [validPositions[0].latitude, validPositions[0].longitude];
    }

    try {
        map = L.map(mapContainer.value).setView(center, zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        validPositions.forEach(site => {
            const vesselList = site.vessels.map(v => `
                <div class="mb-2 p-2 bg-gray-50 border-l-4 border-blue-500 rounded">
                    <div class="font-bold text-blue-700">${v.name}</div>
                    <div class="text-xs text-gray-600">GT: ${v.gt} | Status: <span class="font-medium">${v.status}</span></div>
                    <div class="text-[10px] text-gray-400 mt-1">Tiba: ${v.arrival_time}</div>
                </div>
            `).join('');

            const popupContent = `
                <div class="p-1 min-w-[200px]">
                    <h3 class="font-bold text-sm mb-2 border-b pb-1">${site.name}</h3>
                    <div class="max-h-[200px] overflow-y-auto">
                        ${vesselList || '<p class="text-xs text-gray-500">Tidak ada kapal saat ini</p>'}
                    </div>
                </div>
            `;

            L.marker([site.latitude, site.longitude])
                .addTo(map)
                .bindPopup(popupContent);
        });

        // If multiple markers, fit bounds
        if (validPositions.length > 1) {
            const bounds = L.latLngBounds(validPositions.map(p => [p.latitude, p.longitude]));
            map.fitBounds(bounds, { padding: [50, 50] });
        }

        // Force Leaflet to recalculate the map size after rendering
        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 300);

    } catch (error) {
        console.error("Map initialization failed:", error);
    }
});
</script>

<template>
    <Head title="Posisi Kapal" />

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Posisi Kapal (Real-time)</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Peta sebaran kapal yang saat ini sedang berada di dermaga/tangkahan.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center space-x-4 text-xs font-medium">
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                            <span class="text-gray-700 dark:text-gray-300">{{ positions.reduce((acc, p) => acc + p.vessels.length, 0) }} Kapal di Pelabuhan</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-indigo-500 rounded-full mr-2"></span>
                            <span class="text-gray-700 dark:text-gray-300">{{ positions.length }} Lokasi Aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-2 sm:p-4">
                    <!-- Map Container -->
                    <div 
                        ref="mapContainer" 
                        class="w-full h-[600px] rounded-lg z-0 relative shadow-inner bg-gray-100 dark:bg-gray-900"
                    ></div>
                </div>
            </div>

            <!-- Legend/Table -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="site in positions" :key="site.id" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3 border-b border-gray-50 dark:border-gray-700 pb-2">
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center">
                            <i class="ri-map-pin-2-fill text-red-500 mr-2"></i>
                            {{ site.name }}
                        </h3>
                        <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded-full font-bold">
                            {{ site.vessels.length }} Kapal
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div v-for="v in site.vessels" :key="v.id" class="flex flex-col text-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ v.name }}</span>
                                <span class="text-[10px] text-gray-400">{{ v.status }}</span>
                            </div>
                            <div class="text-[10px] text-gray-500 mt-0.5 flex items-center">
                                <i class="ri-history-line mr-1 text-[8px]"></i> {{ v.arrival_time }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Ensure map is responsive and visible */
.leaflet-container {
    height: 600px;
    width: 100%;
    border-radius: 0.5rem;
}

/* Custom styles for Leaflet popups to match app theme */
.leaflet-popup-content-wrapper {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.dark .leaflet-popup-content-wrapper {
    background-color: #1f2937;
    color: white;
}

.leaflet-popup-tip {
    background-color: white;
}

.dark .leaflet-popup-tip {
    background-color: #1f2937;
}
</style>
