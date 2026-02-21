<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const props = defineProps({
    vessels: {
        type: Object,
        required: true
    }
})

const search = ref(new URLSearchParams(window.location.search).get('search') || '')
const status = ref(new URLSearchParams(window.location.search).get('status') || '')

watch([search, status], ([searchValue, statusValue]) => {
    router.get('/vessels', { search: searchValue, status: statusValue }, {
        preserveState: true,
        replace: true
    })
})

const deleteVessel = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus kapal ini?')) {
        router.delete(`/vessels/${id}`)
    }
}

const approveVessel = (id) => {
    if (confirm('Apakah Anda yakin ingin menyetujui kapal ini?')) {
        router.put(`/vessels/${id}/approve`)
    }
}

const rejectVessel = (id) => {
    if (confirm('Apakah Anda yakin ingin menolak kapal ini?')) {
        router.put(`/vessels/${id}/reject`)
    }
}

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        default:
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    }
}

const getStatusLabel = (status) => {
    switch (status) {
        case 'approved':
            return 'Disetujui'
        case 'rejected':
            return 'Ditolak'
        default:
            return 'Menunggu'
    }
}

const getSipiBadgeClass = (sipiStatus) => {
    switch (sipiStatus) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        case 'expired':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
}
</script>

<template>
    <AppLayout>
    <Head title="Daftar Kapal - SAMOSIR" />

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Kapal</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Kelola data kapal di sistem
                </p>
            </div>
            <Link
                href="/vessels/create"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kapal
            </Link>
        </div>

        <!-- Search & Filter Box -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <svg
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
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
                        placeholder="Cari berdasarkan nama kapal, pemilik, nomor lisensi, atau jenis..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                    />
                </div>
                <div class="relative min-w-[200px]">
                    <select
                        v-model="status"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors appearance-none cursor-pointer"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            <p v-if="vessels.total > 0" class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Menampilkan {{ vessels.from }} - {{ vessels.to }} dari total {{ vessels.total }} data
            </p>
            
            <!-- Flash Messages -->
            <div v-if="flash.success" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                <p class="text-sm text-green-800 dark:text-green-200">
                    {{ flash.success }}
                </p>
            </div>
            <div v-if="flash.error" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                <p class="text-sm text-red-800 dark:text-red-200">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nama Kapal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Pemilik
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nomor Lisensi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Jenis Kapal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                GT
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status SIPI
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status Kapal
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="vessel in vessels.data" :key="vessel.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ vessel.vessel_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ vessel.owner_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ vessel.license_number || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ vessel.vessel_type || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ vessel.gt || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getSipiBadgeClass(vessel.sipi_status)]">
                                    {{ vessel.sipi_status_text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadgeClass(vessel.approval_status)]">
                                    {{ getStatusLabel(vessel.approval_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link
                                    v-if="vessel.approval_status === 'pending'"
                                    @click.prevent="approveVessel(vessel.id)"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2"
                                >
                                    Setujui
                                </Link>
                                <Link
                                    v-if="vessel.approval_status === 'pending'"
                                    @click.prevent="rejectVessel(vessel.id)"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 mr-2"
                                >
                                    Tolak
                                </Link>
                                <Link
                                    :href="`/vessels/${vessel.id}/edit`"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteVessel(vessel.id)"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <tr v-if="vessels.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <p>Tidak ada data kapal yang ditemukan</p>
                                <Link
                                    href="/vessels/create"
                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block"
                                >
                                    Tambah kapal pertama
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="vessels.last_page > 1" class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-t border-gray-200 dark:border-gray-600 sm:px-6">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300 mb-2 sm:mb-0">
                        Halaman {{ vessels.current_page }} dari {{ vessels.last_page }}
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-if="vessels.prev_page_url"
                            :href="vessels.prev_page_url"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            Sebelumnya
                        </Link>
                        <span
                            v-for="page in Math.min(vessels.last_page, 5)"
                            :key="page"
                            class="px-3 py-1 text-sm border rounded-md transition-colors"
                            :class="[
                                page === vessels.current_page
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                            ]"
                        >
                            <Link
                                v-if="Math.abs(page - vessels.current_page) <= 2"
                                :href="`${vessels.path}?page=${page}${search ? '&search=' + search : ''}${status ? '&status=' + status : ''}`"
                                class="block"
                            >
                                {{ page }}
                            </Link>
                        </span>
                        <Link
                            v-if="vessels.next_page_url"
                            :href="vessels.next_page_url"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
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