<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    service: Object,
})

const form = useForm({
    volume: props.service.volume || 0,
    price: props.service.price || 0,
    field_officer: props.service.field_officer || '',
    treasurer: props.service.treasurer || '',
    notes: props.service.notes || '',
})

const totalPayment = computed(() => form.volume * form.price)

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount || 0)
}

const formatDateIndo = (dateStr) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr)
    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${day}-${month}-${year}`
}

const submit = () => {
    form.post(`/water-services/${props.service.id}/calculate`)
}
</script>

<template>
    <AppLayout>
        <Head title="Proses Perhitungan Jasa Air - SAMOSIR" />

        <div class="max-w-5xl mx-auto py-6 px-4">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Proses Perhitungan Jasa Air</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Selesaikan perhitungan biaya pemakaian air bersih
                    </p>
                </div>
                <Link
                    href="/water-services"
                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors text-xs"
                >
                    Kembali
                </Link>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                <form @submit.prevent="submit" class="p-6">
                    <!-- Order Info Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 pb-8 border-b border-gray-100 dark:border-gray-700">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nomor Order</label>
                                <input
                                    :value="service.order_number"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-600 dark:text-gray-400 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Kapal</label>
                                <input
                                    :value="service.vessel?.vessel_name || '-'"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-600 dark:text-gray-400 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Pemohon</label>
                                <input
                                    :value="service.requester || '-'"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-600 dark:text-gray-400 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Pesanan</label>
                                <input
                                    :value="formatDateIndo(service.request_date)"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-600 dark:text-gray-400 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Section -->
                    <div class="mb-8">
                        <h3 class="text-[10px] font-bold text-gray-900 dark:text-white uppercase tracking-widest mb-4 pb-1 border-b border-gray-100 dark:border-gray-700">
                            Data Pemakaian Air
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-12 items-center gap-6">
                            <div class="md:col-span-4">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Volume (Liter)</label>
                                <input
                                    v-model.number="form.volume"
                                    type="number"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all"
                                    placeholder="0"
                                />
                                <p v-if="form.errors.volume" class="mt-1 text-[10px] text-red-600">{{ form.errors.volume }}</p>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Harga per Liter (IDR)</label>
                                <input
                                    v-model.number="form.price"
                                    type="number"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all"
                                    placeholder="0"
                                />
                                <p v-if="form.errors.price" class="mt-1 text-[10px] text-red-600">{{ form.errors.price }}</p>
                            </div>
                            <div class="md:col-span-4 flex flex-col justify-end">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Pembayaran</label>
                                <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg text-right">
                                    <span class="text-lg font-black text-blue-600 dark:text-blue-400">
                                        {{ formatCurrency(totalPayment) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personnel Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Petugas Lapangan</label>
                            <input
                                v-model="form.field_officer"
                                type="text"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="Nama Petugas"
                            />
                            <p v-if="form.errors.field_officer" class="mt-1 text-[10px] text-red-600">{{ form.errors.field_officer }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Bendahara</label>
                            <input
                                v-model="form.treasurer"
                                type="text"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="Nama Bendahara"
                            />
                            <p v-if="form.errors.treasurer" class="mt-1 text-[10px] text-red-600">{{ form.errors.treasurer }}</p>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="mb-8">
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Catatan</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 resize-none"
                            placeholder="Catatan tambahan..."
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all disabled:opacity-50 text-xs"
                        >
                            <span v-if="form.processing">Memproses...</span>
                            <span v-else>Simpan Perhitungan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
