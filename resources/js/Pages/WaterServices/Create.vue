<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    vessels: Array,
    nextOrderNumber: String,
})

const form = useForm({
    order_number: props.nextOrderNumber,
    vessel_id: '',
    request_date: new Date().toISOString().split('T')[0],
    requester: '',
    volume: 0,
    price: 0,
    field_officer: '',
    notes: '',
    status: 'order',
})

const totalAmount = computed(() => form.volume * form.price)

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount || 0)
}

const submit = () => {
    form.post('/water-services', {
        onSuccess: () => {
            form.reset()
        }
    })
}

const reset = () => {
    form.reset()
}


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tambah Order Jasa Air Tawar - SAMOSIR" />

        <div class="max-w-5xl mx-auto py-6 px-4">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Order Jasa Air Tawar</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Gunakan form ini untuk menambah data Jasa Air Tawar.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <form @submit.prevent="submit" class="p-8">
                    <div class="space-y-6">
                        <!-- Nomor Order -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nomor Order</label>
                            <div class="md:col-span-2">
                                <input
                                    v-model="form.order_number"
                                    type="text"
                                    readonly
                                    class="w-full md:w-1/3 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-500 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Nama Kapal -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Kapal</label>
                            <div class="md:col-span-2">
                                <select
                                    v-model="form.vessel_id"
                                    :class="[
                                        'w-full px-4 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none',
                                        form.errors.vessel_id ? 'border-red-500' : 'border-gray-200 dark:border-gray-600',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                >
                                    <option value="">- Pilih Kapal -</option>
                                    <option v-for="vessel in vessels" :key="vessel.id" :value="vessel.id">
                                        {{ vessel.name || vessel.vessel_name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.vessel_id" class="mt-1 text-xs text-red-500">{{ form.errors.vessel_id }}</p>
                            </div>
                        </div>

                        <!-- Tanggal Permintaan -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal Permintaan</label>
                            <div class="md:col-span-2">
                                <input
                                    v-model="form.request_date"
                                    type="date"
                                    :class="[
                                        'w-full md:w-1/3 px-4 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none',
                                        form.errors.request_date ? 'border-red-500' : 'border-gray-200 dark:border-gray-600',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.request_date" class="mt-1 text-xs text-red-500">{{ form.errors.request_date }}</p>
                            </div>
                        </div>

                        <!-- Volume -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Volume (M3)</label>
                            <div class="md:col-span-2">
                                <div class="relative w-full md:w-1/3">
                                    <input
                                        v-model.number="form.volume"
                                        type="number"
                                        step="0.01"
                                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <span class="text-xs font-bold text-gray-400">M3</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Harga -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Harga /M3</label>
                            <div class="md:col-span-2">
                                <div class="relative w-full md:w-1/2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <span class="text-xs font-bold text-gray-400">Rp</span>
                                    </div>
                                    <input
                                        v-model.number="form.price"
                                        type="number"
                                        class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Jumlah Pembayaran -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah Pembayaran</label>
                            <div class="md:col-span-2">
                                <div class="relative w-full md:w-1/2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <span class="text-xs font-bold text-gray-400">Rp</span>
                                    </div>
                                    <input
                                        :value="totalAmount"
                                        type="number"
                                        readonly
                                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-blue-600 dark:text-blue-400 cursor-not-allowed outline-none"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 pt-2">Keterangan</label>
                            <div class="md:col-span-2">
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none resize-none"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Pelaksana Lapangan -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pelaksana Lapangan</label>
                            <div class="md:col-span-2">
                                <input
                                    v-model="form.field_officer"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"
                                />
                            </div>
                        </div>

                        <!-- Pemohon -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pemohon</label>
                            <div class="md:col-span-2">
                                <input
                                    v-model="form.requester"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="mt-10 flex items-center gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all font-bold text-sm shadow-lg shadow-blue-200 dark:shadow-none disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            Simpan
                        </button>
                        <button
                            type="button"
                            @click="reset"
                            class="inline-flex items-center px-6 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all font-bold text-sm shadow-lg shadow-red-200 dark:shadow-none"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
