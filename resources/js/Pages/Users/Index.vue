<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  currentUser: {
    type: Object,
    required: true
  }
})

const deleteUser = (user) => {
  if (confirm(`Apakah Anda yakin ingin menghapus user ${user.name}?`)) {
    router.delete(route('users.destroy', user.id))
  }
}

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'admin':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
    case 'syahbandar':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
    case 'petugas':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
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
            Kelola user staff dan syahbandar
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

      <!-- Users Table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kontak</th>
                <th class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                      <span class="text-white font-semibold text-xs">{{ user.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="ml-3 min-w-0">
                      <div class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ user.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium', getRoleBadgeClass(user.role)]">
                    {{ user.role.toUpperCase() }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                  {{ user.phone || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                  <div class="flex justify-end space-x-2">
                    <Link
                      :href="route('users.edit', user.id)"
                      class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                      title="Edit User"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Link>
                    <button
                      v-if="user.id !== currentUser.id"
                      @click="deleteUser(user)"
                      class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
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
        <div v-if="users.length === 0" class="text-center py-12">
          <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <h3 class="mt-2 text-xs font-medium text-gray-900 dark:text-white">Belum ada user</h3>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mulai dengan menambahkan user baru.</p>
          <Link
            :href="route('users.create')"
            class="mt-4 inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            Tambah User
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>