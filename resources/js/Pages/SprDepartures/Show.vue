<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    sprDeparture: {
        type: Object,
        required: true
    }
})

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const formatWaktu = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB'
}

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200'
        case 'processed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200'
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200'
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
}
</script>

<template>
    <AppLayout>
        <Head title="Detail SPR Keberangkatan" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <Link href="/spr-departures" class="p-2 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg text-gray-500 hover:text-blue-600 transition-colors">
                        <i class="ri-arrow-left-line"></i>
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Detail SPR Keberangkatan</h1>
                        <p class="text-xs text-gray-600 dark:text-gray-400">ID Permohonan: #{{ sprDeparture.id.toString().padStart(5, '0') }}</p>
                    </div>
                </div>
                <div :class="['px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider', getStatusBadgeClass(sprDeparture.status)]">
                    {{ sprDeparture.status }}
                </div>
            </div>

            <!-- Form Format SPR -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <!-- Header Dokumen -->
                <div class="bg-blue-600 px-6 py-4 text-center">
                    <h2 class="text-white font-bold tracking-widest uppercase">Surat Pemberitahuan Rencana Keberangkatan</h2>
                    <p class="text-blue-100 text-[10px] mt-1">(Dihasilkan melalui Sistem SAMOSIR Mobile)</p>
                </div>

                <div class="p-8 space-y-8">
                    <!-- Bagian A: Data Kapal -->
                    <section>
                        <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-4 border-b border-blue-50 dark:border-blue-900/50 pb-2">I. Data Kapal & Keberangkatan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-gray-400 uppercase">Nama Kapal</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ sprDeparture.vessel?.vessel_name || '-' }}</p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-gray-400 uppercase">Pemilik / Perusahaan</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ sprDeparture.vessel?.owner_name || '-' }}</p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-gray-400 uppercase">Nama Nakhoda</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ sprDeparture.nakhoda_name || '-' }}</p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-gray-400 uppercase">Tanda Selar</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ sprDeparture.vessel?.selar_mark || '-' }}</p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-gray-400 uppercase">Ukuran Kapal</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Panjang: {{ sprDeparture.vessel?.length || '-' }} M | GT: {{ sprDeparture.vessel?.gt || '-' }}
                                </p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-gray-400 uppercase">Merk / Kekuatan Mesin</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ sprDeparture.vessel?.engine_power || '-' }}</p>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600">
                            <p class="text-[10px] text-gray-400 uppercase mb-1">Muatan Kapal</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ sprDeparture.muatan || '-' }}</p>
                        </div>
                    </section>

                    <!-- Bagian B: Check Point & Fisik -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <section>
                            <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">II. Check Point</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Kedatangan (Masuk)</p>
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(sprDeparture.cp_arrival_date) }}</p>
                                    <p class="text-[10px] text-gray-500 italic">No. STBL: {{ sprDeparture.cp_arrival_stbl || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Keberangkatan (Keluar)</p>
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(sprDeparture.cp_departure_date) }}</p>
                                    <p class="text-[10px] text-gray-500 italic">No. STBL: {{ sprDeparture.cp_departure_stbl || '-' }}</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">III. Cek Fisik Keberangkatan</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Kedatangan (Masuk)</p>
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(sprDeparture.physical_arrival_date) }}</p>
                                    <p class="text-[10px] text-gray-500 italic">No. STBL: {{ sprDeparture.physical_arrival_stbl || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Keberangkatan (Keluar)</p>
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(sprDeparture.physical_departure_date) }}</p>
                                    <p class="text-[10px] text-gray-500 italic">No. STBL: {{ sprDeparture.physical_departure_stbl || '-' }}</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Bagian C: Rencana Berangkat -->
                    <section class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-xl border border-blue-100 dark:border-blue-800">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-blue-600 rounded-lg text-white">
                                <i class="ri-calendar-event-line text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-widest mb-1">Rencana Keberangkatan</h3>
                                <p class="text-lg font-black text-gray-900 dark:text-white">
                                    {{ formatWaktu(sprDeparture.planned_departure_datetime) }}, {{ formatDate(sprDeparture.planned_departure_datetime) }}
                                </p>
                                <p class="text-[10px] text-blue-600 dark:text-blue-400 font-medium mt-2 italic">
                                    * Telah menyelesaikan administrasi kepelabuhanan (bukti terlampir di permohonan fisik).
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Bagian D: Catatan -->
                    <section v-if="sprDeparture.additional_notes">
                        <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-2">Keterangan Tambahan</h3>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-xs text-gray-600 dark:text-gray-400 italic leading-relaxed">
                            {{ sprDeparture.additional_notes }}
                        </div>
                    </section>

                    <!-- Footer / Tanda Tangan -->
                    <div class="pt-8 flex justify-between items-end border-t border-gray-100 dark:border-gray-700">
                        <div class="text-[10px] text-gray-400">
                            <p>Dicetak otomatis melalui:</p>
                            <p class="font-bold">SAMOSIR V3.0 - PPN Sibolga</p>
                            <p>Waktu Submit: {{ formatDate(sprDeparture.created_at) }} {{ formatWaktu(sprDeparture.created_at) }}</p>
                        </div>
                        <div class="text-center w-48">
                            <p class="text-[10px] text-gray-500 mb-12">Pemohon,</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white border-b border-gray-900 dark:border-white inline-block px-4 pb-1">
                                {{ sprDeparture.nakhoda_name }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">Nakhoda Kapal</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
