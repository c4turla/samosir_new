<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})
const user = computed(() => page.props.auth?.user)

const props = defineProps({
    unloadings: {
        type: Object,
        required: true
    }
})

const search = ref(new URLSearchParams(window.location.search).get('search') || '')
const status = ref(new URLSearchParams(window.location.search).get('status') || '')
const dateFrom = ref(new URLSearchParams(window.location.search).get('date_from') || '')
const dateTo = ref(new URLSearchParams(window.location.search).get('date_to') || '')

watch([search, status, dateFrom, dateTo], () => {
    router.get('/unloadings', { 
        search: search.value, 
        status: status.value, 
        date_from: dateFrom.value,
        date_to: dateTo.value
    }, {
        preserveState: true,
        replace: true
    })
})

const deleteUnloading = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data penimbangan ikan ini?')) {
        router.delete(`/unloadings/${id}`)
    }
}

const getApprovalBadgeClass = (status) => {
    if (status === true || status === '1' || status === 1) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    } else {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    }
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
    return timeString.substring(0, 5)
}

const userRole = computed(() => page.props.auth?.user?.role)
</script>

<template>
    <AppLayout>
        <Head title="Penimbangan Ikan - SAMOSIR" />

        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Penimbangan Ikan</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Kelola data penimbangan ikan di pelabuhan
                    </p>
                </div>
                <Link
                    v-if="userRole !== 'kepala_pelabuhan'"
                    href="/unloadings/create"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-xs"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Penimbangan
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
                            placeholder="Cari kapal atau nomor..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors text-xs"
                        />
                    </div>
                    <div class="relative">
                        <select
                            v-model="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors appearance-none cursor-pointer text-xs"
                        >
                            <option value="">Semua Status</option>
                            <option value="1">Disetujui</option>
                            <option value="0">Menunggu</option>
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
                <p v-if="unloadings.data && unloadings.data.length > 0 && unloadings.total > 0" class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                    Menampilkan {{ unloadings.from }} - {{ unloadings.to }} dari total {{ unloadings.total }} data
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
                                    No. Surat
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Syahbandar
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Lokasi
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="unloading in (unloadings.data || [])" :key="unloading.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div>
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">
                                            {{ formatTanggal(unloading.unloading_date) }}
                                        </p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            {{ formatWaktu(unloading.unloading_time) }}
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
                                                {{ unloading.arrival?.vessel?.vessel_name }}
                                            </p>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                                {{ unloading.arrival?.vessel?.license_number || '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                    {{ unloading.reference_number || '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                    {{ unloading.syahbandar?.name || '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                    {{ unloading.landing_site?.site_name || '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium', getApprovalBadgeClass(unloading.approval_status)]">
                                        {{ unloading.approval_status ? 'Disetujui' : 'Menunggu' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Print button for approved records -->
                                        <a
                                            v-if="unloading.approval_status"
                                            :href="`/unloadings/${unloading.id}/print`"
                                            target="_blank"
                                            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                                            title="Cetak"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                        <!-- Edit button only for unapproved records and NOT for kepala_pelabuhan -->
                                        <Link
                                            v-if="!unloading.approval_status && userRole !== 'kepala_pelabuhan'"
                                            :href="`/unloadings/${unloading.id}/edit`"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Edit"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 0L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <!-- Delete button only for unapproved records and NOT for kepala_pelabuhan -->
                                        <button
                                            v-if="!unloading.approval_status && userRole !== 'kepala_pelabuhan'"
                                            @click="deleteUnloading(unloading.id)"
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
                            <tr v-if="!unloadings.data || unloadings.data.length === 0">
                                <td :colspan="userRole !== 'kepala_pelabuhan' ? 7 : 7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                    <p class="text-xs">Tidak ada data penimbangan ikan yang ditemukan</p>
                                    <Link
                                        v-if="userRole !== 'kepala_pelabuhan'"
                                        href="/unloadings/create"
                                        class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block text-xs"
                                    >
                                        Catat penimbangan pertama
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="unloadings.last_page > 1" class="bg-gray-50 dark:bg-gray-700 px-3 py-2 border-t border-gray-200 dark:border-gray-600 sm:px-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between">
                        <div class="text-xs text-gray-700 dark:text-gray-300 mb-2 sm:mb-0">
                            Halaman {{ unloadings.current_page }} dari {{ unloadings.last_page }}
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-if="unloadings.prev_page_url"
                                :href="unloadings.prev_page_url"
                                class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Sebelumnya
                            </Link>
                            <span
                                v-for="page in Math.min(unloadings.last_page, 5)"
                                :key="page"
                                class="px-2 py-1 text-xs border rounded-md transition-colors"
                                :class="[
                                    page === unloadings.current_page
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                                ]"
                            >
                                <Link
                                    v-if="Math.abs(page - unloadings.current_page) <= 2"
                                    :href="`${unloadings.path}?page=${page}${search ? '&search=' + search : ''}${status ? '&status=' + status : ''}${dateFrom ? '&date_from=' + dateFrom : ''}${dateTo ? '&date_to=' + dateTo : ''}`"
                                    class="block"
                                >
                                    {{ page }}
                                </Link>
                            </span>
                            <Link
                                v-if="unloadings.next_page_url"
                                :href="unloadings.next_page_url"
                                class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Selanjutnya
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
