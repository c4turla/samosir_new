<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

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

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Aplikasi</h1>
      <p class="text-gray-600 dark:text-gray-400">Konfigurasi pengaturan aplikasi</p>
    </div>

    <!-- Admin Warning -->
    <div v-if="user.role !== 'admin'" class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
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
      <div class="space-y-12 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-700 sm:rounded-xl">
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 px-4 py-6 sm:p-8">
          <!-- App Name -->
          <div class="sm:col-span-4">
            <label for="app_name" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Nama Aplikasi</label>
            <div class="mt-2">
              <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                <input
                  id="app_name"
                  v-model="settingsForm.app_name"
                  type="text"
                  :disabled="user.role !== 'admin'"
                  class="block flex-1 border-0 bg-transparent py-1.5 px-3 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm/6 disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
            </div>
            <p v-if="settingsForm.errors.app_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.app_name }}
            </p>
          </div>

          <!-- App Description -->
          <div class="col-span-full">
            <label for="app_description" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Deskripsi Aplikasi</label>
            <div class="mt-2">
              <textarea
                id="app_description"
                v-model="settingsForm.app_description"
                rows="3"
                :disabled="user.role !== 'admin'"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6 disabled:opacity-50 disabled:cursor-not-allowed"
              ></textarea>
            </div>
            <p v-if="settingsForm.errors.app_description" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.app_description }}
            </p>
          </div>

          <!-- Contact Section Divider -->
          <div class="col-span-full border-t border-gray-900/10 dark:border-gray-700 pt-8">
            <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Informasi Kontak</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Informasi kontak untuk aplikasi</p>
          </div>

          <!-- Contact Email -->
          <div class="sm:col-span-4">
            <label for="contact_email" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Email Kontak</label>
            <div class="mt-2">
              <input
                id="contact_email"
                v-model="settingsForm.contact_email"
                type="email"
                placeholder="contact@samosir.com"
                :disabled="user.role !== 'admin'"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6 disabled:opacity-50 disabled:cursor-not-allowed"
              />
            </div>
            <p v-if="settingsForm.errors.contact_email" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.contact_email }}
            </p>
          </div>

          <!-- Contact Phone -->
          <div class="sm:col-span-4">
            <label for="contact_phone" class="block text-sm/6 font-medium text-gray-900 dark:text-white">No. Telepon Kontak</label>
            <div class="mt-2">
              <input
                id="contact_phone"
                v-model="settingsForm.contact_phone"
                type="text"
                placeholder="+62 812-3456-7890"
                :disabled="user.role !== 'admin'"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6 disabled:opacity-50 disabled:cursor-not-allowed"
              />
            </div>
            <p v-if="settingsForm.errors.contact_phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ settingsForm.errors.contact_phone }}
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="user.role === 'admin'" class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-4 py-4 sm:px-8">
          <button
            type="submit"
            :disabled="settingsForm.processing"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="settingsForm.processing">Menyimpan...</span>
            <span v-else>Simpan Pengaturan</span>
          </button>
        </div>
      </div>
    </form>
  </AppLayout>
</template>