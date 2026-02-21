<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { computed, ref } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const photoPreview = ref(null)
const photoInput = ref(null)

const form = useForm({
    vessel_name: '',
    owner_name: '',
    license_number: '',
    gt: '',
    fishing_gear: '',
    selar_mark: '',
    vessel_type: '',
    sipi_date: '',
    sipi_end_date: '',
    length: '',
    loa: '',
    siup_number: '',
    vessel_photo: '',
    qr_code: '',
    approval_status: 'pending',
    notes: '',
})

const handlePhotoChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.vessel_photo = file
        const reader = new FileReader()
        reader.onload = (e) => {
            photoPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const submit = () => {
    form.post('/vessels', {
        forceFormData: true,
        onSuccess: () => {
            form.reset()
            photoPreview.value = null
        }
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Tambah Kapal - SAMOSIR" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <Link
                    href="/vessels"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Daftar
                </Link>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Tambah Kapal</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Isi formulir di bawah untuk menambahkan kapal baru
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Kapal -->
                        <div>
                            <label for="vessel_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Kapal <span class="text-red-500" style="color: #ef4444;">*</span>
                            </label>
                            <input
                                id="vessel_name"
                                type="text"
                                v-model="form.vessel_name"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: KM. Samosir Raya"
                            />
                            <div v-if="form.errors.vessel_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.vessel_name }}
                            </div>
                        </div>

                        <!-- Nama Pemilik -->
                        <div>
                            <label for="owner_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Pemilik <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="owner_name"
                                type="text"
                                v-model="form.owner_name"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: Budi Santoso"
                            />
                            <div v-if="form.errors.owner_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.owner_name }}
                            </div>
                        </div>

                        <!-- Nomor Lisensi -->
                        <div>
                            <label for="license_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nomor Lisensi
                            </label>
                            <input
                                id="license_number"
                                type="text"
                                v-model="form.license_number"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: PK.123.456"
                            />
                            <div v-if="form.errors.license_number" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.license_number }}
                            </div>
                        </div>

                        <!-- GT -->
                        <div>
                            <label for="gt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                GT (Gross Tonnage)
                            </label>
                            <input
                                id="gt"
                                type="text"
                                v-model="form.gt"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: 30"
                            />
                            <div v-if="form.errors.gt" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.gt }}
                            </div>
                        </div>

                        <!-- Alat Tangkap -->
                        <div>
                            <label for="fishing_gear" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Alat Tangkap
                            </label>
                            <input
                                id="fishing_gear"
                                type="text"
                                v-model="form.fishing_gear"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: Gill Net"
                            />
                            <div v-if="form.errors.fishing_gear" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.fishing_gear }}
                            </div>
                        </div>

                        <!-- Tanda Selar -->
                        <div>
                            <label for="selar_mark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tanda Selar
                            </label>
                            <input
                                id="selar_mark"
                                type="text"
                                v-model="form.selar_mark"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: S-123"
                            />
                            <div v-if="form.errors.selar_mark" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.selar_mark }}
                            </div>
                        </div>

                        <!-- Jenis Kapal -->
                        <div>
                            <label for="vessel_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jenis Kapal
                            </label>
                            <div class="relative">
                                <select
                                    id="vessel_type"
                                    v-model="form.vessel_type"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors appearance-none cursor-pointer"
                                >
                                    <option value="">Pilih Jenis Kapal</option>
                                    <option value="Penangkap">Penangkap</option>
                                    <option value="Pengangkut/Pengumpul">Pengangkut/Pengumpul</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <div v-if="form.errors.vessel_type" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.vessel_type }}
                            </div>
                        </div>

                        <!-- Length -->
                        <div>
                            <label for="length" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Panjang (Length)
                            </label>
                            <input
                                id="length"
                                type="text"
                                v-model="form.length"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: 15"
                            />
                            <div v-if="form.errors.length" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.length }}
                            </div>
                        </div>

                        <!-- LOA -->
                        <div>
                            <label for="loa" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                LOA
                            </label>
                            <input
                                id="loa"
                                type="text"
                                v-model="form.loa"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: 12"
                            />
                            <div v-if="form.errors.loa" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.loa }}
                            </div>
                        </div>

                        <!-- Nomor SIUP -->
                        <div>
                            <label for="siup_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nomor SIUP
                            </label>
                            <input
                                id="siup_number"
                                type="text"
                                v-model="form.siup_number"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: SIUP-12345"
                            />
                            <div v-if="form.errors.siup_number" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.siup_number }}
                            </div>
                        </div>

                        <!-- Tanggal SIPI -->
                        <div>
                            <label for="sipi_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tanggal SIPI
                            </label>
                            <input
                                id="sipi_date"
                                type="date"
                                v-model="form.sipi_date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            />
                            <div v-if="form.errors.sipi_date" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.sipi_date }}
                            </div>
                        </div>

                        <!-- Tanggal Berakhir SIPI -->
                        <div>
                            <label for="sipi_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tanggal Berakhir SIPI
                            </label>
                            <input
                                id="sipi_end_date"
                                type="date"
                                v-model="form.sipi_end_date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            />
                            <div v-if="form.errors.sipi_end_date" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.sipi_end_date }}
                            </div>
                        </div>
                    </div>

                    <!-- Foto Kapal -->
                    <div>
                        <label for="vessel_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Foto Kapal
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <!-- Photo Preview -->
                                <div v-if="photoPreview" class="mb-4">
                                    <img :src="photoPreview" alt="Preview" class="h-48 w-auto mx-auto object-cover rounded-lg border border-gray-200 dark:border-gray-600" />
                                </div>
                                
                                <!-- Upload Icon -->
                                <div v-else class="flex justify-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                
                                <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                    <label for="vessel_photo" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload file</span>
                                        <input
                                            id="vessel_photo"
                                            ref="photoInput"
                                            type="file"
                                            accept="image/*"
                                            class="sr-only"
                                            @change="handlePhotoChange"
                                        />
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    PNG, JPG, GIF hingga 10MB
                                </p>
                            </div>
                        </div>
                        <div v-if="form.errors.vessel_photo" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.vessel_photo }}
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div>
                        <label for="qr_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            QR Code
                        </label>
                        <input
                            id="qr_code"
                            type="text"
                            v-model="form.qr_code"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            placeholder="URL QR Code"
                        />
                        <div v-if="form.errors.qr_code" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.qr_code }}
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Catatan
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors resize-none"
                            placeholder="Catatan tambahan..."
                        ></textarea>
                        <div v-if="form.errors.notes" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.notes }}
                        </div>
                    </div>

                    <!-- Status Approval -->
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-800 dark:text-green-200">
                            <strong>Info:</strong> Kapal akan langsung aktif dengan status "Disetujui". Anda dapat menambahkan pengelola kapal melalui menu "Pengelolaan Kapal".
                        </p>
                    </div>

                    <!-- Success/Error Message -->
                    <div v-if="flash.success" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-800 dark:text-green-200">
                            {{ flash.success }}
                        </p>
                    </div>
                    <div v-if="flash.error" class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-800 dark:text-red-200">
                            {{ flash.error }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            href="/vessels"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>