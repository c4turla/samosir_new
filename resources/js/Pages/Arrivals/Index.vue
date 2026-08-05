<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const props = defineProps({
    arrivals: {
        type: Object,
        required: true
    }
})

const search = ref(new URLSearchParams(window.location.search).get('search') || '')
const status = ref(new URLSearchParams(window.location.search).get('status') || '')
const dateFrom = ref(new URLSearchParams(window.location.search).get('date_from') || '')
const dateTo = ref(new URLSearchParams(window.location.search).get('date_to') || '')

watch([search, status, dateFrom, dateTo], () => {
    router.get('/arrivals', { 
        search: search.value, 
        status: status.value, 
        date_from: dateFrom.value,
        date_to: dateTo.value
    }, {
        preserveState: true,
        replace: true
    })
})

const showDetailModal = ref(false)
const selectedArrival = ref(null)

const openDetailModal = (arrival) => {
    selectedArrival.value = arrival
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
    selectedArrival.value = null
}

const user = computed(() => page.props.auth?.user)

const formatCurrency = (amount) => {
    if (!amount) return 'Rp 0'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount)
}

const deleteArrival = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data kedatangan ini?')) {
        router.delete(`/arrivals/${id}`)
    }
}

const approveArrival = (id) => {
    closeDetailModal()
    router.put(`/arrivals/${id}/approve`)
}

const rejectArrival = (id) => {
    closeDetailModal()
    router.put(`/arrivals/${id}/reject`)
}

const forwardArrival = (id) => {
    if (confirm('Apakah Anda yakin ingin meneruskan data kedatangan ini ke Syahbandar untuk approval?')) {
        router.put(`/arrivals/${id}/forward`)
    }
}

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'TAMBAT':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
        case 'BONGKAR':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
        case 'SELESAI':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
}

const getApprovalStatusText = (arrival) => {
    if (arrival.approval_status === true || arrival.approval_status === '1' || arrival.approval_status === 1) {
        return 'Disetujui'
    }
    if (!arrival.is_processed) {
        return 'Perlu Diperiksa'
    }
    return 'Menunggu Approval'
}

const getApprovalBadgeClass = (arrival) => {
    if (arrival.approval_status === true || arrival.approval_status === '1' || arrival.approval_status === 1) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    }
    if (!arrival.is_processed) {
        return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'
    }
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200'
}

const formatTanggal = (dateString) => {
    if (!dateString) return '-'
    
    const options = { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric' 
    }
    
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('id-ID', options)
    } catch (error) {
        return dateString
    }
}

const formatWaktu = (timeString) => {
    if (!timeString) return '-'
    return timeString.substring(0, 5) // Format HH:MM
}
const userRole = computed(() => page.props.auth?.user?.role)

const isApproved = (arrival) => {
    return arrival.approval_status === true || arrival.approval_status === '1' || arrival.approval_status === 1
}

</script>

