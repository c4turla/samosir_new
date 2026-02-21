<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    species: {
        type: Object,
        required: true
    }
})

const search = ref(new URLSearchParams(window.location.search).get('search') || '')
const searchForm = useForm({ search: search.value })

watch(search, (value) => {
    searchForm.search = value
    router.get('/fish-species', { search: value }, {
        preserveState: true,
        replace: true
    })
})

const deleteSpecies = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jenis ikan ini?')) {
        router.delete(`/fish-species/${id}`)
    }
}
</script>

<template>
    <AppLayout>
    <Head title="Jenis Ikan - SAMOSIR" />

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Jenis Ikan</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Kelola data jenis ikan di sistem
                </p>
            </div>
            <Link
                href="/fish-species/create"
                class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-xs"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Jenis Ikan
            </Link>
        </div>

        <!-- Search Box -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3 mb-4">
            <div class="relative">
                <svg
                    class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari berdasarkan nama ikan, nama lokal, nama ilmiah, atau kategori..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors text-xs"
                />
            </div>
            <p v-if="species.total > 0" class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                Menampilkan {{ species.from }} - {{ species.to }} dari total {{ species.total }} data
            </p>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nama Ikan
                            </th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nama Lokal
                            </th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nama Ilmiah
                            </th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Kategori
                            </th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="item in species.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ item.species_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                {{ item.local_name || '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ item.scientific_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                {{ item.category || '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium',
                                        item.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    ]"
                                >
                                    {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/fish-species/${item.id}/edit`"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 0L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="deleteSpecies(item.id)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="species.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs">Tidak ada data jenis ikan yang ditemukan</p>
                                <Link
                                    href="/fish-species/create"
                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block text-xs"
                                >
                                    Tambah jenis ikan pertama
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="species.last_page > 1" class="bg-gray-50 dark:bg-gray-700 px-3 py-2 border-t border-gray-200 dark:border-gray-600 sm:px-4">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <div class="text-xs text-gray-700 dark:text-gray-300 mb-2 sm:mb-0">
                        Halaman {{ species.current_page }} dari {{ species.last_page }}
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-if="species.prev_page_url"
                            :href="species.prev_page_url"
                            class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            Sebelumnya
                        </Link>
                        <span
                            v-for="page in Math.min(species.last_page, 5)"
                            :key="page"
                            class="px-2 py-1 text-xs border rounded-md transition-colors"
                            :class="[
                                page === species.current_page
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                            ]"
                        >
                            <Link
                                v-if="Math.abs(page - species.current_page) <= 2"
                                :href="`${species.path}?page=${page}${search ? '&search=' + search : ''}`"
                                class="block"
                            >
                                {{ page }}
                            </Link>
                        </span>
                        <Link
                            v-if="species.next_page_url"
                            :href="species.next_page_url"
                            class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            Selanjutnya
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>
</template>