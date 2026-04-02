<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    vessels: {
        type: Array,
        required: true
    },
    landingSites: {
        type: Array,
        required: true
    }
})

const form = useForm({
    vessel_id: '',
    destination: '',
    crew_count: null,
    departure_date: new Date().toISOString().split('T')[0],
    departure_time: '',
    departure_datetime: null,
    return_datetime: null,
    landing_site_id: '',
    syahbandar: '',
    administrative_officer: '',
    ice_supply: null,
    water_supply: null,
    diesel_supply: null,
    oil_supply: null,
    gasoline_supply: null,
    other_supplies: '',
    notes: '',
    status: 'MENUNGGU',
    approval_status: false,
})

const submit = () => {
    form.post('/departures', {
        onSuccess: () => {
            form.reset()
        }
    })
}

const cancel = () => {
    form.reset()
    window.location.href = '/departures'
}
</script>

<template>
    <AppLayout>
        <Head title="Tambah Keberangkatan Kapal - SAMOSIR" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Keberangkatan Kapal</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Catat keberangkatan kapal baru di pelabuhan
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <form @submit.prevent="submit">
                    <!-- Informasi Kapal -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Informasi Kapal
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kapal <span class="text-red-500">*</span>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Hanya kapal yang sudah pernah kedatangan yang dapat dipilih</p>
                                </label>
                                <select
                                    v-model="form.vessel_id"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.vessel_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                >
                                    <option value="">Pilih Kapal</option>
                                    <option v-for="vessel in vessels" :key="vessel.id" :value="vessel.id">
                                        {{ vessel.vessel_name }} ({{ vessel.license_number }})
                                    </option>
                                </select>
                                <p v-if="form.errors.vessel_id" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.vessel_id }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tujuan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.destination"
                                    type="text"
                                    placeholder="Contoh: Laut Jawa"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.destination ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <p v-if="form.errors.destination" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.destination }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Jumlah Awak Kapal
                                </label>
                                <input
                                    v-model.number="form.crew_count"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.crew_count ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.crew_count" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.crew_count }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Waktu Keberangkatan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Waktu Keberangkatan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.departure_date"
                                    type="date"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.departure_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                />
                                <p v-if="form.errors.departure_date" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.departure_date }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu</label>
                                <input
                                    v-model="form.departure_time"
                                    type="time"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.departure_time ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.departure_time" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.departure_time }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal & Waktu Keberangkatan</label>
                                <input
                                    v-model="form.departure_datetime"
                                    type="datetime-local"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.departure_datetime ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.departure_datetime" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.departure_datetime }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Perkiraan Tanggal & Waktu Kembali</label>
                                <input
                                    v-model="form.return_datetime"
                                    type="datetime-local"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.return_datetime ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.return_datetime" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.return_datetime }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dermaga -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Lokasi Dermaga</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dermaga</label>
                            <select
                                v-model="form.landing_site_id"
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    form.errors.landing_site_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            >
                                <option value="">Pilih Dermaga</option>
                                <option v-for="site in landingSites" :key="site.id" :value="site.id">
                                    {{ site.site_name }}
                                </option>
                            </select>
                            <p v-if="form.errors.landing_site_id" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.landing_site_id }}</p>
                        </div>
                    </div>

                    <!-- Informasi Petugas -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Informasi Petugas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Syahbandar</label>
                                <input
                                    v-model="form.syahbandar"
                                    type="text"
                                    placeholder="Nama syahbandar"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.syahbandar ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.syahbandar" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.syahbandar }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Pejabat Administrasi</label>
                                <input
                                    v-model="form.administrative_officer"
                                    type="text"
                                    placeholder="Nama pejabat administrasi"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.administrative_officer ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.administrative_officer" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.administrative_officer }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Suplai Kapal -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Suplai Kapal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suplai Es (kg)</label>
                                <input
                                    v-model.number="form.ice_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.ice_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.ice_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.ice_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suplai Air (liter)</label>
                                <input
                                    v-model.number="form.water_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.water_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.water_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.water_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suplai Solar (liter)</label>
                                <input
                                    v-model.number="form.diesel_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.diesel_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.diesel_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.diesel_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suplai Minyak (liter)</label>
                                <input
                                    v-model.number="form.oil_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.oil_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.oil_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.oil_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suplai Bensin (liter)</label>
                                <input
                                    v-model.number="form.gasoline_supply"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.gasoline_supply ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.gasoline_supply" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.gasoline_supply }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Suplai Lainnya</label>
                                <input
                                    v-model="form.other_supplies"
                                    type="text"
                                    placeholder="Suplai lainnya"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.other_supplies ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                />
                                <p v-if="form.errors.other_supplies" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.other_supplies }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status Keberangkatan <span class="text-red-500">*</span></label>
                                <select
                                    v-model="form.status"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                                        form.errors.status ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                        'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                    ]"
                                    required
                                >
                                    <option value="MENUNGGU">Menunggu</option>
                                    <option value="BERANGKAT">Berangkat</option>
                                    <option value="KEMBALI">Kembali</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Catatan</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Tambahkan catatan..."
                                :class="[
                                    'w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none',
                                    form.errors.notes ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500',
                                    'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
                                ]"
                            ></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ form.errors.notes }}</p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm disabled:opacity-50 flex items-center gap-2">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
