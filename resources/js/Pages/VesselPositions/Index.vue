<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
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
let markerGroup = null;
let pathLayerGroup = null;
let refreshInterval = null;
const selectedVessel = ref(null);

// Map basemap styles
const mapStyles = [
    { id: 'google-road', name: 'Google Maps', url: 'https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', attribution: '&copy; Google Maps' },
    { id: 'google-satellite', name: 'Satelit', url: 'https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', attribution: '&copy; Google Satellite' },
    { id: 'osm', name: 'OpenStreetMap', url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', attribution: '&copy; OpenStreetMap' },
    { id: 'dark', name: 'Gelap (CartoDB)', url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', attribution: '&copy; CartoDB' }
];

const activeStyle = ref('google-road');
let tileLayer = null;

const changeMapStyle = (styleId) => {
    activeStyle.value = styleId;
    if (!map) return;
    
    const style = mapStyles.find(s => s.id === styleId);
    if (!style) return;
    
    if (tileLayer) {
        map.removeLayer(tileLayer);
    }
    
    tileLayer = L.tileLayer(style.url, {
        maxZoom: 20,
        attribution: style.attribution
    }).addTo(map);
};

const renderMarkers = (positions) => {
    if (!map) return;

    // Clear existing markers
    if (markerGroup) {
        markerGroup.clearLayers();
    } else {
        markerGroup = L.featureGroup().addTo(map);
    }

    // Filter out sites without valid coordinates
    const validPositions = positions.filter(p => p.latitude && p.longitude);

    validPositions.forEach(site => {
        const vesselList = site.vessels.map(v => `
            <div class="mb-2 p-2 bg-gray-50 dark:bg-gray-800 border-l-4 ${v.status === 'Persiapan Berangkat' ? 'border-orange-500' : 'border-blue-500'} rounded text-gray-800 dark:text-gray-250">
                <div class="font-bold text-gray-900 dark:text-white">${v.name}</div>
                <div class="text-xs text-gray-600 dark:text-gray-300 font-medium">GT: ${v.gt} | Status: <span>${v.status}</span></div>
                <div class="text-[10px] text-gray-400 mt-1.5 flex justify-between items-center">
                    <span><i class="ri-time-line align-middle mr-0.5"></i> ${v.arrival_time}</span>
                    <button class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-bold text-[10px] cursor-pointer btn-show-path border border-blue-500 dark:border-blue-400 rounded px-1.5 py-0.5 bg-white dark:bg-gray-950 transition-colors" data-vessel-id="${v.id}" data-site-id="${site.id}">
                        Jalur
                    </button>
                </div>
            </div>
        `).join('');

        const popupContent = `
            <div class="p-1 min-w-[220px]">
                <h3 class="font-bold text-sm mb-2 border-b pb-1 text-gray-800 dark:text-white">${site.name}</h3>
                <div class="max-h-[200px] overflow-y-auto pr-1">
                    ${vesselList || '<p class="text-xs text-gray-500">Tidak ada kapal saat ini</p>'}
                </div>
            </div>
        `;

        const marker = L.marker([site.latitude, site.longitude]).bindPopup(popupContent);
        markerGroup.addLayer(marker);
    });
};

const selectVessel = (vessel) => {
    selectedVessel.value = vessel;

    // Initialize pathLayerGroup if not present
    if (!pathLayerGroup) {
        pathLayerGroup = L.layerGroup().addTo(map);
    } else {
        pathLayerGroup.clearLayers();
    }

    if (!vessel.movements || vessel.movements.length === 0) return;

    const latlngs = vessel.movements.map(m => [m.latitude, m.longitude]);

    // 1. Draw dashed polyline
    if (latlngs.length > 1) {
        L.polyline(latlngs, {
            color: '#3b82f6', // Tailwind blue-500
            weight: 4,
            dashArray: '8, 8',
            opacity: 0.85
        }).addTo(pathLayerGroup);
    }

    // 2. Add markers for each step
    const stepColors = {
        arrival: 'bg-green-500 border-green-200 text-white',
        unloading: 'bg-amber-500 border-amber-200 text-white',
        departure: 'bg-red-500 border-red-200 text-white'
    };

    vessel.movements.forEach((movement, index) => {
        const colorClass = stepColors[movement.type] || 'bg-blue-500 text-white';
        const num = index + 1;
        
        const html = `
            <div class="relative flex items-center justify-center w-7 h-7 rounded-full border border-white shadow-lg ${colorClass} font-bold text-xs transform -translate-x-1/2 -translate-y-1/2">
                <span class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                </span>
                <span class="text-[10px]">${num}</span>
            </div>
        `;
        
        const divIcon = L.divIcon({
            html: html,
            className: 'custom-movement-icon',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
        
        const markerPopup = `
            <div class="p-2 min-w-[200px] text-gray-800 dark:text-gray-100">
                <div class="flex items-center space-x-1 mb-1">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${colorClass}">
                        Langkah ${num}: ${movement.type_label}
                    </span>
                </div>
                <h4 class="font-bold text-sm border-b pb-1 mb-1">${movement.site_name}</h4>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">${movement.time}</p>
                <div class="text-[10px] bg-gray-50 dark:bg-gray-800 p-1.5 rounded border border-gray-100 dark:border-gray-700 mt-1 leading-normal">
                    ${movement.details}
                </div>
            </div>
        `;
        
        L.marker([movement.latitude, movement.longitude], { icon: divIcon })
            .bindPopup(markerPopup)
            .addTo(pathLayerGroup);
    });

    // 3. Zoom/pan to fit path
    if (latlngs.length > 0) {
        const bounds = L.latLngBounds(latlngs);
        map.fitBounds(bounds, { padding: [80, 80], maxZoom: 18 });
    }
};

const clearSelectedVessel = () => {
    selectedVessel.value = null;
    if (pathLayerGroup) {
        pathLayerGroup.clearLayers();
    }
    
    // Zoom back to show all original site markers
    const validPositions = props.positions.filter(p => p.latitude && p.longitude);
    if (validPositions.length > 0 && markerGroup) {
        map.fitBounds(markerGroup.getBounds(), { padding: [50, 50], maxZoom: 18 });
    }
};

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

        // Initialize with default style (Google Maps)
        const defaultStyle = mapStyles.find(s => s.id === activeStyle.value);
        tileLayer = L.tileLayer(defaultStyle.url, {
            maxZoom: 20,
            attribution: defaultStyle.attribution
        }).addTo(map);

        renderMarkers(props.positions);

        // If multiple markers, fit bounds
        if (validPositions.length > 1 && markerGroup) {
            map.fitBounds(markerGroup.getBounds(), { padding: [50, 50], maxZoom: 18 });
        }

        // Setup popup open handler to bind clicks inside Leaflet popups dynamically
        map.on('popupopen', () => {
            const buttons = document.querySelectorAll('.btn-show-path');
            buttons.forEach(btn => {
                // Clear old event listener by replacing with clone
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const vesselId = parseInt(newBtn.getAttribute('data-vessel-id'));
                    const siteId = parseInt(newBtn.getAttribute('data-site-id'));
                    
                    const targetSite = props.positions.find(s => s.id === siteId);
                    if (targetSite) {
                        const targetVessel = targetSite.vessels.find(v => v.id === vesselId);
                        if (targetVessel) {
                            selectVessel(targetVessel);
                            map.closePopup();
                        }
                    }
                });
            });
        });

        // Force Leaflet to recalculate the map size after rendering
        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 300);

    } catch (error) {
        console.error("Map initialization failed:", error);
    }

    // Set interval to fetch updated data silently every 15 seconds
    refreshInterval = setInterval(() => {
        router.reload({
            only: ['positions'],
            preserveState: true,
            preserveScroll: true
        });
    }, 15000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    if (map) map.remove();
});

