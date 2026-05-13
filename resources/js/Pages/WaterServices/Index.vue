<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import GeneralConfirmModal from '../../Components/GeneralConfirmModal.vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const props = defineProps({
    services: {
        type: Object,
        required: true
    },
    filters: Object
})

const search = ref(new URLSearchParams(window.location.search).get('search') || '')
const statusFilter = ref(new URLSearchParams(window.location.search).get('status') || '')

// Modal state
const showDeleteModal = ref(false)
const serviceToDelete = ref(null)
const isDeleting = ref(false)

watch([search, statusFilter], () => {
    router.get('/water-services', { 
        search: search.value, 
        status: statusFilter.value
    }, {
        preserveState: true,
        replace: true
    })
})

const confirmDelete = (id) => {
    serviceToDelete.value = id
    showDeleteModal.value = true
}

const handleDelete = () => {
    if (!serviceToDelete.value) return
    
    isDeleting.value = true
    router.delete(`/water-services/${serviceToDelete.value}`, {
        onFinish: () => {
            isDeleting.value = false
            showDeleteModal.value = false
            serviceToDelete.value = null
        }
    })
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const options = { day: 'numeric', month: 'short', year: 'numeric' }
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('id-ID', options)
    } catch (error) {
        return dateString
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount)
}

const getStatusLabel = (status) => {
    const labels = {
        order: 'Pesanan',
        processed: 'Diproses',
        completed: 'Selesai',
        cancelled: 'Dibatalkan'
    }
    return labels[status] || status
}

const getStatusBadgeClass = (status) => {
    const classes = {
        order: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        processed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    }
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getProgressWidth = (status) => {
    const widths = {
        order: '25%',
        processed: '75%',
        completed: '100%',
        cancelled: '0%'
    }
    return widths[status] || '0%'
}

const getProgressBarClass = (status) => {
    const classes = {
        order: 'bg-yellow-400',
        processed: 'bg-blue-400',
        completed: 'bg-green-400',
        cancelled: 'bg-red-400'
    }
    return classes[status] || 'bg-gray-400'
}
</script>

<template>
    <AppLayout>
        <Head title="Jasa Air - SAMOSIR" />

        <GeneralConfirmModal
            :show="showDeleteModal"
            title="Hapus Data Jasa Air"
            message="Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan."
            confirm-text="Ya, Hapus"
            type="danger"
            :is-loading="isDeleting"
            @close="showDeleteModal = false"
            @confirm="handleDelete"
        />

        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Jasa Air</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Kelola data jasa air di pelabuhan
                    </p>
                </div>
                <Link
                    href="/water-services/create"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-xs"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Jasa Air
                </Link>
            </div>

            <!-- Search & Filter Box -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative md:col-span-2">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nomor pesanan atau nama pemohon..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors text-xs"
                        />
                    </div>
                    <div class="relative">
                        <select
                            v-model="statusFilter"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors appearance-none cursor-pointer text-xs"
                        >
                            <option value="">Semua Status</option>
                            <option value="order">Pesanan</option>
                            <option value="processed">Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <p v-if="services.data && services.data.length > 0 && services.total > 0" class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                    Menampilkan {{ services.from }} - {{ services.to }} dari total {{ services.total }} data
                </p>

                <!-- Flash Messages -->
                <div v-if="flash.success" class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <p class="text-xs text-green-800 dark:text-green-200">{{ flash.success }}</p>
                </div>
                <div v-if="flash.error" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <p class="text-xs text-red-800 dark:text-red-200">{{ flash.error }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Pesanan</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapal</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemohon</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Volume</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progress</th>
                                <th class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="service in (services.data || [])" :key="service.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-white">{{ service.order_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">{{ service.vessel?.name || '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">{{ service.requester || '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">{{ formatDate(service.request_date) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">{{ service.volume }} Liter</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold text-gray-900 dark:text-white">{{ formatCurrency(service.total_payment) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium', getStatusBadgeClass(service.status)]">
                                        {{ getStatusLabel(service.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                            <div :class="getProgressBarClass(service.status)" :style="{ width: getProgressWidth(service.status) }" class="h-1.5 rounded-full transition-all"></div>
                                        </div>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ getProgressWidth(service.status) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="`/water-services/${service.id}`" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link :href="`/water-services/${service.id}/edit`" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 0L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button @click="confirmDelete(service.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!services.data || services.data.length === 0">
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                    <p class="text-xs">Tidak ada data jasa air</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="services.last_page > 1" class="bg-gray-50 dark:bg-gray-700 px-3 py-2 border-t border-gray-200 dark:border-gray-600 sm:px-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between">
                        <div class="text-xs text-gray-700 dark:text-gray-300 mb-2 sm:mb-0">
                            Halaman {{ services.current_page }} dari {{ services.last_page }}
                        </div>
                        <div class="flex space-x-2">
                            <Link v-if="services.prev_page_url" :href="services.prev_page_url" class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                Sebelumnya
                            </Link>
                            <Link v-if="services.next_page_url" :href="services.next_page_url" class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                Selanjutnya
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
