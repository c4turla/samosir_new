<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    service: Object,
    vessels: Array,
})

const equipmentItems = [
    { key: 'ice_cruiser', label: 'Ice Cruiser' },
]

const form = useForm({
    vessel_id: props.service.vessel_id,
    renter_name: props.service.renter_name,
    service_date: props.service.service_date.split('T')[0],
    officer: props.service.officer || '',
    notes: props.service.notes || '',
    status: props.service.status,
    billing_number: props.service.billing_number || '',
    items: equipmentItems.map(configItem => {
        const existingItem = props.service.items.find(item => item.equipment_name === configItem.key)
        return {
            equipment_name: configItem.key,
            label: configItem.label,
            quantity: existingItem ? existingItem.quantity : 0,
            unit_price: existingItem ? existingItem.unit_price : 0,
            notes: existingItem ? (existingItem.notes || '') : ''
        }
    })
})

const submit = () => {
    // Filter out items with 0 quantity
    const filteredItems = form.items.filter(item => item.quantity > 0)
    
    if (filteredItems.length === 0) {
        alert('Mohon isi minimal satu peralatan yang digunakan.')
        return
    }

    form.transform((data) => ({
        ...data,
        items: filteredItems,
        field_officer: form.officer
    })).put(`/ice-cruiser-services/${props.service.id}`, {
        onSuccess: () => {
            // Success
        }
    })
}

const cancel = () => {
    window.location.href = '/ice-cruiser-services'
}
</script>

<template>
    <AppLayout>
        <Head title="Edit Jasa Ice Cruiser - SAMOSIR" />

        <div class="max-w-5xl mx-auto py-6">
            <div class="mb-6">
                <Link href="/ice-cruiser-services" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Daftar
                </Link>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-8">
                    <form @submit.prevent="submit">
                        <!-- Top Section: Order Info -->
                        <div class="space-y-4 mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nomor Order</label>
                                <div class="md:col-span-2">
                                    <input
                                        type="text"
                                        :value="service.order_number"
                                        disabled
                                        class="w-full md:w-64 px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-500 cursor-not-allowed"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Penyewa</label>
                                <div class="md:col-span-2">
                                    <input
                                        v-model="form.renter_name"
                                        type="text"
                                        placeholder="Masukkan nama penyewa"
                                        class="w-full md:w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    />
                                    <p v-if="form.errors.renter_name" class="mt-1 text-xs text-red-600">{{ form.errors.renter_name }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal Order</label>
                                <div class="md:col-span-2">
                                    <div class="relative w-full md:w-64">
                                        <input
                                            v-model="form.service_date"
                                            type="date"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>
                                    <p v-if="form.errors.service_date" class="mt-1 text-xs text-red-600">{{ form.errors.service_date }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Middle Section: Equipment Usage -->
                        <div class="mb-8">
                            <div class="flex items-center gap-4 mb-6">
                                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Pemakaian Peralatan (Ice Cruiser)</h2>
                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(item, index) in form.items" :key="item.equipment_name" class="grid grid-cols-1 md:grid-cols-12 items-center gap-4">
                                    <div class="md:col-span-3">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300" :class="{'text-indigo-600 font-bold': item.equipment_name === 'ice_cruiser'}">{{ item.label }}</label>
                                    </div>
                                    <div class="md:col-span-2">
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="0"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="0"
                                            :class="{'ring-2 ring-indigo-500/20 border-indigo-300': item.equipment_name === 'ice_cruiser'}"
                                        />
                                    </div>
                                    <div class="md:col-span-1 text-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Keterangan</span>
                                    </div>
                                    <div class="md:col-span-6">
                                        <input
                                            v-model="item.notes"
                                            type="text"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="Keterangan opsional"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Section: Officer & Status -->
                        <div class="mb-8 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-12 items-center gap-4">
                                <div class="md:col-span-3">
                                     <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Petugas</label>
                                </div>
                                <div class="md:col-span-9">
                                     <input
                                         v-model="form.officer"
                                         type="text"
                                         class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                         placeholder="Nama Petugas"
                                     />
                                     <p v-if="form.errors.field_officer" class="mt-1 text-xs text-red-600">{{ form.errors.field_officer }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 items-center gap-4">
                                <div class="md:col-span-3">
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status</label>
                                </div>
                                <div class="md:col-span-9">
                                    <select
                                        v-model="form.status"
                                        class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="order">Pesanan</option>
                                        <option value="processed">Diproses</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                    </select>
                                    <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">{{ form.errors.status }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 items-center gap-4">
                                <div class="md:col-span-3">
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nomor Billing</label>
                                </div>
                                <div class="md:col-span-9">
                                    <input
                                        v-model="form.billing_number"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan Nomor Billing (Opsional)"
                                     />
                                     <p v-if="form.errors.billing_number" class="mt-1 text-xs text-red-600">{{ form.errors.billing_number }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 items-start gap-4 pt-2">
                                <div class="md:col-span-3">
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan</label>
                                </div>
                                <div class="md:col-span-9">
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Catatan tambahan..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition-all duration-200 shadow-sm disabled:opacity-50"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Simpan Perubahan
                            </button>
                            <button
                                type="button"
                                @click="cancel"
                                class="inline-flex items-center px-6 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-lg transition-all duration-200 shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
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