<template>
    <AppLayout>
        <Head title="Kedatangan Kapal - SAMOSIR" />

        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Kedatangan Kapal</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Kelola data kedatangan kapal di pelabuhan
                    </p>
                </div>
                <Link
                    v-if="userRole !== 'kepala_pelabuhan'"
                    href="/arrivals/create"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-xs"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Kedatangan
                </Link>
            </div>

            <!-- Search & Filter Box -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative md:col-span-1">
                        <svg
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari kapal atau dermaga..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors text-xs"
                        />
                    </div>
                    <div class="relative">
                        <select
                            v-model="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors appearance-none cursor-pointer text-xs"
                        >
                            <option value="">Semua Status</option>
                            <option value="TAMBAT">Tambat</option>
                            <option value="BONGKAR">Bongkar</option>
                            <option value="SELESAI">Selesai</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="relative">
                        <input
                            v-model="dateFrom"
                            type="date"
                            placeholder="Dari tanggal..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors text-xs"
                        />
                    </div>
                    <div class="relative">
                        <input
                            v-model="dateTo"
                            type="date"
                            placeholder="Sampai tanggal..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors text-xs"
                        />
                    </div>
                </div>
                <p v-if="arrivals.data && arrivals.data.length > 0 && arrivals.total > 0" class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                    Menampilkan {{ arrivals.from }} - {{ arrivals.to }} dari total {{ arrivals.total }} data
                </p>
                
                <!-- Flash Messages -->
                <div v-if="flash.success" class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <p class="text-xs text-green-800 dark:text-green-200">
                        {{ flash.success }}
                    </p>
                </div>
                <div v-if="flash.error" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <p class="text-xs text-red-800 dark:text-red-200">
                        {{ flash.error }}
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Kapal
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Asal
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Dermaga
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Approval
                                </th>
                                <th v-if="userRole !== 'kepala_pelabuhan'" class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="arrival in (arrivals.data || [])" :key="arrival.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div>
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">
                                            {{ formatTanggal(arrival.arrival_date) }}
                                        </p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            {{ formatWaktu(arrival.arrival_time) }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-gray-900 dark:text-white">
                                                {{ arrival.vessel?.vessel_name }}
                                            </p>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                                {{ arrival.vessel?.license_number || '-' }} | Nakhoda: {{ arrival.nakhoda_name || '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                    {{ arrival.origin || '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                    {{ arrival.landing_site?.site_name || '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium', getStatusBadgeClass(arrival.status)]">
                                        {{ arrival.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium', getApprovalBadgeClass(arrival)]">
                                        {{ getApprovalStatusText(arrival) }}
                                    </span>
                                </td>
                                <td v-if="userRole !== 'kepala_pelabuhan'" class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Teruskan Button (for petugas/admin to forward to syahbandar) -->
                                        <button
                                            v-if="userRole !== 'syahbandar' && !isApproved(arrival) && !arrival.is_processed"
                                            @click.prevent="forwardArrival(arrival.id)"
                                            class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 cursor-pointer"
                                            title="Teruskan ke Syahbandar"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7M5 12h11" />
                                            </svg>
                                        </button>
                                        <!-- Periksa Button for Syahbandar (opens detail modal before approval) -->
                                         <button
                                             v-if="userRole === 'syahbandar' && !isApproved(arrival) && arrival.is_processed"
                                             @click="openDetailModal(arrival)"
                                             class="inline-flex items-center px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[10px] font-semibold transition-colors cursor-pointer animate-pulse"
                                             title="Periksa & Setujui"
                                         >
                                             <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                             </svg>
                                             Periksa
                                         </button>

                                         <!-- Detail/Eye button for non-syahbandar or already approved -->
                                         <button
                                             v-if="isApproved(arrival) || userRole !== 'syahbandar'"
                                             @click="openDetailModal(arrival)"
                                             class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 cursor-pointer"
                                             title="Lihat Detail"
                                         >
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                             </svg>
                                         </button>

                                         <!-- Tombol Print (always available if approved) -->
                                         <a
                                             v-if="isApproved(arrival)"
                                             :href="`/arrivals/${arrival.id}/print`"
                                             class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-250 cursor-pointer"
                                             title="Cetak Laporan Kedatangan"
                                         >
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                             </svg>
                                         </a>

                                        <!-- Edit Button (petugas/admin only) -->
                                        <Link
                                            v-if="userRole === 'admin' || (userRole !== 'syahbandar' && !isApproved(arrival))"
                                            :href="`/arrivals/${arrival.id}/edit`"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Edit"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 0L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>

                                        <!-- Delete Button (petugas/admin only) -->
                                        <button
                                            v-if="userRole === 'admin' || (userRole !== 'syahbandar' && !isApproved(arrival))"
                                            @click="deleteArrival(arrival.id)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Hapus"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!arrivals.data || arrivals.data.length === 0">
                                <td :colspan="userRole !== 'kepala_pelabuhan' ? 7 : 6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                    <p class="text-xs">Tidak ada data kedatangan kapal yang ditemukan</p>
                                    <Link
                                        v-if="userRole !== 'kepala_pelabuhan'"
                                        href="/arrivals/create"
                                        class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block text-xs"
                                    >
                                        Catat kedatangan pertama
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="arrivals.last_page > 1" class="bg-gray-50 dark:bg-gray-700 px-3 py-2 border-t border-gray-200 dark:border-gray-600 sm:px-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                        <div class="text-xs text-gray-700 dark:text-gray-300 mb-2 sm:mb-0">
                            Halaman {{ arrivals.current_page }} dari {{ arrivals.last_page }}
                        </div>
                        <div class="flex flex-wrap items-center gap-1">
                            <Link
                                v-for="(link, index) in arrivals.links"
                                :key="index"
                                :href="link.url || '#'"
                                class="px-2.5 py-1 text-xs rounded-md border transition-all duration-200 font-medium"
                                :class="[
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                        : link.url
                                            ? 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                                            : 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed opacity-50 pointer-events-none'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Detail & Approval Modal -->
        <Teleport to="body">
        <div v-if="showDetailModal && selectedArrival">
            <!-- Backdrop -->
            <div class="fixed inset-0 z-[50] bg-gray-900/60 backdrop-blur-sm" @click="closeDetailModal"></div>
            <!-- Modal wrapper -->
            <div class="fixed inset-0 z-[51] flex items-center justify-center p-4" aria-modal="true">
                <!-- Modal panel -->
                <div class="relative bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl w-full max-w-2xl border border-gray-200 dark:border-gray-700">
                    <!-- Modal Header -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white" id="modal-title">
                                Detail Kedatangan Kapal
                            </h3>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                Kapal: {{ selectedArrival.vessel?.vessel_name || '-' }}
                            </p>
                        </div>
                        <button @click="closeDetailModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-4 max-h-[460px] overflow-y-auto space-y-4">
                        <!-- Data Kapal & Kedatangan -->
                        <div>
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Informasi Kapal & Kedatangan
                            </p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-505 uppercase tracking-wider">Nama Kapal</span>
                                    <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ selectedArrival.vessel?.vessel_name || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-505 uppercase tracking-wider">Nama Nakhoda</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ selectedArrival.nakhoda_name || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-505 uppercase tracking-wider">Dermaga Pendaratan</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ selectedArrival.landing_site?.site_name || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-505 uppercase tracking-wider">Asal / Daerah Penangkapan</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ selectedArrival.origin || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-505 uppercase tracking-wider">Waktu Kedatangan</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ formatTanggal(selectedArrival.arrival_date) }} {{ selectedArrival.arrival_time ? formatWaktu(selectedArrival.arrival_time) + ' WIB' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Hasil Tangkapan Ikan -->
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                Hasil Tangkapan Ikan
                            </p>
                            <div v-if="!selectedArrival.catches || selectedArrival.catches.length === 0" class="text-center py-4 bg-gray-50 dark:bg-gray-900/30 rounded border border-dashed border-gray-100 dark:border-gray-700">
                                <p class="text-xs text-gray-505 text-gray-500 dark:text-gray-400">Tidak ada data tangkapan ikan yang dicatat.</p>
                            </div>
                            <div v-else class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded">
                                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-750">
                                    <thead class="bg-gray-50 dark:bg-gray-750">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                                            <th scope="col" class="px-4 py-2 text-left text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Ikan</th>
                                            <th scope="col" class="px-4 py-2 text-right text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Berat (kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                        <tr v-for="(catchItem, index) in selectedArrival.catches" :key="catchItem.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-white">{{ catchItem.fish_species?.species_name || 'Tidak diketahui' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-white text-right font-semibold">{{ catchItem.weight_kg }} kg</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Kondisi Ikan & Estimasi -->
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Kondisi Ikan & Estimasi
                            </p>
                            <div class="grid grid-cols-3 gap-x-6 gap-y-3">
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Mutu Penanganan</span>
                                    <span class="text-xs font-medium text-gray-750 dark:text-gray-300">{{ selectedArrival.mutu || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Produk Dihasilkan</span>
                                    <span class="text-xs font-medium text-gray-750 dark:text-gray-300">{{ selectedArrival.fish_quality || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Volume Limbah</span>
                                    <span class="text-xs font-medium text-gray-750 dark:text-gray-300">{{ selectedArrival.waste_volume ? selectedArrival.waste_volume + ' kg' : '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Suhu Ikan</span>
                                    <span class="text-xs font-medium text-gray-750 dark:text-gray-300">{{ selectedArrival.fish_temperature ? selectedArrival.fish_temperature + ' °C' : '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Suhu Palka</span>
                                    <span class="text-xs font-medium text-gray-750 dark:text-gray-300">{{ selectedArrival.hold_temperature ? selectedArrival.hold_temperature + ' °C' : '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Harga Rata-rata</span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ formatCurrency(selectedArrival.average_price) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div v-if="selectedArrival.notes" class="border-t border-gray-100 dark:border-gray-700 pt-3">
                            <span class="block text-[10px] font-semibold text-gray-400 dark:text-gray-505 uppercase tracking-wider mb-1">Catatan Khusus</span>
                            <p class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/30 p-2.5 rounded border border-gray-100 dark:border-gray-700">
                                {{ selectedArrival.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-2">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-505 uppercase tracking-wider block">Status Approval</span>
                            <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold mt-0.5', getApprovalBadgeClass(selectedArrival)]">
                                {{ getApprovalStatusText(selectedArrival) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <button 
                                @click="closeDetailModal" 
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors cursor-pointer"
                            >
                                Tutup
                            </button>
                            
                            <!-- Syahbandar Approval actions in footer -->
                            <button
                                v-if="userRole === 'syahbandar' && !isApproved(selectedArrival) && selectedArrival.is_processed"
                                @click="rejectArrival(selectedArrival.id)"
                                class="w-full sm:w-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow transition-colors flex items-center justify-center cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tolak
                            </button>
                            <button
                                v-if="userRole === 'syahbandar' && !isApproved(selectedArrival) && selectedArrival.is_processed"
                                @click="approveArrival(selectedArrival.id)"
                                class="w-full sm:w-auto px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold shadow transition-colors flex items-center justify-center cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Setujui
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </Teleport>

    </AppLayout>
</template>