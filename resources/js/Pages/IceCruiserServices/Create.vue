<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    vessels: Array,
})

const equipmentItems = [
    { key: 'ice_cruiser', label: 'Ice Cruiser' },
]

const form = useForm({
    vessel_id: '',
    renter_name: '',
    service_date: new Date().toISOString().split('T')[0],
    officer: '',
    notes: '',
    status: 'order',
    items: equipmentItems.map(item => ({
        equipment_name: item.key,
        label: item.label,
        quantity: item.key === 'ice_cruiser' ? 1 : 0, // Default 1 for Ice Cruiser since it's this menu
        unit_price: 0,
        notes: ''
    }))
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
        // Match controller's expected field names (handling both single item and multi-item compatibility)
        field_officer: form.officer,
        // For backward compatibility if controller isn't updated yet to handle 'items'
        quantity: form.items.find(i => i.equipment_name === 'ice_cruiser')?.quantity || 0,
        unit_price: form.items.find(i => i.equipment_name === 'ice_cruiser')?.unit_price || 0,
    })).post('/ice-cruiser-services', {
        onSuccess: () => {
            form.reset()
        }
    })
}

const resetForm = () => {
    form.reset()
}
</script>

<template>
    <AppLayout>
        <Head title="Tambah Jasa Ice Cruiser - SAMOSIR" />

        <div class="max-w-5xl mx-auto py-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="p-8">
                    <form @submit.prevent="submit">
                        <!-- Top Section: Order Info -->
                        <div class="space-y-4 mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nomor Order</label>
                                <div class="md:col-span-2">
                                    <input
                                        type="text"
                                        value="Auto-generated"
                                        disabled
                                        class="w-full md:w-64 px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-500 cursor-not-allowed"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Penyewa</label>
                                <div class="md:col-span-2">
                                    <select
                                        v-model="form.vessel_id"
                                        class="w-full md:w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        @change="e => {
                                            const selected = vessels.find(v => v.id == e.target.value);
                                            form.renter_name = selected ? selected.vessel_name : '';
                                        }"
                                    >
                                        <option value="">Pilih Penyewa</option>
                                        <option v-for="vessel in vessels" :key="vessel.id" :value="vessel.id">
                                            {{ vessel.vessel_name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.vessel_id" class="mt-1 text-xs text-red-600">{{ form.errors.vessel_id }}</p>
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

                        <!-- Bottom Section: Officer -->
                        <div class="mb-8">
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
                                Simpan
                            </button>
                            <button
                                type="button"
                                @click="resetForm"
                                class="inline-flex items-center px-6 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-lg transition-all duration-200 shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Reset
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
