<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    arrival: { type: Object, required: true }
})

// Format helpers
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatCurrency = (amount) => {
    if (!amount) return '0'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount)
}

const getStatusColor = (status) => {
    const colors = {
        'TAMBAT': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'LABUH': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'BONGKAR': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'MENGISI PERBEKALAN': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'PERBAIKAN': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'SELESAI': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <AppLayout>
        <Head :title="'Detail Kedatangan - ' + (arrival.vessel?.vessel_name || 'Kapal') + ' - SAMOSIR'" />
        
        <div class="max-w-4xl mx-auto">
            <!-- Header section -->
            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <Link href="/arrivals" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Detail Kedatangan</h1>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 ml-8">
                        Informasi lengkap kedatangan kapal
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="['px-2.5 py-1 text-xs font-medium rounded-full', getStatusColor(arrival.status)]">
                        {{ arrival.status }}
                    </span>
                    <span v-if="arrival.approval_status === '1'" class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Disetujui
                    </span>
                    <span v-else class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Menunggu Penyetujuan
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="p-6">
                    <!-- Informasi Kapal -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Informasi Kapal & Kedatangan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nama Kapal</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.vessel?.vessel_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Dermaga Pendaratan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.landing_site?.site_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Asal / Daerah Penangkapan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.origin || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Waktu Kedatangan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(arrival.arrival_date) }} {{ arrival.arrival_time ? arrival.arrival_time + ' WIB' : '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Tangkapan -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                            Hasil Tangkapan Ikan
                        </h3>
                        
                        <div v-if="!arrival.catches || arrival.catches.length === 0" class="text-center py-6 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tidak ada data tangkapan ikan yang dicatat.</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 rounded-lg overflow-hidden">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Ikan</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Berat (kg)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr v-for="(catchItem, index) in arrival.catches" :key="catchItem.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ catchItem.fish_species?.species_name || 'Tidak diketahui' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">{{ catchItem.weight_kg }} kg</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Informasi Kondisi Ikan -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Kondisi Ikan & Estimasi
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Mutu Penanganan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.mutu || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Produk Dihasilkan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.fish_quality || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Volume Limbah</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.waste_volume ? arrival.waste_volume + ' kg' : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Suhu Ikan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.fish_temperature ? arrival.fish_temperature + ' °C' : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Suhu Palka</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.hold_temperature ? arrival.hold_temperature + ' °C' : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Harga Rata-rata</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(arrival.average_price) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div v-if="arrival.notes">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Catatan Khusus
                        </h3>
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg text-sm text-gray-700 dark:text-gray-300 italic">
                            {{ arrival.notes }}
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Ditambahkan pada: {{ formatDate(arrival.created_at) }}
                    </div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('arrivals.edit', arrival.id)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Edit Kedatangan
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
