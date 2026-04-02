<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const settingsForm = useForm({
  app_name: 'SAMOSIR',
  app_description: 'Sistem Pelabuhan Perikanan',
  contact_email: '',
  contact_phone: ''
})

const updateSettings = () => {
  settingsForm.post(route('settings.update'))
}
</script>

<template>
  <AppLayout>
    <Head title="Settings" />

    <div class="max-w-4xl mx-auto mb-4">
      <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Aplikasi</h1>
      <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Konfigurasi pengaturan aplikasi</p>
    </div>

    <!-- Admin Warning -->
    <div v-if="user.role !== 'admin'" class="max-w-4xl mx-auto mb-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
      <div class="flex">
        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Akses Terbatas</h3>
          <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
            Hanya admin yang dapat mengubah pengaturan aplikasi.
          </div>
        </div>
      </div>
    </div>

    <form @submit.prevent="updateSettings">
      <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="grid grid-cols-1 gap-4">
          <!-- App Name -->
          <div>
            <label for="app_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
              Nama Aplikasi <span class="text-red-500">*</span>
            </label>
            <input
              id="app_name"
              v-model="settingsForm.app_name"
              type="text"
              :disabled="user.role !== 'admin'"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            />
            <p v-if="settingsForm.errors.app_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.app_name }}
            </p>
          </div>

          <!-- App Description -->
          <div>
            <label for="app_description" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
              Deskripsi Aplikasi
            </label>
            <textarea
              id="app_description"
              v-model="settingsForm.app_description"
              rows="3"
              :disabled="user.role !== 'admin'"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 resize-none disabled:opacity-50 disabled:cursor-not-allowed"
            ></textarea>
            <p v-if="settingsForm.errors.app_description" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.app_description }}
            </p>
          </div>

          <!-- Contact Section Divider -->
          <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2">
              Informasi Kontak
            </h2>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
              Informasi kontak untuk aplikasi
            </p>
          </div>

          <!-- Contact Email -->
          <div>
            <label for="contact_email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
              Email Kontak
            </label>
            <input
              id="contact_email"
              v-model="settingsForm.contact_email"
              type="email"
              placeholder="contact@samosir.com"
              :disabled="user.role !== 'admin'"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            />
            <p v-if="settingsForm.errors.contact_email" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.contact_email }}
            </p>
          </div>

          <!-- Contact Phone -->
          <div>
            <label for="contact_phone" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
              No. Telepon Kontak
            </label>
            <input
              id="contact_phone"
              v-model="settingsForm.contact_phone"
              type="text"
              placeholder="+62 812-3456-7890"
              :disabled="user.role !== 'admin'"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            />
            <p v-if="settingsForm.errors.contact_phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.contact_phone }}
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="user.role === 'admin'" class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
          <button
            type="submit"
            :disabled="settingsForm.processing"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 text-sm"
          >
            <span v-if="settingsForm.processing">Menyimpan...</span>
            <span v-else>Simpan Pengaturan</span>
          </button>
        </div>
      </div>
    </form>
  </AppLayout>
</template>