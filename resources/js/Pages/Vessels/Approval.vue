<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const props = defineProps({
    vessels: {
        type: Object,
        required: true
    },
    users: {
        type: Array,
        required: true
    }
})

const search = ref(new URLSearchParams(window.location.search).get('search') || '')
const status = ref(new URLSearchParams(window.location.search).get('status') || '')

watch([search, status], ([searchValue, statusValue]) => {
    router.get('/vessels/approval', { search: searchValue, status: statusValue }, {
        preserveState: true,
        replace: true
    })
})

const deleteVessel = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus kapal ini?')) {
        router.delete(`/vessels/${id}`)
    }
}

const approveVessel = (id) => {
    if (confirm('Apakah Anda yakin ingin menyetujui kapal ini?')) {
        router.put(`/vessels/${id}/approve`)
    }
}

const rejectVessel = (id) => {
    if (confirm('Apakah Anda yakin ingin menolak kapal ini?')) {
        router.put(`/vessels/${id}/reject`)
    }
}

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        default:
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    }
}

const getStatusLabel = (status) => {
    switch (status) {
        case 'approved':
            return 'Disetujui'
        case 'rejected':
            return 'Ditolak'
        default:
            return 'Menunggu'
    }
}

const assignManager = (vesselId) => {
    const form = useForm({
        user_id: '',
        is_primary: false,
        address: '',
        id_card: '',
        authorization_letter: ''
    })
    
    // Find vessel and show modal or redirect
    const vessel = props.vessels.data.find(v => v.id === vesselId)
    if (!vessel) return
    
    form.post(`/vessels/${vesselId}/assign-manager`, {
        onSuccess: () => {
            router.reload()
        }
    })
}

const removeManager = (vesselId, userId) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengelola ini?')) {
        router.delete(`/vessels/${vesselId}/managers/${userId}`, {
            onSuccess: () => {
                router.reload()
            }
        })
    }
}

const selectedVesselForManager = ref(null)
const managerForm = useForm({
    user_id: '',
    is_primary: false,
    address: '',
    id_card: '',
    authorization_letter: ''
})

const openManagerModal = (vessel) => {
    selectedVesselForManager.value = vessel
    managerForm.reset()
}

const closeManagerModal = () => {
    selectedVesselForManager.value = null
    managerForm.reset()
}

const submitManager = () => {
    if (!selectedVesselForManager.value) return
    
    managerForm.post(`/vessels/${selectedVesselForManager.value.id}/assign-manager`, {
        onSuccess: () => {
            closeManagerModal()
            router.reload()
        }
    })
}

const primaryManagerForm = useForm({
    is_primary: false,
    address: '',
    id_card: '',
    authorization_letter: ''
})

const selectedManager = ref(null)
const selectedVessel = ref(null)

const openEditManagerModal = (vessel, manager) => {
    selectedVessel.value = vessel
    selectedManager.value = manager
    primaryManagerForm.is_primary = manager.pivot.is_primary
    primaryManagerForm.address = manager.pivot.address || ''
    primaryManagerForm.id_card = manager.pivot.id_card || ''
    primaryManagerForm.authorization_letter = manager.pivot.authorization_letter || ''
}

const closeEditManagerModal = () => {
    selectedVessel.value = null
    selectedManager.value = null
    primaryManagerForm.reset()
}

