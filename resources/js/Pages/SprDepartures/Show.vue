<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    sprDeparture: {
        type: Object,
        required: true
    }
})

const page = usePage()
const userRole = computed(() => page.props.auth?.user?.role)
const flash = computed(() => page.props.flash || {})

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

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending':
            return 'Pending (Menunggu Petugas)'
        case 'processed':
            return 'Diteruskan ke Syahbandar'
        case 'approved':
            return 'Diajukan'
        case 'rejected':
            return 'Ditolak Syahbandar'
        default:
            return status
    }
}

// Parse combined muatan (BBM, Air, Es)
const parsedMuatan = computed(() => {
    const muatanStr = props.sprDeparture.muatan || ''
    const bbmMatch = muatanStr.match(/BBM:\s*([^,]+)/i)
    const airMatch = muatanStr.match(/AIR:\s*([^,]+)/i)
    const esMatch = muatanStr.match(/ES:\s*([^,]+)/i)
    if (!bbmMatch && !airMatch && !esMatch) {
        return null // raw muatan
    }
    return {
        bbm: bbmMatch ? bbmMatch[1].trim() : '-',
        air: airMatch ? airMatch[1].trim() : '-',
        es: esMatch ? esMatch[1].trim() : '-'
    }
})

// Parse combined additional_notes (Kegiatan, Pemohon, Catatan)
const parsedNotes = computed(() => {
    const notesStr = props.sprDeparture.additional_notes || ''
    const kegiatanMatch = notesStr.match(/Kegiatan:\s*([^|]+)/i)
    const pemohonMatch = notesStr.match(/Pemohon:\s*([^|]+)/i)
    const catatanMatch = notesStr.match(/Catatan:\s*(.+)/i)
    
    if (!kegiatanMatch && !pemohonMatch) {
        return {
            kegiatan: '-',
            pemohon: props.sprDeparture.user?.name || '-',
            catatan: notesStr
        }
    }
    
    return {
        kegiatan: kegiatanMatch ? kegiatanMatch[1].trim() : '-',
        pemohon: pemohonMatch ? pemohonMatch[1].trim() : (props.sprDeparture.user?.name || '-'),
        catatan: catatanMatch ? catatanMatch[1].trim() : '-'
    }
})


</script>

<template>
    <AppLayout>
        <Head title="Detail SPR Keberangkatan" />

        <div class="max-w-4xl mx-auto">
            <!-- Flash Message -->
            <div v-if="flash.success" class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 text-sm text-green-800 dark:text-green-200">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-sm text-red-800 dark:text-red-200">
                {{ flash.error }}
            </div>

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
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-gray-400 uppercase">Kegiatan</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ parsedNotes.kegiatan }}</p>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600">
                            <p class="text-[10px] text-gray-400 uppercase mb-1">Muatan Kapal</p>
                            <div v-if="parsedMuatan" class="grid grid-cols-3 gap-4 text-xs font-semibold">
                                <div>
                                    <span class="text-gray-400 font-normal block">BBM</span>
                                    <span class="text-gray-900 dark:text-white">{{ parsedMuatan.bbm }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-normal block">AIR</span>
                                    <span class="text-gray-900 dark:text-white">{{ parsedMuatan.air }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-normal block">ES</span>
                                    <span class="text-gray-900 dark:text-white">{{ parsedMuatan.es }}</span>
                                </div>
                            </div>
                            <p v-else class="text-sm font-medium text-gray-900 dark:text-white">{{ sprDeparture.muatan || '-' }}</p>
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
                                    <p class="text-[10px] text-gray-555 dark:text-gray-200 italic">No. STBL: {{ sprDeparture.cp_arrival_stbl || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Keberangkatan (Keluar)</p>
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(sprDeparture.cp_departure_date) }}</p>
                                    <p class="text-[10px] text-gray-555 dark:text-gray-200 italic">No. STBL: {{ sprDeparture.cp_departure_stbl || '-' }}</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">III. Cek Fisik Keberangkatan</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Kedatangan (Masuk)</p>
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(sprDeparture.physical_arrival_date) }}</p>
                                    <p class="text-[10px] text-gray-555 dark:text-gray-200 italic">No. STBL: {{ sprDeparture.physical_arrival_stbl || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase">Keberangkatan (Keluar)</p>
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(sprDeparture.physical_departure_date) }}</p>
                                    <p class="text-[10px] text-gray-555 dark:text-gray-200 italic">No. STBL: {{ sprDeparture.physical_departure_stbl || '-' }}</p>
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
                    <section v-if="parsedNotes.catatan">
                        <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-2">Keterangan Tambahan</h3>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-xs text-gray-600 dark:text-gray-400 italic leading-relaxed">
                            {{ parsedNotes.catatan }}
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
                            <p class="text-[10px] text-gray-555 dark:text-gray-200 mb-12">Pemohon,</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white border-b border-gray-900 dark:border-white inline-block px-4 pb-1">
                                {{ parsedNotes.pemohon }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">Pemohon / Pengelola</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
