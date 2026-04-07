<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    unloading: { type: Object, required: true },
    currentUser: { type: Object, required: true }
})

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatWaktu = (timeString) => {
    if (!timeString) return '-'
    return timeString.substring(0, 5)
}

const approveUnloading = () => {
    if (confirm('Apakah Anda yakin ingin menyetujui penimbangan ikan ini?')) {
        router.post(`/approval/${props.unloading.id}/approve`)
    }
}

const rejectUnloading = () => {
    if (confirm('Apakah Anda yakin ingin menolak penimbangan ikan ini?')) {
        router.post(`/approval/${props.unloading.id}/reject`)
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="'Review Penimbangan - ' + (unloading.arrival?.vessel?.vessel_name || 'Kapal') + ' - SAMOSIR'" />
        
        <div class="max-w-4xl mx-auto">
            <!-- Header section -->
            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <Link href="/approval" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Review Penimbangan Ikan</h1>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 ml-8">
                        Periksa detail data sebelum memberikan persetujuan
                    </p>
                </div>
                <div>
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200 flex items-center gap-1 border border-yellow-200 dark:border-yellow-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Menunggu Persetujuan
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6">
                    <!-- Vessel Info Section -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <i class="ri-ship-line text-blue-500"></i>
                            Informasi Kapal
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Nama Kapal</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ unloading.arrival?.vessel?.vessel_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Tanda Selar / Mark</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ unloading.identification_mark || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Nama Nakhoda</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ unloading.captain_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Lokasi Penimbangan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ unloading.landing_site?.site_name || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Document Info Section -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <i class="ri-file-list-3-line text-orange-500"></i>
                            Informasi Dokumen & Waktu
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Nomor Surat</p>
                                <p class="text-sm font-mono font-medium text-gray-900 dark:text-white">{{ unloading.reference_number || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Nomor STBL Kedatangan</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ unloading.bl_code || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Waktu Bongkar</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(unloading.unloading_date) }} {{ formatWaktu(unloading.unloading_time) }} WIB
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">No Urut Bongkar</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ unloading.sequence_number || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Syahbandar Penanggung Jawab</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ unloading.syahbandar?.name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Tanggal Surat</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(unloading.registration_date) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div v-if="unloading.notes">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                            <i class="ri-chat-1-line text-gray-400"></i>
                            Catatan Petugas
                        </h3>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg text-sm text-gray-700 dark:text-gray-300 italic border border-gray-100 dark:border-gray-700">
                            "{{ unloading.notes }}"
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="px-6 py-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                        Data ini memerlukan verifikasi oleh Syahbandar
                    </div>
                    
                    <div class="flex items-center gap-3" v-if="unloading.syahbandar_id === currentUser.id">
                        <button
                            @click="rejectUnloading"
                            class="px-5 py-2.5 bg-white dark:bg-gray-700 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-semibold rounded-xl transition-all flex items-center gap-2"
                        >
                            <i class="ri-close-circle-line text-lg"></i>
                            Tolak
                        </button>
                        <button
                            @click="approveUnloading"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all flex items-center gap-2"
                        >
                            <i class="ri-checkbox-circle-line text-lg"></i>
                            Setujui Penimbangan
                        </button>
                    </div>
                    <div v-else class="text-xs py-2 px-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg border border-red-200 dark:border-red-900/30">
                        Hanya Syahbandar yang ditunjuk ({{ unloading.syahbandar?.name }}) yang dapat menyetujui.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
