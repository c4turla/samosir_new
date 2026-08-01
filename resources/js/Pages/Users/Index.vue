<script setup>
import { ref, watch, computed } from 'vue'
import { router, usePage, Head, Link } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { route } from 'ziggy-js'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const props = defineProps({
  users: {
    type: Object,
    required: true
  },
  currentUser: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({ search: '', role: '' })
  }
})

const search = ref(new URLSearchParams(window.location.search).get('search') || props.filters?.search || '')
const role = ref(new URLSearchParams(window.location.search).get('role') || props.filters?.role || '')

watch([search, role], () => {
  router.get(route('users.index'), {
    search: search.value,
    role: role.value
  }, {
    preserveState: true,
    replace: true
  })
})

const deleteUser = (user) => {
  if (confirm(`Apakah Anda yakin ingin menghapus user ${user.name}?`)) {
    router.delete(route('users.destroy', user.id))
  }
}

const getRoleBadgeClass = (userRole) => {
  switch (userRole) {
    case 'admin':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
    case 'syahbandar':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
    case 'petugas':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
    case 'kepala_pelabuhan':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
    case 'pengelola':
      return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300'
    case 'umum':
      return 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Manajemen User" />

    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen User</h1>
          <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
            Kelola user staff, syahbandar, pengelola, dan umum
          </p>
        </div>
        <Link
          :href="route('users.create')"
          class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-xs"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah User
        </Link>
      </div>

      <!-- Search & Filter Box -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <!-- Search Input -->
          <div class="relative md:col-span-2">
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
              placeholder="Cari nama, email, NIP, atau no HP user..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors text-xs"
            />
          </div>

          <!-- Role Filter Dropdown -->
          <div class="relative">
            <select
              v-model="role"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 transition-colors appearance-none cursor-pointer text-xs"
            >
              <option value="">Semua Role</option>
              <option value="admin">Admin</option>
              <option value="petugas">Petugas</option>
              <option value="syahbandar">Syahbandar</option>
              <option value="kepala_pelabuhan">Kepala Pelabuhan</option>
              <option value="pengelola">Pengelola Kapal</option>
              <option value="umum">Umum</option>
            </select>
            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <p v-if="users.data && users.data.length > 0 && users.total > 0" class="text-xs text-gray-600 dark:text-gray-400 mt-2">
          Menampilkan {{ users.from }} - {{ users.to }} dari total {{ users.total }} user
        </p>

        <!-- Flash Messages -->
        <div v-if="flash.success" class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
          <p class="text-xs text-green-800 dark:text-green-200">
            {{ flash.success }}
          </p>
        </div>
        <div v-if="flash.error" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
          <p class="text-xs text-red-800 dark:text-red-200">
            {{ flash.error }}
          </p>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIP</th>
                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kontak</th>
                <th class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="userItem in (users.data || [])" :key="userItem.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                      <span class="text-white font-semibold text-xs">{{ userItem.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="ml-3 min-w-0">
                      <div class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ userItem.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ userItem.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                  {{ userItem.nip || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium', getRoleBadgeClass(userItem.role)]">
                    {{ userItem.role.replace('_', ' ').toUpperCase() }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                  {{ userItem.phone || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                  <div class="flex justify-end space-x-2">
                    <Link
                      :href="route('users.edit', userItem.id)"
                      class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                      title="Edit User"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Link>
                    <button
                      v-if="userItem.id !== currentUser.id"
                      @click="deleteUser(userItem)"
                      class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer"
                      title="Hapus User"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="!users.data || users.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <h3 class="mt-2 text-xs font-medium text-gray-900 dark:text-white">Tidak ada user ditemukan</h3>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Coba ubah kata kunci pencarian atau filter role.</p>
          <Link
            :href="route('users.create')"
            class="mt-4 inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            Tambah User Baru
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="bg-gray-50 dark:bg-gray-700 px-3 py-2 border-t border-gray-200 dark:border-gray-600 sm:px-4">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="text-xs text-gray-700 dark:text-gray-300 mb-2 sm:mb-0">
              Halaman {{ users.current_page }} dari {{ users.last_page }}
            </div>
            <div class="flex flex-wrap items-center gap-1">
              <Link
                v-for="(link, index) in users.links"
                :key="index"
                :href="link.url || '#'"
                class="px-2.5 py-1 text-xs rounded-md border transition-all duration-200 font-medium"
                :class="[
                  link.active
                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                    : link.url
                      ? 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                      : 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed opacity-50 pointer-events-none'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>