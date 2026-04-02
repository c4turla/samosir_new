<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const props = defineProps({
    unloadings: {
        type: Object,
        required: true
    },
    currentUser: {
        type: Object,
        required: true
    }
})

const approveUnloading = (id) => {
    if (confirm('Apakah Anda yakin ingin menyetujui penimbangan ikan ini?')) {
        router.post(`/approval/${id}/approve`)
    }
}

const rejectUnloading = (id) => {
    if (confirm('Apakah Anda yakin ingin menolak penimbangan ikan ini?')) {
        router.post(`/approval/${id}/reject`)
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
</script>

<template>
    <AppLayout>
        <Head title="Approval Penimbangan Ikan - SAMOSIR" />

        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Approval Penimbangan Ikan</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Setujui atau tolak penimbangan ikan yang telah diinput petugas
                    </p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="flash.success" class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                <p class="text-xs text-green-800 dark:text-green-200">
                    {{ flash.success }}
                </p>
            </div>
            <div v-if="flash.error" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                <p class="text-xs text-red-800 dark:text-red-200">
                    {{ flash.error }}
                </p>
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
                                    Petugas
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Lokasi
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
                                    {{ unloading.landingSite?.site_name || '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Only show approve if assigned to this syahbandar -->
                                        <Link
                                            v-if="unloading.syahbandar_id === currentUser.id"
                                            @click.prevent="approveUnloading(unloading.id)"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                            title="Setujui"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </Link>
                                        <!-- Only show reject if assigned to this syahbandar -->
                                        <Link
                                            v-if="unloading.syahbandar_id === currentUser.id"
                                            @click.prevent="rejectUnloading(unloading.id)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Tolak"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!unloadings.data || unloadings.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs">Tidak ada data penimbangan yang menunggu approval</p>
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
                                    :href="`${unloadings.path}?page=${page}`"
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
