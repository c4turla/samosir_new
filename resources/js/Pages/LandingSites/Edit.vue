<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

const props = defineProps({
    landingSite: {
        type: Object,
        required: true
    }
})

const form = useForm({
    site_name: props.landingSite.site_name,
    address: props.landingSite.address || '',
    distance: props.landingSite.distance || '',
    latitude: props.landingSite.latitude || '',
    longitude: props.landingSite.longitude || '',
    site_type: props.landingSite.site_type || 'tangkahan',
    is_active: props.landingSite.is_active ?? true,
})

const submit = () => {
    form.put(`/landing-sites/${props.landingSite.id}`)
}
</script>

<template>
    <AppLayout>
        <Head title="Edit Dermaga - SAMOSIR" />

        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <Link
                    href="/landing-sites"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Daftar
                </Link>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit Dermaga</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Perbarui informasi dermaga di bawah
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Dermaga <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="site_name"
                            type="text"
                            v-model="form.site_name"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            placeholder="Contoh: Dermaga Utara"
                        />
                        <div v-if="form.errors.site_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.site_name }}
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alamat
                        </label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors resize-none"
                            placeholder="Contoh: Jl. Pelabuhan No. 1, Kabupaten Samosir"
                        ></textarea>
                        <div v-if="form.errors.address" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.address }}
                        </div>
                    </div>

                    <div>
                        <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Jarak
                        </label>
                        <input
                            id="distance"
                            type="text"
                            v-model="form.distance"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            placeholder="Contoh: 5 km"
                        />
                        <div v-if="form.errors.distance" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.distance }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Latitude
                            </label>
                            <input
                                id="latitude"
                                type="number"
                                v-model="form.latitude"
                                step="any"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: 2.5833"
                            />
                            <div v-if="form.errors.latitude" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.latitude }}
                            </div>
                        </div>

                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Longitude
                            </label>
                            <input
                                id="longitude"
                                type="number"
                                v-model="form.longitude"
                                step="any"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                                placeholder="Contoh: 98.8167"
                            />
                            <div v-if="form.errors.longitude" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.longitude }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="site_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Jenis Situs <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="site_type"
                            v-model="form.site_type"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                        >
                            <option value="tangkahan">Tangkahan</option>
                            <option value="dermaga">Dermaga</option>
                        </select>
                        <div v-if="form.errors.site_type" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.site_type }}
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="is_active"
                            type="checkbox"
                            v-model="form.is_active"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                        />
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Aktif
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <Link
                            href="/landing-sites"
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
                            <span v-else>Update</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>