<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    vessels: {
        type: Array,
        required: true
    },
    landingSites: {
        type: Array,
        required: true
    },
    fishSpecies: {
        type: Array,
        required: true
    }
})

const catches = ref([])

// Searchable custom dropdown (Select2 style) for Vessels
const isDropdownOpen = ref(false)
const vesselSearch = ref('')

const filteredVessels = computed(() => {
    if (!vesselSearch.value) return props.vessels
    const query = vesselSearch.value.toLowerCase().trim()
    return props.vessels.filter(vessel => 
        vessel.vessel_name.toLowerCase().includes(query) || 
        (vessel.selar_mark && vessel.selar_mark.toLowerCase().includes(query))
    )
})

const selectedVessel = computed(() => {
    return props.vessels.find(vessel => vessel.id === form.vessel_id)
})

const selectVessel = (vesselId) => {
    form.vessel_id = vesselId
    isDropdownOpen.value = false
    vesselSearch.value = ''
}

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value
}

// Searchable custom dropdown for Landing Sites (Dermaga)
const isLandingSiteDropdownOpen = ref(false)
const landingSiteSearch = ref('')

const filteredLandingSites = computed(() => {
    if (!landingSiteSearch.value) return props.landingSites
    const query = landingSiteSearch.value.toLowerCase().trim()
    return props.landingSites.filter(site => 
        site.site_name.toLowerCase().includes(query)
    )
})

const selectedLandingSite = computed(() => {
    return props.landingSites.find(site => site.id === form.landing_site_id)
})

const selectLandingSite = (siteId) => {
    form.landing_site_id = siteId
    isLandingSiteDropdownOpen.value = false
    landingSiteSearch.value = ''
}

const toggleLandingSiteDropdown = () => {
    isLandingSiteDropdownOpen.value = !isLandingSiteDropdownOpen.value
}

// Searchable custom dropdown for dynamic Fish Species (Jenis Ikan) rows
const openCatchDropdownIndex = ref(null)
const fishSearch = ref('')

const getFilteredAvailableFishSpecies = (index) => {
    const available = getAvailableFishSpecies(index)
    if (!fishSearch.value) return available
    const query = fishSearch.value.toLowerCase().trim()
    return available.filter(fish => 
        fish.species_name.toLowerCase().includes(query) ||
        (fish.local_name && fish.local_name.toLowerCase().includes(query))
    )
}

const getSelectedFishSpecies = (fishId) => {
    return props.fishSpecies.find(fish => fish.id === fishId)
}

const selectFish = (index, fishId) => {
    catches.value[index].fish_species_id = fishId
    openCatchDropdownIndex.value = null
    fishSearch.value = ''
}

const toggleCatchDropdown = (index) => {
    if (openCatchDropdownIndex.value === index) {
        openCatchDropdownIndex.value = null
    } else {
        openCatchDropdownIndex.value = index
        fishSearch.value = ''
    }
}

const form = useForm({
    vessel_id: '',
    nakhoda_name: '',
    origin: '',
    arrival_date: new Date().toISOString().split('T')[0],
    arrival_time: '',
    landing_site_id: '',
    mutu: '',
    fish_quality: '',
    average_price: '',
    waste_volume: '',
    fish_temperature: '',
    hold_temperature: '',
    status: 'TAMBAT',
    approval_status: false,
    notes: '',
    is_processed: false,
    catches: [],
})

const addCatch = () => {
    catches.value.push({
        fish_species_id: '',
        weight_kg: '',
    })
}

const removeCatch = (index) => {
    catches.value.splice(index, 1)
}

const getAvailableFishSpecies = (currentIndex) => {
    const selectedIds = catches.value
        .filter((catchItem, index) => index !== currentIndex && catchItem.fish_species_id)
        .map(catchItem => catchItem.fish_species_id)
    return props.fishSpecies.filter(fish => !selectedIds.includes(fish.id))
}

const submit = () => {
    const validCatches = catches.value.filter(catchItem => catchItem.fish_species_id && catchItem.weight_kg)
    form.catches = validCatches
    form.post('/arrivals', {
        onSuccess: () => {
            form.reset()
            catches.value = []
        }
    })
}

