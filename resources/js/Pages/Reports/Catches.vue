<script setup>
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    catches: Object,
    filters: Object,
    fishSpecies: Array,
    summary: Object,
})

// Local filter state
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const fishSpeciesId = ref(props.filters.fish_species_id || '')

// Apply filters
const applyFilters = () => {
    router.get('/reports/catches', {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        fish_species_id: fishSpeciesId.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Reset filters
const resetFilters = () => {
    dateFrom.value = ''
    dateTo.value = ''
    fishSpeciesId.value = ''
    router.get('/reports/catches', {}, { preserveState: true })
}

// Build export URL
const buildExportUrl = (type) => {
    const params = new URLSearchParams()
    if (dateFrom.value) params.append('date_from', dateFrom.value)
    if (dateTo.value) params.append('date_to', dateTo.value)
    if (fishSpeciesId.value) params.append('fish_species_id', fishSpeciesId.value)
    return `/reports/catches/export-${type}?${params.toString()}`
}

// Go to page
const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const hasActiveFilters = computed(() => {
    return fishSpeciesId.value
})

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)
}
</script>

<template>
    <Head title="Laporan Hasil Tangkapan" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-bar-chart-box-line text-blue-500"></i>
                    Laporan Hasil Tangkapan
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Analitik volume dan nilai hasil tangkapan di PPN Sibolga.
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

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 grid grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter mb-4">Total Berat Tangkapan</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ Math.round(summary.total_weight).toLocaleString('id-ID') }}</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-medium">Kg (Kilogram)</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center">
                            <i class="ri-scales-3-line text-blue-600 dark:text-blue-400 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter mb-4">Estimasi Nilai Produksi</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <h3 class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ formatCurrency(summary.total_value) }}</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-medium">Nilai Estimasi (IDR)</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center">
                            <i class="ri-coins-line text-amber-600 dark:text-amber-400 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Species -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="ri-medal-line text-blue-500"></i>
                    Komoditas Utama (Top 5)
                </h3>
                <div class="space-y-3">
                    <div v-for="(item, idx) in summary.top_species" :key="idx" class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-700 text-[10px] font-bold text-gray-500">
                                {{ idx + 1 }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">{{ item.fish_species?.species_name }}</p>
                                <p class="text-[10px] text-gray-400">{{ item.fish_species?.local_name }}</p>
                            </div>
                        </div>
                        <p class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ Math.round(item.total_weight).toLocaleString('id-ID') }} kg</p>
                    </div>
                </div>
                <div v-if="summary.top_species.length === 0" class="text-center py-6">
                    <p class="text-[11px] text-gray-400 italic">Data belum tersedia</p>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2 uppercase tracking-widest">
                    <i class="ri-filter-3-line text-blue-500"></i>
                    Filter Laporan
                </h3>
                <button
                    v-if="hasActiveFilters"
                    @click="resetFilters"
                    class="text-[10px] uppercase font-bold text-red-500 hover:text-red-700 transition-colors"
                >
                    Hapus Filter
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Dari Tanggal</label>
                    <input
                        v-model="dateFrom"
                        type="date"
                        @change="applyFilters"
                        class="w-full px-4 py-3 text-sm border border-gray-100 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                    />
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Sampai Tanggal</label>
                    <input
                        v-model="dateTo"
                        type="date"
                        @change="applyFilters"
                        class="w-full px-4 py-3 text-sm border border-gray-100 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                    />
                </div>

                <!-- Fish Species -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Jenis Ikan</label>
                    <select
                        v-model="fishSpeciesId"
                        @change="applyFilters"
                        class="w-full px-4 py-3 text-sm border border-gray-100 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                    >
                        <option value="">Seluruh Jenis Ikan</option>
                        <option v-for="fish in fishSpecies" :key="fish.id" :value="fish.id">
                            {{ fish.species_name }} ({{ fish.local_name }})
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50">
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">No</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Kapal</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Komoditas</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Berat</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Estimasi Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <tr
                            v-for="(item, index) in catches.data"
                            :key="item.id"
                            class="hover:bg-gray-50/30 dark:hover:bg-gray-700/10 transition-colors"
                        >
                            <td class="px-6 py-4 text-xs font-bold text-gray-300">
                                {{ (catches.current_page - 1) * catches.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ new Date(item.arrival?.arrival_date).toLocaleDateString('id-ID') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-gray-900 dark:text-white uppercase">{{ item.arrival?.vessel?.vessel_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">{{ item.fish_species?.species_name }}</div>
                                <div class="text-[10px] text-gray-400 italic">({{ item.fish_species?.local_name }})</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ item.weight_kg.toLocaleString('id-ID') }}</span>
                                <span class="text-[10px] text-gray-400 ml-1">kg</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs font-black text-blue-600 dark:text-blue-400">{{ formatCurrency(item.estimated_value) }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="catches.data.length === 0" class="text-center py-24 bg-gray-50/20 dark:bg-gray-900/10">
                <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700">
                    <i class="ri-windy-line text-3xl text-gray-200 dark:text-gray-600"></i>
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">Data belum tersedia</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 max-w-xs mx-auto">Tidak ada rekaman tangkapan untuk kriteria filter yang Anda pilih.</p>
            </div>

            <!-- Pagination -->
            <div v-if="catches.data.length > 0" class="px-6 py-6 border-t border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    {{ catches.from }} - {{ catches.to }} / {{ catches.total }} Rekaman
                </p>
                <div class="flex items-center gap-1">
                    <button
                        v-for="link in catches.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url"
                        :class="[
                            'px-4 py-2 text-[10px] font-black rounded-xl transition-all duration-300 uppercase tracking-tighter',
                            link.active
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30'
                                : 'text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-700'
                        ]"
                        v-html="link.label"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</template>
