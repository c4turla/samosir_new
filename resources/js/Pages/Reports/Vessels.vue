<script setup>
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    vessels: Object,
    filters: Object,
    options: Object,
    summary: Object,
})

// Local filter state
const search = ref(props.filters.search || '')
const gtMin = ref(props.filters.gt_min || '')
const gtMax = ref(props.filters.gt_max || '')
const fishingGear = ref(props.filters.fishing_gear || '')
const vesselType = ref(props.filters.vessel_type || '')
const sipiStatus = ref(props.filters.sipi_status || '')

// Debounce timer
let searchTimer = null

// Apply filters
const applyFilters = () => {
    router.get('/reports/vessels', {
        search: search.value || undefined,
        gt_min: gtMin.value || undefined,
        gt_max: gtMax.value || undefined,
        fishing_gear: fishingGear.value || undefined,
        vessel_type: vesselType.value || undefined,
        sipi_status: sipiStatus.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Watch inputs with debounce
watch([search, gtMin, gtMax], () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 500)
})

// Reset filters
const resetFilters = () => {
    search.value = ''
    gtMin.value = ''
    gtMax.value = ''
    fishingGear.value = ''
    vesselType.value = ''
    sipiStatus.value = ''
    router.get('/reports/vessels', {}, { preserveState: true })
}

// Build export URL
const buildExportUrl = (type) => {
    const params = new URLSearchParams()
    if (search.value) params.append('search', search.value)
    if (gtMin.value) params.append('gt_min', gtMin.value)
    if (gtMax.value) params.append('gt_max', gtMax.value)
    if (fishingGear.value) params.append('fishing_gear', fishingGear.value)
    if (vesselType.value) params.append('vessel_type', vesselType.value)
    if (sipiStatus.value) params.append('sipi_status', sipiStatus.value)
    return `/reports/vessels/export-${type}?${params.toString()}`
}

// Go to page
const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const hasActiveFilters = computed(() => {
    return search.value || gtMin.value || gtMax.value || fishingGear.value || vesselType.value || sipiStatus.value
})
</script>

<template>
    <Head title="Laporan Data Kapal" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-ship-2-line text-blue-500"></i>
                    Laporan Data Kapal
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Database lengkap kapal penangkap ikan dengan filter GT dan alat tangkap.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    :href="buildExportUrl('excel')"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-xl border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all duration-200 shadow-sm"
                >
                    <i class="ri-file-excel-2-line mr-2 text-lg"></i>
                    Export Excel
                </a>
                <a
                    :href="buildExportUrl('pdf')"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200 shadow-sm"
                >
                    <i class="ri-file-pdf-2-line mr-2 text-lg"></i>
                    Export PDF
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Kapal</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ summary.total }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-ship-2-line text-blue-600 dark:text-blue-400 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rata-rata GT</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ summary.avg_gt }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-scales-line text-blue-600 dark:text-blue-400 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-filter-3-line text-gray-400"></i>
                    Filter Data Kapal
                </h3>
                <button
                    v-if="hasActiveFilters"
                    @click="resetFilters"
                    class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 flex items-center gap-1 transition-colors"
                >
                    <i class="ri-close-circle-line"></i>
                    Reset Filter
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Cari Kapal / Pemilik</label>
                    <div class="relative">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Nama kapal, pemilik, no izin..."
                            class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                        />
                    </div>
                </div>

                <!-- GT Range -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Min GT</label>
                    <input
                        v-model="gtMin"
                        type="number"
                        placeholder="0"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Max GT</label>
                    <input
                        v-model="gtMax"
                        type="number"
                        placeholder="Any"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    />
                </div>

                <!-- Fishing Gear -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Alat Tangkap</label>
                    <select
                        v-model="fishingGear"
                        @change="applyFilters"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    >
                        <option value="">Semua</option>
                        <option v-for="gear in options.fishing_gears" :key="gear" :value="gear">{{ gear }}</option>
                    </select>
                </div>

                <!-- Vessel Type -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Jenis Kapal</label>
                    <select
                        v-model="vesselType"
                        @change="applyFilters"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    >
                        <option value="">Semua</option>
                        <option v-for="type in options.vessel_types" :key="type" :value="type">{{ type }}</option>
                    </select>
                </div>

                <!-- SIPI Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status SIPI</label>
                    <select
                        v-model="sipiStatus"
                        @change="applyFilters"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    >
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/80">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Kapal</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemilik</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">GT</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alat Tangkap</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">SIPI Akhir</th>
                            <th class="px-4 py-3 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <tr
                            v-for="(vessel, index) in vessels.data"
                            :key="vessel.id"
                            class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                        >
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ (vessels.current_page - 1) * vessels.per_page + index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-bold text-gray-900 dark:text-white uppercase">{{ vessel.vessel_name }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ vessel.license_number || vessel.selar_mark || '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">{{ vessel.owner_name || '-' }}</td>
                            <td class="px-4 py-3 text-xs font-mono font-bold text-blue-600 dark:text-blue-400">{{ vessel.gt || 0 }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">{{ vessel.fishing_gear || '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                {{ vessel.sipi_end_date ? new Date(vessel.sipi_end_date).toLocaleDateString('id-ID') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-bold inline-block',
                                        vessel.sipi_status === 'active' 
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' 
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                    ]"
                                >
                                    {{ vessel.sipi_status_text }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="vessels.data.length === 0" class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="ri-search-2-line text-2xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tidak ada hasil</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba sesuaikan filter pencarian Anda.</p>
            </div>

            <!-- Pagination -->
            <div v-if="vessels.data.length > 0" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ vessels.from }} s/d {{ vessels.to }} dari {{ vessels.total }} kapal
                </p>
                <div class="flex items-center gap-1">
                    <button
                        v-for="link in vessels.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url"
                        :class="[
                            'px-3 py-1.5 text-xs rounded-lg transition-all duration-200 font-medium',
                            link.active
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-30'
                        ]"
                        v-html="link.label"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</template>
