<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    service: Object,
    vessels: Array,
    equipmentItems: Array,
})

const form = useForm({
    vessel_id: props.service.vessel_id,
    equipment_item_id: props.service.equipment_item_id,
    service_date: props.service.service_date,
    renter_name: props.service.renter_name,
    quantity: props.service.quantity,
    unit_price: props.service.unit_price,
    notes: props.service.notes,
    status: props.service.status,
})

const totalAmount = computed(() => form.quantity * form.unit_price)

const submit = () => {
    form.put(`/equipment-services/${props.service.id}`, {
        onSuccess: () => {
            // Success
        }
    })
}

const cancel = () => {
    window.location.href = '/equipment-services'
}
</script>

<template>
    <AppLayout>
        <Head title="Edit Jasa Peralatan - SAMOSIR" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-4">
                <Link href="/equipment-services" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </Link>
            </div>

            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Jasa Peralatan</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Edit jasa peralatan - No. Pesanan: {{ service.order_number }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kapal</label>
                            <select
                                v-model="form.vessel_id"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.vessel_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            >
                                <option value="">Pilih Kapal</option>
                                <option v-for="vessel in vessels" :key="vessel.id" :value="vessel.id">
                                    {{ vessel.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Peralatan</label>
                            <select
                                v-model="form.equipment_item_id"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.equipment_item_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            >
                                <option value="">Pilih Peralatan</option>
                                <option v-for="item in equipmentItems" :key="item.id" :value="item.id">
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Penyewa</label>
                            <input
                                v-model="form.renter_name"
                                type="text"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.renter_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                            <input
                                v-model="form.service_date"
                                type="date"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.service_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah</label>
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                min="1"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.quantity ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga per Unit</label>
                            <input
                                v-model.number="form.unit_price"
                                type="number"
                                min="0"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.unit_price ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select
                                v-model="form.status"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.status ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            >
                                <option value="order">Pesanan</option>
                                <option value="processed">Diproses</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Total:</span>
                            <span class="text-xl font-bold text-blue-600">{{ formatCurrency(totalAmount) }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            :class="[
                                'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none',
                                form.errors.notes ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                                'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                            ]"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                        <button type="button" @click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script>
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount || 0)
}
</script>
