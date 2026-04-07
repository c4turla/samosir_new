<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    sprDepartures: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const search = ref(props.filters.search || '')

watch(search, (value) => {
    router.get('/spr-departures', { search: value }, {
        preserveState: true,
        replace: true
    })
})

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200'
        case 'processed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200'
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
}

const formatTanggal = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    })
}

const formatWaktu = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB'
}
</script>

<template>
    <AppLayout>
        <Head title="Permohonan SPR Keberangkatan" />

        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Permohonan SPR Keberangkatan</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Daftar Rencana Keberangkatan Kapal yang diajukan oleh Pengelola melalui Mobile
                </p>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
                <div class="relative max-w-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama kapal atau nakhoda..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-xs"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapal / Selar</th>
                                <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nakhoda</th>
                                <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rencana Berangkat</th>
                                <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Muatan</th>
                                <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengaju</th>
                                <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Status</th>
                                <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <tr v-for="spr in sprDepartures.data" :key="spr.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ spr.vessel?.vessel_name }}</span>
                                        <span class="text-[10px] text-gray-500">{{ spr.vessel?.selar_mark }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ spr.nakhoda_name }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-gray-900 dark:text-white">{{ formatTanggal(spr.planned_departure_datetime) }}</span>
                                        <span class="text-[10px] text-gray-500">{{ formatWaktu(spr.planned_departure_datetime) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-600 dark:text-gray-400 truncate max-w-[150px]" :title="spr.muatan">
                                    {{ spr.muatan || '-' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-gray-900 dark:text-white">{{ spr.user?.name }}</span>
                                        <span class="text-[10px] text-gray-500">Pengelola</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span :class="['px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider', getStatusBadgeClass(spr.status)]">
                                        {{ spr.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <Link
                                        :href="`/spr-departures/${spr.id}`"
                                        class="inline-flex items-center space-x-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                                    >
                                        <i class="ri-eye-line"></i>
                                        <span>Detail</span>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="sprDepartures.data.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400 italic">
                                    Belum ada data permohonan SPR.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="sprDepartures.links.length > 3" class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                    <div class="text-[10px] text-gray-500">
                        Menampilkan {{ sprDepartures.from }} - {{ sprDepartures.to }} dari {{ sprDepartures.total }} data
                    </div>
                    <div class="flex space-x-1">
                        <template v-for="(link, k) in sprDepartures.links" :key="k">
                            <div
                                v-if="link.url === null"
                                class="px-2 py-1 text-[10px] border border-gray-200 dark:border-gray-600 text-gray-400 rounded"
                                v-html="link.label"
                            />
                            <Link
                                v-else
                                :href="link.url"
                                class="px-2 py-1 text-[10px] border border-gray-200 dark:border-gray-600 rounded transition-colors"
                                :class="{ 'bg-blue-600 text-white border-blue-600': link.active, 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active }"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