// Watch for prop changes to update map without full reload
watch(() => props.positions, (newPositions) => {
    renderMarkers(newPositions);
    
    // If a vessel is selected, update its state from the new positions array
    if (selectedVessel.value) {
        let found = false;
        for (const site of newPositions) {
            const match = site.vessels.find(v => v.id === selectedVessel.value.id);
            if (match) {
                // Update selected vessel data, but preserve the path on the map
                selectedVessel.value = match;
                found = true;
                break;
            }
        }
        if (!found) {
            clearSelectedVessel();
        }
    }
}, { deep: true });

</script>

<template>
    <Head title="Posisi Kapal" />

    <div class="space-y-4">
        <!-- Page Title -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Posisi Kapal (Real-time)</h1>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Peta sebaran kapal yang saat ini sedang berada di dermaga/tangkahan beserta histori perpindahannya.
                    </p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-3 py-1.5 rounded-xl text-[11px] font-bold border border-blue-100 dark:border-blue-800/30 flex items-center space-x-4">
                    <div class="flex items-center">
                        <span class="w-2.5 h-2.5 bg-blue-500 rounded-full mr-1.5"></span>
                        <span class="text-gray-700 dark:text-gray-300">{{ positions.reduce((acc, p) => acc + p.vessels.length, 0) }} Kapal di Pelabuhan</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full mr-1.5"></span>
                        <span class="text-gray-700 dark:text-gray-300">{{ positions.length }} Lokasi Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Area -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700">
            <div class="p-4 relative">
                <!-- Map Container -->
                <div 
                    ref="mapContainer" 
                    class="w-full h-[720px] rounded-xl z-0 relative shadow-inner bg-gray-100 dark:bg-gray-900"
                ></div>

                <!-- Map Style Selector Overlay (Google Maps type style) -->
                <div class="absolute bottom-6 left-6 z-[1000] flex bg-white dark:bg-gray-850 rounded-lg shadow-2xl border border-gray-250 dark:border-gray-700 p-1 space-x-1">
                    <button 
                        v-for="style in mapStyles" 
                        :key="style.id"
                        @click="changeMapStyle(style.id)"
                        class="px-2.5 py-1 text-[10px] font-bold rounded transition-all duration-200 cursor-pointer"
                        :class="activeStyle === style.id 
                            ? 'bg-blue-500 text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-350 hover:bg-gray-100 dark:hover:bg-gray-800'"
                    >
                        {{ style.name }}
                    </button>
                </div>

                <!-- Floating Detail Panel for Selected Vessel Path -->
                <div v-if="selectedVessel" class="absolute top-6 right-6 z-[1000] w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 p-4 max-h-[640px] overflow-y-auto transition-all duration-300">
                    <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-3">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white flex items-center text-sm">
                                <i class="ri-ship-2-fill text-blue-500 mr-2 text-base"></i>
                                {{ selectedVessel.name }}
                            </h3>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                Pemilik: {{ selectedVessel.owner }} | GT: {{ selectedVessel.gt }}
                            </p>
                        </div>
                        <button @click="clearSelectedVessel" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i class="ri-close-line text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 mb-3 tracking-wider">
                        DETAIL PERPINDAHAN KAPAL
                    </div>
                    
                    <!-- Stepper Timeline -->
                    <div v-if="selectedVessel.movements && selectedVessel.movements.length > 0" class="relative pl-6 space-y-4">
                        <!-- Vertical Line -->
                        <div class="absolute left-2 top-2 bottom-2 w-0.5 bg-gray-200 dark:bg-gray-750 border-dashed border-l"></div>
                        
                        <div v-for="(mv, idx) in selectedVessel.movements" :key="idx" class="relative group">
                            <!-- Icon/Indicator -->
                            <div class="absolute -left-[22px] top-1.5 w-3.5 h-3.5 rounded-full border-2 bg-white dark:bg-gray-800 flex items-center justify-center transition-all"
                                 :class="{
                                     'border-green-500': mv.type === 'arrival',
                                     'border-amber-500': mv.type === 'unloading',
                                     'border-red-500': mv.type === 'departure'
                                 }">
                                <div class="w-1.5 h-1.5 rounded-full animate-pulse"
                                     :class="{
                                         'bg-green-500': mv.type === 'arrival',
                                         'bg-amber-500': mv.type === 'unloading',
                                         'bg-red-500': mv.type === 'departure'
                                     }"></div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="font-bold text-gray-900 dark:text-white text-xs">
                                        {{ idx + 1 }}. {{ mv.type_label }}
                                    </span>
                                    <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap ml-2">
                                        {{ mv.time_relative }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-gray-700 dark:text-gray-300 font-semibold mt-0.5 flex items-center">
                                    <i class="ri-map-pin-line mr-1 text-[10px] text-blue-500"></i>
                                    {{ mv.site_name }}
                                </div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 bg-gray-50 dark:bg-gray-900/60 p-1.5 rounded border border-gray-100 dark:border-gray-850">
                                    {{ mv.details }}
                                </div>
                                <div class="text-[9px] text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ mv.time }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-gray-500 dark:text-gray-400 py-4 text-center">
                        Tidak ada riwayat perpindahan
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-750 flex justify-end">
                        <button @click="clearSelectedVessel" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs px-3 py-1.5 rounded-lg transition-colors font-medium">
                            Reset Peta
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend/Table -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="site in positions" :key="site.id" class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3 border-b border-gray-50 dark:border-gray-700 pb-2">
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="ri-map-pin-2-fill text-red-500 mr-2"></i>
                        {{ site.name }}
                    </h3>
                    <span class="bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] px-2 py-0.5 rounded-full font-bold">
                        {{ site.vessels.length }} Kapal
                    </span>
                </div>
                <div class="space-y-2">
                    <div v-for="v in site.vessels" :key="v.id" 
                         @click="selectVessel(v)"
                         class="flex flex-col text-xs p-2 rounded hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer border border-transparent hover:border-blue-100 dark:hover:border-gray-600 transition-all"
                         :class="{'bg-blue-50 dark:bg-gray-700 border-blue-200 dark:border-gray-600': selectedVessel && selectedVessel.id === v.id}">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                <i class="ri-ship-2-fill text-blue-500 mr-1"></i>
                                {{ v.name }}
                            </span>
                            <span class="text-[9px] px-1.5 py-0.5 rounded font-medium"
                                  :class="{
                                      'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': v.status === 'Persiapan Berangkat',
                                      'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': v.status !== 'Persiapan Berangkat'
                                  }">
                                {{ v.status }}
                            </span>
                        </div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 flex items-center justify-between">
                            <span><i class="ri-history-line mr-1 text-[9px]"></i> {{ v.arrival_time }}</span>
                            <span class="text-blue-600 dark:text-blue-400 hover:underline font-bold text-[9px] flex items-center">
                                Lihat Jalur <i class="ri-arrow-right-s-line ml-0.5"></i>
                            </span>
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
    height: 720px;
    width: 100%;
    border-radius: 0.75rem;
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

/* Dark mode overrides for map style selector */
.dark .dark\:bg-gray-855 {
    background-color: #151d2a;
}
</style>
