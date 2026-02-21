<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

const form = useForm({
    species_name: '',
    local_name: '',
    scientific_name: '',
    category: '',
    is_active: true,
})

const submit = () => {
    form.post('/fish-species')
}
</script>

<template>
    <AppLayout>
        <Head title="Tambah Jenis Ikan - SAMOSIR" />

        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <Link
                    href="/fish-species"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Daftar
                </Link>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Tambah Jenis Ikan</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Isi formulir di bawah untuk menambahkan jenis ikan baru
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="species_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Ikan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="species_name"
                            type="text"
                            v-model="form.species_name"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            placeholder="Contoh: Tuna"
                        />
                        <div v-if="form.errors.species_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.species_name }}
                        </div>
                    </div>

                    <div>
                        <label for="local_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Lokal
                        </label>
                        <input
                            id="local_name"
                            type="text"
                            v-model="form.local_name"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            placeholder="Contoh: Ikan Tongkol"
                        />
                        <div v-if="form.errors.local_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.local_name }}
                        </div>
                    </div>

                    <div>
                        <label for="scientific_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Ilmiah
                        </label>
                        <input
                            id="scientific_name"
                            type="text"
                            v-model="form.scientific_name"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            placeholder="Contoh: Thunnus albacares"
                        />
                        <div v-if="form.errors.scientific_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.scientific_name }}
                        </div>
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kategori
                        </label>
                        <input
                            id="category"
                            type="text"
                            v-model="form.category"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                            placeholder="Contoh: Ikan Laut"
                        />
                        <div v-if="form.errors.category" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.category }}
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
                            href="/fish-species"
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