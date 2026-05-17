<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const props = defineProps({
    service: Object,
})

const form = useForm({
    vessel_id: props.service.vessel_id,
    renter_name: props.service.renter_name,
    service_date: props.service.service_date,
    start_time: props.service.start_time || '00:00',
    end_time: props.service.end_time || '00:00',
    duration: 0,
    officer: props.service.officer || '',
    treasurer: props.service.treasurer || '',
    notes: props.service.notes || '',
    total_amount: 0,
    items: props.service.items.map(item => ({
        id: item.id,
        equipment_name: item.equipment_name,
        label: getEquipmentLabel(item.equipment_name),
        quantity: item.quantity,
        unit_price: getUnitPrice(item.equipment_name),
        subtotal: item.subtotal || 0,
        disabled: item.equipment_name === 'timbangan' || item.equipment_name === 'ice_cruiser'
    }))
})

function getEquipmentLabel(name) {
    const labels = {
        keranjang_plastik: 'Keranjang Plastik',
        meja_sortir: 'Meja Sortir',
        gerobak: 'Gerobak',
        timbangan: 'Timbangan',
        ice_cruiser: 'Ice Cruiser',
    }
    return labels[name] || name
}

function getUnitPrice(name) {
    const hourlyPrices = {
        keranjang_plastik: 500,
        meja_sortir: 1000,
        gerobak: 500,
        timbangan: 500,
    }
    const unitPrices = {
        ice_cruiser: 100
    }
    return hourlyPrices[name] || unitPrices[name] || 0
}

const durationInHours = computed(() => {
    if (!form.start_time || !form.end_time) return 0
    
    const [h1, m1] = form.start_time.split(':').map(Number)
    const [h2, m2] = form.end_time.split(':').map(Number)

    const mulai = new Date(0, 0, 0, h1, m1)
    const selesai = new Date(0, 0, 0, h2, m2)

    const durasi = (selesai - mulai) / (1000 * 60 * 60)
    return durasi > 0 ? durasi : 0
})

const durationFormatted = computed(() => {
    const totalMinutes = Math.round(durationInHours.value * 60)
    const hours = Math.floor(totalMinutes / 60)
    const minutes = totalMinutes % 60
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
})

// Update subtotals when duration or quantities change
watch([durationInHours, () => form.items], () => {
    form.items.forEach(item => {
        if (item.equipment_name === 'ice_cruiser') {
            item.subtotal = item.quantity * item.unit_price
        } else {
            item.subtotal = item.quantity * durationInHours.value * item.unit_price
        }
    })
    form.duration = durationInHours.value
}, { deep: true, immediate: true })

const totalAmount = computed(() => {
    return form.items.reduce((sum, item) => sum + item.subtotal, 0)
})

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
    form.total_amount = totalAmount.value
    form.post(`/equipment-services/${props.service.id}/calculate`)
}
</script>

<template>
    <AppLayout>
        <Head title="Proses Perhitungan - SAMOSIR" />

        <div class="max-w-5xl mx-auto py-6 px-4">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Proses Perhitungan</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Selesaikan perhitungan biaya pemakaian peralatan
                    </p>
                </div>
                <Link
                    href="/equipment-services"
                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors text-xs"
                >
                    Kembali
                </Link>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                <form @submit.prevent="submit" class="p-6">
                    <!-- Order Info Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nomor Order</label>
                                <input
                                    :value="service.order_number"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Penyewa</label>
                                <input
                                    :value="form.renter_name"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Order</label>
                                <input
                                    :value="formatDateIndo(form.service_date)"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 cursor-not-allowed focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Jam Mulai</label>
                                    <input
                                        v-model="form.start_time"
                                        type="time"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Jam Selesai</label>
                                    <input
                                        v-model="form.end_time"
                                        type="time"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Durasi (Jam)</label>
                                <input
                                    :value="durationFormatted"
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-blue-600 font-bold text-sm cursor-not-allowed focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Usage Section -->
                    <div class="mb-8">
                        <h3 class="text-[10px] font-bold text-gray-900 dark:text-white uppercase tracking-widest mb-4 pb-1 border-b border-gray-100 dark:border-gray-700">
                            Pemakaian Peralatan
                        </h3>
                        
                        <div class="space-y-3">
                            <div v-for="(item, index) in form.items" :key="index" class="flex flex-col md:flex-row items-start md:items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="w-full md:w-1/3">
                                    <label class="block text-[10px] font-medium text-gray-600 dark:text-gray-400 mb-1">{{ item.label }}</label>
                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        :disabled="item.disabled"
                                        :class="[
                                            'w-full md:w-24 px-3 py-1.5 border rounded-lg text-xs transition-all',
                                            item.disabled 
                                                ? 'bg-gray-100 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-gray-400 cursor-not-allowed' 
                                                : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500'
                                        ]"
                                    />
                                </div>
                                <div class="w-full md:flex-1 flex items-center gap-3">
                                    <span class="text-[10px] text-gray-500">Biaya Pemakaian</span>
                                    <input
                                        :value="item.subtotal"
                                        type="number"
                                        readonly
                                        class="flex-1 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 text-right font-mono"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="mt-6 flex flex-col md:flex-row justify-end items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Total Keseluruhan</span>
                            <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg">
                                <span class="text-lg font-black text-blue-600 dark:text-blue-400">
                                    {{ formatCurrency(totalAmount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Petugas Lapangan</label>
                            <input
                                v-model="form.officer"
                                type="text"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Bendahara</label>
                            <input
                                v-model="form.treasurer"
                                type="text"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow transition-all disabled:opacity-50 text-xs"
                        >
                            {{ form.processing ? 'Memproses...' : 'Simpan Perhitungan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