const updateManager = () => {
    if (!selectedVessel.value || !selectedManager.value) return
    
    primaryManagerForm.put(`/vessels/${selectedVessel.value.id}/managers/${selectedManager.value.id}`, {
        onSuccess: () => {
            closeEditManagerModal()
            router.reload()
        }
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Approval Kapal - SAMOSIR" />

        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengelolaan Kapal</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Kelola pengelola kapal dan review pengajuan dari mobile
                </p>
            </div>

            <!-- Search & Filter Box -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1">
                        <svg
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
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
                            placeholder="Cari berdasarkan nama kapal, pemilik, nomor lisensi, atau jenis..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors"
                        />
                    </div>
                    <div class="relative min-w-[200px]">
                        <select
                            v-model="status"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <p v-if="vessels.total > 0" class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    Menampilkan {{ vessels.from }} - {{ vessels.to }} dari total {{ vessels.total }} data
                </p>
                
                <!-- Flash Messages -->
                <div v-if="flash.success" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <p class="text-sm text-green-800 dark:text-green-200">
                        {{ flash.success }}
                    </p>
                </div>
                <div v-if="flash.error" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        {{ flash.error }}
                    </p>
                </div>
            </div>

            <!-- Vessels List -->
            <div class="space-y-4">
                <div
                    v-for="vessel in vessels.data"
                    :key="vessel.id"
                    class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden"
                >
                    <div class="p-6">
                        <!-- Vessel Header -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                            <div class="flex items-center">
                                <svg class="w-10 h-10 text-blue-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ vessel.vessel_name }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Pemilik: {{ vessel.owner_name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadgeClass(vessel.approval_status)]">
                                    {{ getStatusLabel(vessel.approval_status) }}
                                </span>
                                <Link
                                    :href="`/vessels/${vessel.id}/edit`"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </Link>
                            </div>
                        </div>

                        <!-- Vessel Details -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Nomor Lisensi:</span>
                                <p class="font-medium text-gray-900 dark:text-white">{{ vessel.license_number || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Jenis Kapal:</span>
                                <p class="font-medium text-gray-900 dark:text-white">{{ vessel.vessel_type || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">GT:</span>
                                <p class="font-medium text-gray-900 dark:text-white">{{ vessel.gt || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">LOA:</span>
                                <p class="font-medium text-gray-900 dark:text-white">{{ vessel.loa || '-' }}</p>
                            </div>
                        </div>

                        <!-- Managers Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Pengelola Kapal</h4>
                                <button
                                    @click="openManagerModal(vessel)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Pengelola
                                </button>
                            </div>

                            <!-- Managers List -->
                            <div v-if="vessel.managers && vessel.managers.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div
                                    v-for="manager in vessel.managers"
                                    :key="manager.id"
                                    class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium mr-3">
                                                {{ manager.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ manager.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ manager.email }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                v-if="manager.pivot.is_primary"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                                            >
                                                Utama
                                            </span>
                                            <button
                                                @click="openEditManagerModal(vessel, manager)"
                                                class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="removeManager(vessel.id, manager.id)"
                                                class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="manager.pivot.id_card || manager.pivot.authorization_letter || manager.pivot.address" class="mt-3 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                        <p v-if="manager.pivot.id_card">No. KTP: {{ manager.pivot.id_card }}</p>
                                        <p v-if="manager.pivot.authorization_letter">Surat Kuasa: {{ manager.pivot.authorization_letter }}</p>
                                        <p v-if="manager.pivot.address">Alamat: {{ manager.pivot.address }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-4 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <p>Belum ada pengelola kapal</p>
                                <button
                                    @click="openManagerModal(vessel)"
                                    class="mt-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    Tambah pengelola pertama
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4 flex justify-end gap-2">
                            <button
                                @click="deleteVessel(vessel.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="vessels.data.length === 0" class="bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <p>Tidak ada data kapal yang ditemukan</p>
                    <Link
                        href="/vessels/create"
                        class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block"
                    >
                        Tambah kapal pertama
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="vessels.last_page > 1" class="mt-6 bg-white dark:bg-gray-800 shadow rounded-lg px-4 py-3">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300 mb-2 sm:mb-0">
                        Halaman {{ vessels.current_page }} dari {{ vessels.last_page }}
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-if="vessels.prev_page_url"
                            :href="vessels.prev_page_url"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            Sebelumnya
                        </Link>
                        <span
                            v-for="page in Math.min(vessels.last_page, 5)"
                            :key="page"
                            class="px-3 py-1 text-sm border rounded-md transition-colors"
                            :class="[
                                page === vessels.current_page
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                            ]"
                        >
                            <Link
                                v-if="Math.abs(page - vessels.current_page) <= 2"
                                :href="`${vessels.path}?page=${page}${search ? '&search=' + search : ''}${status ? '&status=' + status : ''}`"
                                class="block"
                            >
                                {{ page }}
                            </Link>
                        </span>
                        <Link
                            v-if="vessels.next_page_url"
                            :href="vessels.next_page_url"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            Selanjutnya
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Manager Modal -->
        <div
            v-if="selectedVesselForManager"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeManagerModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Tambah Pengelola - {{ selectedVesselForManager.vessel_name }}
                        </h3>
                        <button
                            @click="closeManagerModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <form @submit.prevent="submitManager" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pengelola <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="managerForm.user_id"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                        >
                            <option value="">Pilih pengelola...</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <div v-if="managerForm.errors.user_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ managerForm.errors.user_id }}
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="is_primary"
                            type="checkbox"
                            v-model="managerForm.is_primary"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_primary" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Jadikan pengelola utama
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alamat
                        </label>
                        <input
                            v-model="managerForm.address"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                            placeholder="Alamat pengelola..."
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nomor KTP
                        </label>
                        <input
                            v-model="managerForm.id_card"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                            placeholder="Nomor KTP..."
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Surat Kuasa
                        </label>
                        <input
                            v-model="managerForm.authorization_letter"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                            placeholder="Nomor/nama surat kuasa..."
                        />
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button
                            type="button"
                            @click="closeManagerModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="managerForm.processing"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="managerForm.processing">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Manager Modal -->
        <div
            v-if="selectedManager && selectedVessel"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeEditManagerModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Pengelola - {{ selectedManager.name }}
                        </h3>
                        <button
                            @click="closeEditManagerModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <form @submit.prevent="updateManager" class="p-6 space-y-4">
                    <div class="flex items-center">
                        <input
                            id="edit_is_primary"
                            type="checkbox"
                            v-model="primaryManagerForm.is_primary"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="edit_is_primary" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Jadikan pengelola utama
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alamat
                        </label>
                        <input
                            v-model="primaryManagerForm.address"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                            placeholder="Alamat pengelola..."
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nomor KTP
                        </label>
                        <input
                            v-model="primaryManagerForm.id_card"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                            placeholder="Nomor KTP..."
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Surat Kuasa
                        </label>
                        <input
                            v-model="primaryManagerForm.authorization_letter"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                            placeholder="Nomor/nama surat kuasa..."
                        />
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button
                            type="button"
                            @click="closeEditManagerModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="primaryManagerForm.processing"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="primaryManagerForm.processing">Menyimpan...</span>
                            <span v-else>Update</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>