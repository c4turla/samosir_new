<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    arrivals: {
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
    suggestedReference: {
        type: String,
        required: true
    }
})

const form = useForm({
    arrival_id: '',
    reference_number: props.suggestedReference,
    syahbandar_id: '',
    captain_name: '',
    identification_mark: '',
    unloading_date: new Date().toISOString().split('T')[0],
    unloading_time: '',
    sequence_number: '',
    registration_date: new Date().toISOString().split('T')[0],
    bl_code: '',
    landing_site_id: '',
    notes: '',
})

// Dropdown search state
const vesselSearch = ref('')
const showVesselDropdown = ref(false)
const selectedArrival = ref(null)
const dropdownContainer = ref(null)

// Searchable custom dropdown (Select2 style) for Landing Sites (Lokasi Penimbangan)
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

const submit = () => {
    form.post('/unloadings', {
        onSuccess: () => {
            form.reset()
            selectedArrival.value = null
            vesselSearch.value = ''
        }
    })
}

const cancel = () => {
    form.reset()
    selectedArrival.value = null
    vesselSearch.value = ''
    window.location.href = '/unloadings'
}

const filteredArrivals = computed(() => {
    if (!vesselSearch.value) return props.arrivals
    const search = vesselSearch.value.toLowerCase()
    return props.arrivals.filter(a => 
        a.vessel?.vessel_name?.toLowerCase().includes(search) ||
        a.vessel?.license_number?.toLowerCase().includes(search)
    )
})

const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    try {
        const d = new Date(dateStr)
        if (isNaN(d.getTime())) return dateStr
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
    } catch {
        return dateStr
    }
}

const handleArrivalChange = (arrival, event) => {
    if (event) event.stopPropagation()
    form.arrival_id = arrival.id
    selectedArrival.value = arrival
    vesselSearch.value = `${arrival.vessel?.vessel_name} - ${arrival.vessel?.license_number} (${formatDate(arrival.arrival_date)})`
    showVesselDropdown.value = false
    
    // Auto-fill some fields from arrival data
    form.landing_site_id = arrival.landing_site_id || ''
    form.identification_mark = arrival.vessel?.selar_mark || ''
}

const clearArrival = (event) => {
    if (event) event.stopPropagation()
    form.arrival_id = ''
    selectedArrival.value = null
    vesselSearch.value = ''
    form.landing_site_id = ''
    form.identification_mark = ''
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (dropdownContainer.value && !dropdownContainer.value.contains(event.target)) {
        showVesselDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <AppLayout>
        <Head title="Tambah Penimbangan Ikan - SAMOSIR" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Penimbangan Ikan</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Catat penimbangan ikan baru di pelabuhan
                </p>
            </div>

            <!-- Status Info -->
            <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-200">
                    <i class="ri-information-line text-lg"></i>
                    <p class="text-[10px] font-medium uppercase tracking-wider">
                        Status Awal: <strong>PENDING</strong> (Memerlukan Persetujuan Syahbandar)
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <form @submit.prevent="submit">
                    <!-- Data Kapal -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Data Kapal
                        </h3>
                        <div class="md:col-span-2 relative" ref="dropdownContainer">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Kapal <span class="text-red-500">*</span>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Hanya kapal yang sudah selesai kedatangan dan belum dilakukan penimbangan</p>
                            </label>
                            <div class="relative">
                                <input
                                    v-model="vesselSearch"
                                    @focus="showVesselDropdown = true"
                                    @click="showVesselDropdown = true"
                                    placeholder="Cari kapal atau nomor..."
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.arrival_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <button
                                    v-if="selectedArrival"
                                    @click="clearArrival($event)"
                                    type="button"
                                    class="absolute right-10 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                                
                                <!-- Dropdown Options -->
                                <div
                                    v-if="showVesselDropdown && filteredArrivals.length > 0"
                                    class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <div
                                        v-for="arrival in filteredArrivals"
                                        :key="arrival.id"
                                        @click="handleArrivalChange(arrival, $event)"
                                        class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm text-gray-900 dark:text-white"
                                    >
                                        <div class="font-medium">{{ arrival.vessel?.vessel_name }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                            {{ arrival.vessel?.license_number }} - {{ formatDate(arrival.arrival_date) }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="showVesselDropdown && filteredArrivals.length === 0"
                                    class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Tidak ada kapal yang ditemukan
                                </div>
                            </div>
                            <input type="hidden" v-model="form.arrival_id" required />
                            <p v-if="form.errors.arrival_id" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.arrival_id }}</p>
                        </div>
                    </div>

                    <!-- Informasi Dokumen -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Informasi Dokumen
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Surat
                                </label>
                                <input
                                    v-model="form.reference_number"
                                    type="text"
                                    placeholder="Contoh: 340-0007-SPLP-IV-2026"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.reference_number ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono'
                                    ]"
                                />
                                <p v-if="form.errors.reference_number" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.reference_number }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Syahbandar <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.syahbandar_id"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.syahbandar_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                >
                                    <option value="">Pilih Syahbandar</option>
                                    <option v-for="syahbandar in syahbandars" :key="syahbandar.id" :value="syahbandar.id">
                                        {{ syahbandar.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.syahbandar_id" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.syahbandar_id }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Nakhoda/Nelayan
                                </label>
                                <input
                                    v-model="form.captain_name"
                                    type="text"
                                    placeholder="Nama nakhoda atau nelayan"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.captain_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.captain_name" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.captain_name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanda Pengenal Kapal/Tanda Selar
                                </label>
                                <input
                                    v-model="form.identification_mark"
                                    type="text"
                                    placeholder="Tanda pengenal kapal atau tanda selar"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.identification_mark ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.identification_mark" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.identification_mark }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Waktu Bongkar -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Waktu Bongkar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Bongkar <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.unloading_date"
                                    type="date"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.unloading_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <p v-if="form.errors.unloading_date" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.unloading_date }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jam Bongkar</label>
                                <input
                                    v-model="form.unloading_time"
                                    type="time"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.unloading_time ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.unloading_time" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.unloading_time }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No Urut Bongkar</label>
                                <input
                                    v-model="form.sequence_number"
                                    type="text"
                                    placeholder="Nomor urut bongkar"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.sequence_number ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.sequence_number" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.sequence_number }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Surat <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.registration_date"
                                    type="date"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.registration_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <p v-if="form.errors.registration_date" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.registration_date }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Informasi Tambahan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor STBL Kedatangan</label>
                                <input
                                    v-model="form.bl_code"
                                    type="text"
                                    placeholder="Nomor STBL kedatangan"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.bl_code ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.bl_code" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.bl_code }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasi Penimbangan</label>
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
                                            Pilih Lokasi Penimbangan
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
                                                placeholder="Ketik untuk mencari lokasi..."
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
                                                Lokasi tidak ditemukan
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <p v-if="form.errors.landing_site_id" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.landing_site_id }}</p>
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
