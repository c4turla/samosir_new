<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    departure: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close'])

const close = () => {
    emit('close')
}

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
    <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-show="show" class="fixed inset-0 z-[99999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-80 transition-opacity" aria-hidden="true" @click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div v-if="departure" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-4xl max-h-[90vh] flex flex-col">
                    
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800 shrink-0">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                Detail Keberangkatan: {{ departure?.vessel?.vessel_name || '-' }}
                            </h3>
                        </div>
                        <button @click="close" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Contens (Scrollable) -->
                    <div class="px-6 py-5 overflow-y-auto grow">
                        
                        <!-- Status Badges -->
                        <div class="mb-6 flex gap-3 flex-wrap">
                            <span :class="['px-3 py-1 text-xs font-semibold rounded-full', getStatusColor(departure.status)]">
                                Status: {{ departure.status }}
                            </span>
                            <span v-if="departure.approval_status === 1" class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Disetujui
                            </span>
                            <span v-else class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Menunggu Penyetujuan
                            </span>
                        </div>

                        <!-- Data Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">Tujuan Operasi</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ departure.destination || '-' }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">Dermaga Singgah</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ departure.landing_site?.site_name || '-' }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">Tanggal Keberangkatan</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(departure.departure_date) }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">Waktu Aktual</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ departure.departure_time ? departure.departure_time + ' WIB' : '-' }}</p>
                            </div>
                        </div>

                        <!-- Technical Stats -->
                        <div class="mb-4">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Administrasi & Awak</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-2 border border-gray-100 dark:border-gray-700 p-4 rounded-lg bg-gray-50/50 dark:bg-gray-800/20">
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Jumlah ABK</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.crew_count || 0 }} Orang</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Syahbandar</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.syahbandar || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Petugas Administrasi</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.administrative_officer || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Logistik Perbekalan</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-2 border border-gray-100 dark:border-gray-700 p-4 rounded-lg bg-gray-50/50 dark:bg-gray-800/20">
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Es (Balok/Ember)</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.ice_supply || 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Air Tawar</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.water_supply || 0 }} L</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Solar</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.diesel_supply || 0 }} L</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Oli</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.oil_supply || 0 }} L</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="departure.other_supplies || departure.notes" class="mt-4 border border-gray-100 dark:border-gray-700 p-4 rounded-lg bg-gray-50/50 dark:bg-gray-800/20">
                            <div v-if="departure.other_supplies" class="mb-2">
                                <p class="text-[10px] text-gray-500 uppercase font-semibold">Logistik Lainnya</p>
                                <p class="text-sm text-gray-900 dark:text-gray-200">{{ departure.other_supplies }}</p>
                            </div>
                            <div v-if="departure.notes" :class="{'pt-2 border-t border-gray-200 dark:border-gray-700': departure.other_supplies}">
                                <p class="text-[10px] text-gray-500 uppercase font-semibold">Catatan Khusus</p>
                                <p class="text-sm text-gray-900 dark:text-gray-200 italic">{{ departure.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 shrink-0">
                        <button type="button" @click="close" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                            Tutup
                        </button>
                        <Link 
                            :href="route('departures.edit', departure.id)" 
                            @click="close"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-md shadow-sm transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            Edit Keberangkatan
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </Transition>
</template>
