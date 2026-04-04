<script setup>
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    arrivals: Object,
    filters: Object,
    landingSites: Array,
    summary: Object,
})

// Local filter state
const search = ref(props.filters.search || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const status = ref(props.filters.status || '')
const landingSiteId = ref(props.filters.landing_site_id || '')

// Debounce timer
let searchTimer = null

// Apply filters
const applyFilters = () => {
    router.get('/reports/arrivals', {
        search: search.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        status: status.value || undefined,
        landing_site_id: landingSiteId.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Watch search input with debounce
watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
})

// Reset filters
const resetFilters = () => {
    search.value = ''
    dateFrom.value = ''
    dateTo.value = ''
    status.value = ''
    landingSiteId.value = ''
    router.get('/reports/arrivals', {}, { preserveState: true })
}

// Format date to Indonesian format
const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    try {
        const d = new Date(dateStr)
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
    } catch { return dateStr }
}

// Format time
const formatTime = (timeStr) => {
    if (!timeStr) return '-'
    try {
        const d = new Date(timeStr)
        if (!isNaN(d.getTime())) return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        return timeStr
    } catch { return timeStr }
}

// Format currency
const formatCurrency = (val) => {
    if (!val) return 'Rp 0'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)
}

// Status badge classes
const getStatusClass = (s) => {
    const map = {
        'BERLABUH': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'BONGKAR': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'SELESAI': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    }
    return map[s] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
}

// Build export URL with current filters
const buildExportUrl = (type) => {
    const params = new URLSearchParams()
    if (dateFrom.value) params.append('date_from', dateFrom.value)
    if (dateTo.value) params.append('date_to', dateTo.value)
    if (status.value) params.append('status', status.value)
    if (landingSiteId.value) params.append('landing_site_id', landingSiteId.value)
    if (search.value) params.append('search', search.value)
    return `/reports/arrivals/export-${type}?${params.toString()}`
}

// Go to page
const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Has active filters
const hasActiveFilters = computed(() => {
    return search.value || status.value || landingSiteId.value
})
</script>

<template>
    <Head title="Laporan Kedatangan" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-file-chart-line text-blue-500"></i>
                    Laporan Kedatangan
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Lihat dan unduh data kedatangan kapal dalam format Excel atau PDF.
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
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Kedatangan</p>
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
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Berlabuh</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ summary.statusCounts?.BERLABUH || 0 }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-anchor-line text-blue-600 dark:text-blue-400 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bongkar</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ summary.statusCounts?.BONGKAR || 0 }}</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-download-cloud-2-line text-amber-600 dark:text-amber-400 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Selesai</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ summary.statusCounts?.SELESAI || 0 }}</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-check-double-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-filter-3-line text-gray-400"></i>
                    Filter Data
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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Cari Kapal</label>
                    <div class="relative">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Nama kapal..."
                            class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                        />
                    </div>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Dari Tanggal</label>
                    <input
                        v-model="dateFrom"
                        type="date"
                        @change="applyFilters"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    />
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Sampai Tanggal</label>
                    <input
                        v-model="dateTo"
                        type="date"
                        @change="applyFilters"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    />
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    >
                        <option value="">Semua Status</option>
                        <option value="BERLABUH">Berlabuh</option>
                        <option value="BONGKAR">Bongkar</option>
                        <option value="SELESAI">Selesai</option>
                    </select>
                </div>

                <!-- Landing Site -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Lokasi Pendaratan</label>
                    <select
                        v-model="landingSiteId"
                        @change="applyFilters"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    >
                        <option value="">Semua Lokasi</option>
                        <option v-for="site in landingSites" :key="site.id" :value="site.id">
                            {{ site.site_name }}
                        </option>
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
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal / Waktu</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapal</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Asal</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dermaga</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[150px]">Detail Ikan</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kualitas</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga Rata²</th>
                            <th class="px-4 py-3 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Suhu</th>
                            <th class="px-4 py-3 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <tr
                            v-for="(arrival, index) in arrivals.data"
                            :key="arrival.id"
                            class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                        >
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ (arrivals.current_page - 1) * arrivals.per_page + index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(arrival.arrival_date) }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ formatTime(arrival.arrival_time) }} WIB</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-semibold text-gray-900 dark:text-white">{{ arrival.vessel?.vessel_name || '-' }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ arrival.vessel?.license_number || '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">{{ arrival.origin || '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">{{ arrival.landing_site?.site_name || '-' }}</td>
                            <td class="px-4 py-3">
                                <div v-if="arrival.catches && arrival.catches.length > 0" class="space-y-1">
                                    <div v-for="c in arrival.catches" :key="c.id" class="text-[10px] flex justify-between bg-gray-50 dark:bg-gray-700/30 p-1 rounded">
                                        <span class="text-gray-700 dark:text-gray-300">{{ c.fish_species?.species_name || c.fish_species?.local_name || '-' }}</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-200">{{ c.weight_kg }}kg</span>
                                    </div>
                                </div>
                                <span v-else class="text-[10px] text-gray-400">-</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ arrival.fish_quality || '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs font-mono text-gray-700 dark:text-gray-300">
                                {{ formatCurrency(arrival.average_price) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                    <span class="inline-block" title="Suhu Ikan">🐟 {{ arrival.fish_temperature || 0 }}°</span>
                                    <span class="mx-1 text-gray-300 dark:text-gray-600">|</span>
                                    <span class="inline-block" title="Suhu Palka">📦 {{ arrival.hold_temperature || 0 }}°</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="getStatusClass(arrival.status)"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold"
                                >
                                    {{ arrival.status || '-' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="arrivals.data.length === 0" class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="ri-inbox-line text-2xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tidak ada data</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba ubah filter atau rentang tanggal untuk menampilkan data.</p>
            </div>

            <!-- Pagination -->
            <div v-if="arrivals.data.length > 0" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan <span class="font-semibold text-gray-700 dark:text-gray-300">{{ arrivals.from }}</span>
                    s/d <span class="font-semibold text-gray-700 dark:text-gray-300">{{ arrivals.to }}</span>
                    dari <span class="font-semibold text-gray-700 dark:text-gray-300">{{ arrivals.total }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    <button
                        v-for="link in arrivals.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url"
                        :class="[
                            'px-3 py-1.5 text-xs rounded-lg transition-all duration-200 font-medium',
                            link.active
                                ? 'bg-blue-600 text-white shadow-sm'
                                : link.url
                                    ? 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                    : 'text-gray-300 dark:text-gray-600 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</template>
