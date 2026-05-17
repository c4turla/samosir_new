<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps({
    vessels: {
        type: Array,
        required: true
    },
    landingSites: {
        type: Array,
        required: true
    },
    syahbandars: {
        type: Array,
        required: true
    },
    next_nomor: {
        type: String,
        required: true
    }
})

// Searchable custom dropdown (Select2 style) for Vessels
const isDropdownOpen = ref(false)
const vesselSearch = ref('')

const filteredVessels = computed(() => {
    if (!vesselSearch.value) return props.vessels
    const query = vesselSearch.value.toLowerCase().trim()
    return props.vessels.filter(vessel => 
        vessel.vessel_name.toLowerCase().includes(query) || 
        (vessel.license_number && vessel.license_number.toLowerCase().includes(query))
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

// Searchable custom dropdown (Select2 style) for Landing Sites (Dermaga)
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

const form = useForm({
    vessel_id: '',
    nakhoda_name: '',
    destination: '',
    crew_count: null,
    arrival_datetime: '',
    departure_date: new Date().toISOString().split('T')[0],
    departure_time: '',
    departure_datetime: '',
    landing_site_id: '',
    syahbandar: '',
    ice_supply: null,
    water_supply: null,
    diesel_supply: null,
    oil_supply: null,
    gasoline_supply: null,
    other_supplies: '',
    notes: '',
    floating_status: '',
    unloading_status: '',
    admin_completion: '',
    status: 'Sesuai Jadwal',
    etmal_days: 0,
    etmal_hours: '0',
    approval_status: false,
})

const submit = () => {
    form.post('/departures', {
        onSuccess: () => {
            form.reset()
        }
    })
}

const cancel = () => {
    form.reset()
    window.location.href = '/departures'
}

// Watcher for real-time Etmal calculation
watch([() => form.arrival_datetime, () => form.departure_datetime], () => {
    if (!form.arrival_datetime || !form.departure_datetime) {
        form.etmal_days = 0
        form.etmal_hours = 0
        return
    }
    
    const arrival = new Date(form.arrival_datetime)
    const departure = new Date(form.departure_datetime)
    
    if (isNaN(arrival.getTime()) || isNaN(departure.getTime())) {
        form.etmal_days = 0
        form.etmal_hours = 0
        return
    }
    
    const diffMs = departure - arrival
    if (diffMs <= 0) {
        form.etmal_days = 0
        form.etmal_hours = '0'
        return
    }
    
    const totalMinutes = Math.floor(diffMs / (1000 * 60))
    const diffHours = Math.floor(totalMinutes / 60)
    const remainingHours = diffHours % 24
    const remainingMinutes = totalMinutes % 60
    
    let etcDays = Math.floor(diffHours / 24)
    let etcHours = "0"
    
    if (remainingHours > 0 || remainingMinutes > 0) {
        if (remainingHours <= 6) {
            etcHours = "0.25"
        } else if (remainingHours <= 12) {
            etcHours = "0.50"
        } else if (remainingHours <= 18) {
            etcHours = "0.75"
        } else {
            etcDays += 1
            etcHours = "0"
        }
    }
    
    form.etmal_days = parseFloat((etcDays + parseFloat(etcHours)).toFixed(2))
    const totalMin = Math.floor(diffMs / (1000 * 60))
    const hours = Math.floor(totalMin / 60)
    const minutes = totalMin % 60
    form.etmal_hours = `${hours} Jam ${minutes} Menit`
})

const etmal_decimal = computed(() => form.etmal_days)
const etmal_text = computed(() => form.etmal_hours)
</script>

<template>
    <AppLayout>
        <Head title="Catat Keberangkatan - SAMOSIR" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Keberangkatan Kapal</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Catat keberangkatan kapal baru di pelabuhan
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
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor SKP</label>
                                <input
                                    :value="next_nomor"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                                    readonly
                                />
                                <p class="mt-1 text-[10px] text-gray-500 italic">* Nomor digenerate otomatis oleh sistem</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kapal <span class="text-red-500">*</span>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Hanya kapal yang sudah pernah kedatangan yang dapat dipilih</p>
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
                                            {{ selectedVessel.vessel_name }} ({{ selectedVessel.license_number }})
                                        </span>
                                        <span v-else class="text-gray-400 dark:text-gray-400">
                                            Pilih Kapal
                                        </span>
                                        <i class="ri-arrow-down-s-line text-lg text-gray-400"></i>
                                    </button>

                                    <!-- Invisible backdrop -->
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
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">({{ vessel.license_number }})</span>
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
                                <p v-if="form.errors.vessel_id" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.vessel_id }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Nakhoda
                                </label>
                                <input
                                    v-model="form.nakhoda_name"
                                    type="text"
                                    placeholder="Nama Nakhoda"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.nakhoda_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.nakhoda_name" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.nakhoda_name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tujuan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.destination"
                                    type="text"
                                    placeholder="Contoh: Laut Jawa"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.destination ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <p v-if="form.errors.destination" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.destination }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Jumlah ABK
                                </label>
                                <input
                                    v-model.number="form.crew_count"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.crew_count ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.crew_count" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.crew_count }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Waktu Keberangkatan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Waktu Kedatangan & Keberangkatan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal & Waktu Masuk <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.arrival_datetime"
                                    type="datetime-local"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.arrival_datetime ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <p v-if="form.errors.arrival_datetime" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.arrival_datetime }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal & Waktu Keberangkatan <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.departure_datetime"
                                    type="datetime-local"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.departure_datetime ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <p v-if="form.errors.departure_datetime" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.departure_datetime }}</p>
                            </div>
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                <div class="flex items-center gap-4">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 min-w-[80px]">Tambat</label>
                                    <div class="flex-1 flex border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700">
                                        <div class="flex-1 px-3 py-2 text-sm text-gray-900 dark:text-white border-r border-gray-300 dark:border-gray-600">{{ etmal_decimal }}</div>
                                        <div class="bg-gray-100 dark:bg-gray-600 px-3 py-2 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center">Etmal</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 min-w-[80px]">Total Jam</label>
                                    <div class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white">
                                        {{ etmal_text }}
                                    </div>
                                </div>
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
                            <p v-if="form.errors.landing_site_id" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.landing_site_id }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Informasi Petugas</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Syahbandar</label>
                            <select
                                v-model="form.syahbandar"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.syahbandar ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            >
                                <option value="">Pilih Syahbandar</option>
                                <option v-for="user in syahbandars" :key="user.id" :value="user.name">
                                    {{ user.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.syahbandar" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.syahbandar }}</p>
                        </div>
                    </div>

                    <!-- Status Kegiatan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Status Kegiatan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Floating</label>
                                <input
                                    v-model="form.floating_status"
                                    type="text"
                                    placeholder="Status Floating"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.floating_status ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.floating_status" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.floating_status }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bongkar Ikan</label>
                                <input
                                    v-model="form.unloading_status"
                                    type="text"
                                    placeholder="Status Bongkar Ikan"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.unloading_status ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.unloading_status" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.unloading_status }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Penyelesaian Administrasi</label>
                                <select
                                    v-model="form.admin_completion"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.admin_completion ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                >
                                    <option value="">- Pilih Administrasi -</option>
                                    <option value="Cek Poin">Cek Poin</option>
                                    <option value="Cek Fisik">Cek Fisik</option>
                                    <option value="Surat Keterangan">Surat Keterangan</option>
                                    <option value="Perberkalan">Perberkalan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <p v-if="form.errors.admin_completion" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.admin_completion }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Suplai Kapal -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Info Muatan / Data Logistik</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Es (kg)</label>
                                <input
                                    v-model.number="form.ice_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.ice_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.ice_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.ice_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Air (liter)</label>
                                <input
                                    v-model.number="form.water_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.water_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.water_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.water_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Solar (liter)</label>
                                <input
                                    v-model.number="form.diesel_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.diesel_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.diesel_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.diesel_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Olie (liter)</label>
                                <input
                                    v-model.number="form.oil_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.oil_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.oil_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.oil_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Umpan</label>
                                <input
                                    v-model.number="form.gasoline_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.gasoline_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.gasoline_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.gasoline_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Lain-lain</label>
                                <input
                                    v-model="form.other_supplies"
                                    type="text"
                                    placeholder="Suplai lainnya"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.other_supplies ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.other_supplies" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.other_supplies }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status Keberangkatan <span class="text-red-500">*</span></label>
                                <select
                                    v-model="form.status"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.status ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                >
                                    <option value="">- Pilih Status -</option>
                                    <option value="Sesuai Jadwal">Sesuai Jadwal</option>
                                    <option value="Pembatalan">Pembatalan</option>
                                    <option value="Ditunda">Ditunda</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Catatan</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Tambahkan catatan..."
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none',
                                    form.errors.notes ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            ></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.notes }}</p>
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
