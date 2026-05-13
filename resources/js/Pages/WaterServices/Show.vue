<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import GeneralConfirmModal from '../../Components/GeneralConfirmModal.vue'

const props = defineProps({
    service: Object,
})

const showConfirmModal = ref(false)
const isProcessing = ref(false)

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount || 0)
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const options = { day: 'numeric', month: 'long', year: 'numeric' }
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('id-ID', options)
    } catch (error) {
        return dateString
    }
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

const handleComplete = () => {
    isProcessing.value = true
    router.post(`/water-services/${props.service.id}/complete`, {}, {
        onFinish: () => {
            isProcessing.value = false
            showConfirmModal.value = false
        }
    })
}

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Jasa Air', href: route('water-services.index') },
    { title: 'Detail Order', href: '#' }
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Detail Jasa Air Tawar - SAMOSIR" />

        <GeneralConfirmModal
            :show="showConfirmModal"
            title="Selesaikan Orderan"
            message="Apakah Anda yakin ingin menyelesaikan orderan ini? Status akan berubah menjadi selesai."
            confirm-text="Ya, Selesaikan"
            type="primary"
            :is-loading="isProcessing"
            @close="showConfirmModal = false"
            @confirm="handleComplete"
        />

        <div class="max-w-5xl mx-auto py-6 px-4">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Jasa Air Tawar</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Informasi lengkap pesanan #{{ service.order_number }}
                    </p>
                </div>
                <div class="flex gap-2 text-xs">
                    <Link
                        href="/water-services"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors font-medium"
                    >
                        Kembali
                    </Link>
                    <Link
                        v-if="service.status === 'order'"
                        :href="`/water-services/${service.id}/edit`"
                        class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors font-medium"
                    >
                        Edit Pesanan
                    </Link>
                    <button
                        v-if="service.status === 'processed'"
                        @click="showConfirmModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-bold shadow-sm"
                    >
                        Bayar / Selesaikan
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info Card -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wider">Informasi Umum</h3>
                            <span :class="['px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tight', getStatusBadgeClass(service.status)]">
                                {{ getStatusLabel(service.status) }}
                            </span>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-xs">
                                <div class="space-y-3">
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Nama Kapal / Pemohon</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ service.vessel?.vessel_name || service.requester }}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Tanggal Permintaan</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ formatDate(service.request_date) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Volume</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">{{ service.volume }} M3</span>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Harga / M3</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ formatCurrency(service.price) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Pelaksana Lapangan</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ service.field_officer || '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wider">Ringkasan Pembayaran</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                <div class="text-center md:text-left">
                                    <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mb-1">Total Pembayaran</p>
                                    <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ formatCurrency(service.total_payment) }}</p>
                                </div>
                                <div v-if="service.status === 'completed'" class="flex items-center gap-2 px-4 py-2 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-800">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span class="text-sm font-bold text-green-700 dark:text-green-300 uppercase">Lunas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="service.notes" class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Keterangan</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ service.notes }}</p>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <!-- Progress Card -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Progress Order</h4>
                        <div class="flex items-center gap-4 mb-2">
                            <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                <div :class="['h-full transition-all duration-1000', getProgressBarClass(service.status)]" :style="{ width: getProgressWidth(service.status) }"></div>
                            </div>
                            <span class="text-sm font-black text-gray-700 dark:text-gray-300">{{ getProgressWidth(service.status) }}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 italic">
                            Status: <span class="font-bold text-gray-700 dark:text-gray-300 uppercase">{{ getStatusLabel(service.status) }}</span>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-1 gap-2">
                        <button 
                            @click="window.open(`/water-services/${service.id}/print`, '_blank')"
                            class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600 text-white rounded-xl transition-all font-bold"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Cetak Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