const handlePriceInput = (event) => {
    const value = event.target.value.replace(/\D/g, '')
    form.average_price = value
}

const cancel = () => {
    form.reset()
    catches.value = []
    window.location.href = '/arrivals'
}
</script>

<template>
    <AppLayout>
        <Head title="Tambah Kedatangan Kapal - SAMOSIR" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Kedatangan Kapal</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Catat kedatangan kapal baru di pelabuhan
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <form @submit.prevent="submit">
                    <!-- Informasi Kapal -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Informasi Kapal
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kapal <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <!-- Trigger Button -->
                                    <button
                                        type="button"
                                        @click="toggleDropdown"
                                        :class="[
                                            'w-full px-3 py-2 border rounded-lg text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center justify-between transition-colors shadow-sm',
                                            form.errors.vessel_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                            'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                        ]"
                                    >
                                        <span v-if="selectedVessel" class="truncate font-medium">
                                            {{ selectedVessel.vessel_name }} ({{ selectedVessel.selar_mark }})
                                        </span>
                                        <span v-else class="text-gray-400 dark:text-gray-400">
                                            Pilih Kapal
                                        </span>
                                        <i class="ri-arrow-down-s-line text-lg text-gray-400"></i>
                                    </button>

                                    <!-- Invisible backdrop to close dropdown when clicked outside -->
                                    <div v-if="isDropdownOpen" class="fixed inset-0 z-30" @click="isDropdownOpen = false"></div>

                                    <!-- Dropdown panel -->
                                    <div 
                                        v-if="isDropdownOpen" 
                                        class="absolute left-0 z-40 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden animate-fadeIn"
                                    >
                                        <!-- Search input -->
                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center">
                                            <i class="ri-search-line text-gray-400 ml-2 mr-2"></i>
                                            <input
                                                type="text"
                                                v-model="vesselSearch"
                                                placeholder="Ketik untuk mencari kapal..."
                                                class="w-full bg-transparent border-0 focus:ring-0 p-1 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none"
                                                @keyup.esc="isDropdownOpen = false"
                                            />
                                            <button 
                                                v-if="vesselSearch"
                                                type="button"
                                                @click="vesselSearch = ''"
                                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                            >
                                                <i class="ri-close-line text-sm"></i>
                                            </button>
                                        </div>

                                        <!-- Options list -->
                                        <ul class="max-h-60 overflow-y-auto py-1 divide-y divide-gray-50 dark:divide-gray-700/50">
                                            <li
                                                v-for="vessel in filteredVessels"
                                                :key="vessel.id"
                                                @click="selectVessel(vessel.id)"
                                                :class="[
                                                    'px-4 py-2 text-sm cursor-pointer transition-colors flex items-center justify-between',
                                                    form.vessel_id === vessel.id
                                                        ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold'
                                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/30'
                                                ]"
                                            >
                                                <div class="truncate">
                                                    <span class="font-medium">{{ vessel.vessel_name }}</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">({{ vessel.selar_mark }})</span>
                                                </div>
                                                <i v-if="form.vessel_id === vessel.id" class="ri-check-line text-blue-500 dark:text-blue-400"></i>
                                            </li>

                                            <!-- No results state -->
                                            <li v-if="filteredVessels.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                Kapal tidak ditemukan
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Nakhoda</label>
                                <input
                                    v-model="form.nakhoda_name"
                                    type="text"
                                    placeholder="Nama Nakhoda"
                                    :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.nakhoda_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Asal</label>
                                <input
                                    v-model="form.origin"
                                    type="text"
                                    placeholder="Contoh: Laut Jawa"
                                    :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.origin ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Waktu Kedatangan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Waktu Kedatangan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input v-model="form.arrival_date" type="date" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.arrival_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" required />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu</label>
                                <input v-model="form.arrival_time" type="time" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.arrival_time ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" />
                            </div>
                        </div>
                    </div>

                    <!-- Dermaga -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Lokasi Dermaga</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dermaga</label>
                            <div class="relative">
                                <!-- Trigger Button -->
                                <button
                                    type="button"
                                    @click="toggleLandingSiteDropdown"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center justify-between transition-colors shadow-sm',
                                        form.errors.landing_site_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                >
                                    <span v-if="selectedLandingSite" class="truncate font-medium">
                                        {{ selectedLandingSite.site_name }}
                                    </span>
                                    <span v-else class="text-gray-400 dark:text-gray-400">
                                        Pilih Dermaga
                                    </span>
                                    <i class="ri-arrow-down-s-line text-lg text-gray-400"></i>
                                </button>

                                <!-- Invisible backdrop -->
                                <div v-if="isLandingSiteDropdownOpen" class="fixed inset-0 z-30" @click="isLandingSiteDropdownOpen = false"></div>

                                <!-- Dropdown panel -->
                                <div 
                                    v-if="isLandingSiteDropdownOpen" 
                                    class="absolute left-0 z-40 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden animate-fadeIn"
                                >
                                    <!-- Search input -->
                                    <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center">
                                        <i class="ri-search-line text-gray-400 ml-2 mr-2"></i>
                                        <input
                                            type="text"
                                            v-model="landingSiteSearch"
                                            placeholder="Ketik untuk mencari dermaga..."
                                            class="w-full bg-transparent border-0 focus:ring-0 p-1 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none"
                                            @keyup.esc="isLandingSiteDropdownOpen = false"
                                        />
                                        <button 
                                            v-if="landingSiteSearch"
                                            type="button"
                                            @click="landingSiteSearch = ''"
                                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                        >
                                            <i class="ri-close-line text-sm"></i>
                                        </button>
                                    </div>

                                    <!-- Options list -->
                                    <ul class="max-h-60 overflow-y-auto py-1 divide-y divide-gray-50 dark:divide-gray-700/50">
                                        <li
                                            v-for="site in filteredLandingSites"
                                            :key="site.id"
                                            @click="selectLandingSite(site.id)"
                                            :class="[
                                                'px-4 py-2 text-sm cursor-pointer transition-colors flex items-center justify-between',
                                                form.landing_site_id === site.id
                                                    ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/30'
                                            ]"
                                        >
                                            <span class="font-medium">{{ site.site_name }}</span>
                                            <i v-if="form.landing_site_id === site.id" class="ri-check-line text-blue-500 dark:text-blue-400"></i>
                                        </li>

                                        <!-- No results state -->
                                        <li v-if="filteredLandingSites.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                                            Dermaga tidak ditemukan
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ikan Hasil Tangkapan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Ikan Hasil Tangkapan</h3>
                        <div class="space-y-3">
                            <div v-if="catches.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-xs">
                                Belum ada ikan hasil tangkapan ditambahkan
                            </div>
                            <div v-for="(catchItem, index) in catches" :key="index" class="flex items-end gap-2 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Ikan</label>
                                    <div class="relative">
                                        <!-- Trigger Button -->
                                        <button
                                            type="button"
                                            @click="toggleCatchDropdown(index)"
                                            :class="[
                                                'w-full px-3 py-2 border rounded-lg text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center justify-between transition-colors shadow-sm',
                                                'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                                'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                            ]"
                                        >
                                            <span v-if="catchItem.fish_species_id && getSelectedFishSpecies(catchItem.fish_species_id)" class="truncate font-medium">
                                                {{ getSelectedFishSpecies(catchItem.fish_species_id).species_name }}
                                            </span>
                                            <span v-else class="text-gray-400 dark:text-gray-400">
                                                Pilih Ikan
                                            </span>
                                            <i class="ri-arrow-down-s-line text-lg text-gray-400"></i>
                                        </button>

                                        <!-- Invisible backdrop -->
                                        <div v-if="openCatchDropdownIndex === index" class="fixed inset-0 z-30" @click="openCatchDropdownIndex = null"></div>

                                        <!-- Dropdown panel -->
                                        <div 
                                            v-if="openCatchDropdownIndex === index" 
                                            class="absolute left-0 z-40 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden animate-fadeIn"
                                        >
                                            <!-- Search input -->
                                            <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center">
                                                <i class="ri-search-line text-gray-400 ml-2 mr-2"></i>
                                                <input
                                                    type="text"
                                                    v-model="fishSearch"
                                                    placeholder="Ketik untuk mencari jenis ikan..."
                                                    class="w-full bg-transparent border-0 focus:ring-0 p-1 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none"
                                                    @keyup.esc="openCatchDropdownIndex = null"
                                                />
                                                <button 
                                                    v-if="fishSearch"
                                                    type="button"
                                                    @click="fishSearch = ''"
                                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                >
                                                    <i class="ri-close-line text-sm"></i>
                                                </button>
                                            </div>

                                            <!-- Options list -->
                                            <ul class="max-h-60 overflow-y-auto py-1 divide-y divide-gray-50 dark:divide-gray-700/50">
                                                <li
                                                    v-for="fish in getFilteredAvailableFishSpecies(index)"
                                                    :key="fish.id"
                                                    @click="selectFish(index, fish.id)"
                                                    :class="[
                                                        'px-4 py-2 text-sm cursor-pointer transition-colors flex items-center justify-between',
                                                        catchItem.fish_species_id === fish.id
                                                            ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold'
                                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/30'
                                                    ]"
                                                >
                                                    <span class="font-medium">{{ fish.species_name }}</span>
                                                    <i v-if="catchItem.fish_species_id === fish.id" class="ri-check-line text-blue-500 dark:text-blue-400"></i>
                                                </li>

                                                <!-- No results state -->
                                                <li v-if="getFilteredAvailableFishSpecies(index).length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                    Jenis ikan tidak ditemukan
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Berat (kg)</label>
                                    <input v-model="catchItem.weight_kg" type="number" min="0" step="1" placeholder="0" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" />
                                </div>
                                <button type="button" @click="removeCatch(index)" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors text-xs mb-0.5">
                                    Hapus
                                </button>
                            </div>
                            <button type="button" @click="addCatch" class="w-full px-3 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-500 text-gray-700 dark:text-gray-300 rounded-lg transition-colors text-xs flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Ikan
                            </button>
                        </div>
                    </div>

                    <!-- Informasi Ikan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Informasi Tambahan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Produk</label>
                                <select 
                                    v-model="form.fish_quality" 
                                    :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.fish_quality ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']"
                                >
                                    <option value="" disabled>Pilih Produk</option>
                                    <option value="Segar">Segar</option>
                                    <option value="Beku">Beku</option>
                                    <option value="Olahan">Olahan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Rata-rata (Rp)</label>
                                <input v-model="form.average_price" type="number" placeholder="0" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.average_price ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Volume Limbah (kg)</label>
                                <input v-model="form.waste_volume" type="number" min="0" placeholder="0" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.waste_volume ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suhu Ikan (°C)</label>
                                <input v-model="form.fish_temperature" type="number" placeholder="0" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.fish_temperature ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suhu Palka (°C)</label>
                                <input v-model="form.hold_temperature" type="number" placeholder="0" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.hold_temperature ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" />
                            </div>
                                                        <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Mutu</label>
                                <select 
                                    v-model="form.mutu" 
                                    :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.mutu ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']"
                                >
                                    <option value="" disabled>Pilih Mutu</option>
                                    <option value="I">I</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status Kedatangan <span class="text-red-500">*</span></label>
                                <select v-model="form.status" :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.status ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']" required>
                                    <option value="TAMBAT">Tambat</option>
                                    <option value="LABUH">Labuh</option>
                                    <option value="BONGKAR">Bongkar</option>
                                    <option value="MENGISI PERBEKALAN">Mengisi Perbekalan</option>
                                    <option value="PERBAIKAN">Perbaikan</option>
                                    <option value="SELESAI">Selesai</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input v-model="form.is_processed" type="checkbox" class="sr-only peer" />
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:bg-blue-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                    <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">Sudah Diproses</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Catatan</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan</label>
                            <textarea v-model="form.notes" rows="3" placeholder="Tambahkan catatan..." :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none', form.errors.notes ? 'border-red-500' : 'border-gray-300 dark:border-gray-600', 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white']"></textarea>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm disabled:opacity-50 flex items-center gap-2">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>