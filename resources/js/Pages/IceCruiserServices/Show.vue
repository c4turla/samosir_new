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

const getEquipmentLabel = (name) => {
    const labels = {
        keranjang_plastik: 'Keranjang Plastik',
        meja_sortir: 'Meja Sortir',
        gerobak: 'Gerobak',
        timbangan: 'Timbangan',
        ice_cruiser: 'Ice Cruiser',
    }
    return labels[name] || name
}

const handleComplete = () => {
    isProcessing.value = true
    router.post(`/ice-cruiser-services/${props.service.id}/complete`, {}, {
        onFinish: () => {
            isProcessing.value = false
            showConfirmModal.value = false
        }
    })
}

const printOrder = () => {
    window.open(`/ice-cruiser-services/${props.service.id}/print`, '_blank')
}

const printCalculation = () => {
    window.open(`/ice-cruiser-services/${props.service.id}/print-calculation`, '_blank')
}
</script>

<template>
    <AppLayout>
        <Head title="Detail Ice Cruiser - SAMOSIR" />

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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Ice Cruiser</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Informasi lengkap pesanan #{{ service.order_number }}
                    </p>
                </div>
                <div class="flex gap-2 text-xs">
                    <Link
                        href="/ice-cruiser-services"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors font-medium"
                    >
                        Kembali
                    </Link>
                    <Link
                        v-if="service.status === 'order'"
                        :href="`/ice-cruiser-services/${service.id}/edit`"
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
                                        <span class="text-gray-500 font-medium">Nama Kapal / Penyewa</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ service.vessel?.name || service.renter_name }}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Tanggal Order</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ formatDate(service.service_date) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Durasi</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">{{ service.duration || 0 }} Jam</span>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Jam Mulai</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ service.start_time || '-' }}</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                        <span class="text-gray-500 font-medium">Jam Selesai</span>
                                        <span class="text-gray-900 dark:text-white font-semibold">{{ service.end_time || '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wider">Rincian Peralatan</h3>
                        </div>
                        <div class="p-0 overflow-x-auto text-xs">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 font-bold border-b border-gray-100 dark:border-gray-700">
                                        <th class="px-6 py-3">Nama Alat</th>
                                        <th class="px-6 py-3 text-center">Jumlah</th>
                                        <th class="px-6 py-3 text-right">Harga Satuan</th>
                                        <th class="px-6 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                    <tr v-for="item in service.items" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ getEquipmentLabel(item.equipment_name) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">{{ item.quantity }}</td>
                                        <td class="px-6 py-4 text-right text-gray-500">{{ formatCurrency(item.unit_price) }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(item.subtotal) }}</td>
                                    </tr>
                                    <tr v-if="!service.items?.length">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada item yang dipilih</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-blue-50/30 dark:bg-blue-900/10 font-bold border-t border-blue-100 dark:border-blue-800/50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right text-gray-600 dark:text-gray-300 uppercase tracking-wider">Total Keseluruhan</td>
                                        <td class="px-6 py-4 text-right text-lg text-blue-600 dark:text-blue-400">{{ formatCurrency(service.total_amount) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info Cards -->
                <div class="space-y-6 text-xs">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white text-[10px] uppercase tracking-widest mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">Pihak Terkait</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-400 mb-1">Petugas Lapangan</label>
                                <p class="text-gray-900 dark:text-white font-semibold">{{ service.officer || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Bendahara</label>
                                <p class="text-gray-900 dark:text-white font-semibold">{{ service.treasurer || '-' }}</p>
                            </div>
                            <div v-if="service.billing_number">
                                <label class="block text-gray-400 mb-1">Nomor Billing</label>
                                <p class="text-blue-600 dark:text-blue-400 font-bold text-sm bg-blue-50 dark:bg-blue-900/20 px-2.5 py-1 rounded inline-block">{{ service.billing_number }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white text-[10px] uppercase tracking-widest mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">Catatan Tambahan</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed italic">
                            {{ service.notes || 'Tidak ada catatan tambahan.' }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-1 gap-2">
                        <button 
                            @click="printOrder"
                            class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600 text-white rounded-xl transition-all font-bold"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Cetak Order
                        </button>
                        <button 
                            v-if="service.status !== 'order'"
                            @click="printCalculation"
                            class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl transition-all font-bold"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Cetak Perhitungan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
