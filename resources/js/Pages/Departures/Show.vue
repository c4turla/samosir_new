<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    departure: { type: Object, required: true }
})

// Format helpers
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

const getStatusColor = (status) => {
    const colors = {
        'MENUNGGU': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'DIPROSES': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'DISETUJUI': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'BERANGKAT': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'SELESAI': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
    return colors[status?.toUpperCase()] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <AppLayout>
        <Head :title="'Detail Keberangkatan - ' + (departure.vessel?.vessel_name || 'Kapal') + ' - SAMOSIR'" />
        
        <div class="max-w-4xl mx-auto">
            <!-- Header section -->
            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <Link href="/departures" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Detail Keberangkatan</h1>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 ml-8">
                        Informasi lengkap untuk keberangkatan kapal
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="['px-2.5 py-1 text-xs font-medium rounded-full', getStatusColor(departure.status)]">
                        {{ departure.status }}
                    </span>
                    <span v-if="departure.approval_status === 1" class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 flex items-center gap-1">
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
                            Jadwal Keberangkatan & Pelayaran
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nama Kapal</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.vessel?.vessel_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Dermaga Singgah</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.landing_site?.site_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tujuan Operasi Kapal</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.destination || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Jumlah ABK</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ departure.crew_count || 0 }} Orang
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Waktu Keberangkatan Aktual</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(departure.departure_date) }} {{ departure.departure_time ? departure.departure_time + ' WIB' : '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Perbekalan Kapal -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4v10l8 4 8-4V7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22V11M20 7L12 11M4 7l8 4" /></svg>
                            Logistik & Perbekalan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Es (Balok/Ember)</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.ice_supply || 0 }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Air Tawar (Liter)</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.water_supply || 0 }} L</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Solar (Liter)</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.diesel_supply || 0 }} L</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Oli (Liter)</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.oil_supply || 0 }} L</p>
                            </div>
                            <div class="md:col-span-4" v-if="departure.other_supplies">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Perbekalan Lainnya</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.other_supplies }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pejabat Berwenang -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Pejabat Penandatangan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Syahbandar</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.syahbandar || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Petugas Administrasi</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.administrative_officer || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div v-if="departure.notes">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Catatan Khusus
                        </h3>
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg text-sm text-gray-700 dark:text-gray-300 italic">
                            {{ departure.notes }}
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Ditambahkan pada: {{ formatDate(departure.created_at) }}
                    </div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('departures.edit', departure.id)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Edit Keberangkatan
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
