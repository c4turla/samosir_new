<script setup>
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    results: Object,
    filters: Object,
    summary: Object,
})

// Local filter state
const serviceType = ref(props.filters.service_type || 'all')
const search = ref(props.filters.search || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const status = ref(props.filters.status || '')

// Debounce timer
let searchTimer = null

// Apply filters
const applyFilters = () => {
    router.get('/reports/services', {
        service_type: serviceType.value,
        search: search.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Watch serviceType to automatically reload when user switches tabs
watch(serviceType, () => {
    // Reset page and other specific filters if necessary
    applyFilters()
})

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
    router.get('/reports/services', { service_type: serviceType.value }, { preserveState: true })
}

// Format date to Indonesian format
const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    try {
        const d = new Date(dateStr)
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
    } catch { return dateStr }
}

// Format currency
const formatCurrency = (val) => {
    if (!val) return 'Rp 0'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)
}

// Status badge classes
const getStatusClass = (s) => {
    const map = {
        'order': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'processed': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'completed': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'cancelled': 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
    }
    return map[s] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
}

// Get status label in Indonesian
const getStatusLabel = (s) => {
    const map = {
        'order': 'Pesanan',
        'processed': 'Diproses',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',
    }
    return map[s] || s
}

// Build export URL with current filters
const buildExportUrl = (type) => {
    const params = new URLSearchParams()
    params.append('service_type', serviceType.value)
    if (dateFrom.value) params.append('date_from', dateFrom.value)
    if (dateTo.value) params.append('date_to', dateTo.value)
    if (status.value) params.append('status', status.value)
    if (search.value) params.append('search', search.value)
    return `/reports/services/export-${type}?${params.toString()}`
}

// Go to page
const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Has active filters
const hasActiveFilters = computed(() => {
    return search.value || status.value || dateFrom.value || dateTo.value
})
</script>

<template>
    <Head title="Laporan Pelayanan Jasa" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-hand-heart-line text-blue-500"></i>
                    Laporan Pelayanan Jasa
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola dan unduh laporan transaksi dari Jasa Peralatan, Ice Cruiser, dan Air Tawar.
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

        <!-- Service Type Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-8 overflow-x-auto" aria-label="Tabs">
                <button
                    @click="serviceType = 'all'"
                    :class="[
                        serviceType === 'all'
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400 font-semibold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300',
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200'
                    ]"
                >
                    <i class="ri-pie-chart-2-line"></i>
                    Keseluruhan
                </button>
                <button
                    @click="serviceType = 'equipment'"
                    :class="[
                        serviceType === 'equipment'
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400 font-semibold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300',
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200'
                    ]"
                >
                    <i class="ri-tools-line"></i>
                    Jasa Peralatan
                </button>
                <button
                    @click="serviceType = 'ice_cruiser'"
                    :class="[
                        serviceType === 'ice_cruiser'
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400 font-semibold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300',
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200'
                    ]"
                >
                    <i class="ri-snowy-line"></i>
                    Jasa Ice Cruiser
                </button>
                <button
                    @click="serviceType = 'water'"
                    :class="[
                        serviceType === 'water'
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400 font-semibold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300',
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200'
                    ]"
                >
                    <i class="ri-water-percent-line"></i>
                    Jasa Air
                </button>
            </nav>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Transaksi -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Transaksi</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ summary.total_records }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-calculator-line text-blue-600 dark:text-blue-400 text-lg"></i>
                    </div>
                </div>
            </div>
            <!-- Total Pendapatan Lunas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan (Lunas)</p>
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{{ formatCurrency(summary.total_revenue) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-money-dollar-circle-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    </div>
                </div>
            </div>
            <!-- Status Pending/Order -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pesanan Baru</p>
                        <p class="text-2xl font-bold text-amber-500 dark:text-amber-400 mt-1">{{ summary.status_counts.order }}</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-time-line text-amber-500 dark:text-amber-400 text-lg"></i>
                    </div>
                </div>
            </div>
            <!-- Status Diproses -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sedang Diproses</p>
                        <p class="text-2xl font-bold text-blue-500 dark:text-blue-400 mt-1">{{ summary.status_counts.processed }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-loader-4-line text-blue-500 dark:text-blue-400 text-lg animate-spin" style="animation-duration: 3s;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Total Pendapatan Jasa (Only for 'all') -->
        <div v-if="serviceType === 'all'" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center shrink-0">
                    <i class="ri-tools-line text-indigo-600 dark:text-indigo-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Peralatan</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.equipment_revenue) }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center shrink-0">
                    <i class="ri-snowy-line text-cyan-600 dark:text-cyan-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Ice Cruiser</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.ice_cruiser_revenue) }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 bg-sky-100 dark:bg-sky-900/30 rounded-xl flex items-center justify-center shrink-0">
                    <i class="ri-water-percent-line text-sky-600 dark:text-sky-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Air Tawar</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.water_revenue) }}</p>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Pencarian</label>
                    <div class="relative">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari order, penyewa, kapal..."
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
                        <option value="order">Pesanan Baru (Order)</option>
                        <option value="processed">Sedang Diproses</option>
                        <option value="completed">Selesai (Lunas)</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div v-if="serviceType !== 'all'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/80">
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Order</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ serviceType === 'water' ? 'Pemohon' : 'Nama Penyewa' }}
                            </th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Kapal</th>
                            <th class="px-4 py-3 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            
                            <!-- Custom Columns for Water vs Equipment -->
                            <th v-if="serviceType === 'equipment'" class="px-4 py-3 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durasi</th>
                            <th v-if="serviceType === 'water'" class="px-4 py-3 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Volume</th>
                            
                            <th class="px-4 py-3 text-right text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Biaya</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Petugas</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bendahara</th>
                            <th class="px-4 py-3 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <tr
                            v-for="(record, index) in results.data"
                            :key="record.id"
                            class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                        >
                            <!-- Index -->
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ (results.current_page - 1) * results.per_page + index + 1 }}
                            </td>
                            <!-- Order Number -->
                            <td class="px-4 py-3 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                {{ record.order_number }}
                            </td>
                            <!-- Renter/Requester -->
                            <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-white">
                                {{ serviceType === 'water' ? (record.requester || '-') : (record.renter_name || '-') }}
                            </td>
                            <!-- Vessel -->
                            <td class="px-4 py-3">
                                <div class="text-xs font-semibold text-gray-900 dark:text-white">{{ record.vessel?.vessel_name || '-' }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ record.vessel?.license_number || '-' }}</div>
                            </td>
                            <!-- Service Date -->
                            <td class="px-4 py-3 text-center text-xs text-gray-600 dark:text-gray-300">
                                {{ formatDate(serviceType === 'water' ? record.request_date : record.service_date) }}
                            </td>
                            
                            <!-- Duration / Volume -->
                            <td v-if="serviceType === 'equipment'" class="px-4 py-3 text-center text-xs font-mono text-gray-700 dark:text-gray-300">
                                {{ record.duration || 0 }} Jam
                            </td>
                            <td v-if="serviceType === 'water'" class="px-4 py-3 text-center text-xs font-mono text-gray-700 dark:text-gray-300">
                                {{ record.volume || 0 }} Ton
                            </td>

                            <!-- Total payment / amount -->
                            <td class="px-4 py-3 text-right text-xs font-bold text-gray-800 dark:text-gray-200">
                                {{ formatCurrency(serviceType === 'water' ? record.total_payment : record.total_amount) }}
                            </td>

                            <!-- Officers -->
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                {{ serviceType === 'water' ? (record.field_officer || '-') : (record.officer || '-') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                {{ record.treasurer || '-' }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="getStatusClass(record.status)"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                >
                                    {{ getStatusLabel(record.status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="results.data.length === 0" class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="ri-inbox-line text-2xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tidak ada data</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba ubah filter atau rentang tanggal untuk menampilkan data.</p>
            </div>

            <!-- Pagination -->
            <div v-if="results.data.length > 0" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan <span class="font-semibold text-gray-700 dark:text-gray-300">{{ results.from }}</span>
                    s/d <span class="font-semibold text-gray-700 dark:text-gray-300">{{ results.to }}</span>
                    dari <span class="font-semibold text-gray-700 dark:text-gray-300">{{ results.total }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    <button
                        v-for="link in results.links"
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
