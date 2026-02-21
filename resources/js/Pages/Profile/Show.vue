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

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
  address: props.user.address || ''
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const updateProfile = () => {
  profileForm.post(route('profile.update'), {
    onSuccess: () => {
      profileForm.reset()
      profileForm.name = props.user.name
      profileForm.email = props.user.email
      profileForm.phone = props.user.phone || ''
      profileForm.address = props.user.address || ''
    }
  })
}

const updatePassword = () => {
  passwordForm.post(route('profile.password'), {
    onSuccess: () => {
      passwordForm.reset()
    }
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Profile" />

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile</h1>
      <p class="text-gray-600 dark:text-gray-400">Kelola informasi profil dan keamanan akun Anda</p>
    </div>

    <div class="space-y-12">
      <!-- Profile Information Form -->
      <form @submit.prevent="updateProfile">
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-700 sm:rounded-xl">
          <div class="px-4 py-6 sm:px-8 sm:py-8 border-b border-gray-900/10 dark:border-gray-700">
            <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Informasi Profil</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Update informasi pribadi Anda</p>
          </div>

          <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 px-4 py-6 sm:px-8">
            <!-- Name -->
            <div class="sm:col-span-3">
              <label for="name" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
              <div class="mt-2">
                <input
                  id="name"
                  v-model="profileForm.name"
                  type="text"
                  placeholder="Masukkan nama lengkap"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.name }}
              </p>
            </div>

            <!-- Email -->
            <div class="sm:col-span-3">
              <label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Email</label>
              <div class="mt-2">
                <input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  placeholder="user@example.com"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.email }}
              </p>
            </div>

            <!-- Phone -->
            <div class="sm:col-span-3">
              <label for="phone" class="block text-sm/6 font-medium text-gray-900 dark:text-white">No. Telepon</label>
              <div class="mt-2">
                <input
                  id="phone"
                  v-model="profileForm.phone"
                  type="text"
                  placeholder="08123456789"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="profileForm.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.phone }}
              </p>
            </div>

            <!-- Address -->
            <div class="col-span-full">
              <label for="address" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Alamat</label>
              <div class="mt-2">
                <textarea
                  id="address"
                  v-model="profileForm.address"
                  rows="3"
                  placeholder="Masukkan alamat lengkap"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                ></textarea>
              </div>
              <p v-if="profileForm.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.address }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-4 py-4 sm:px-8">
            <button
              type="submit"
              :disabled="profileForm.processing"
              class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="profileForm.processing">Menyimpan...</span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </div>
        </div>
      </form>

      <!-- Change Password Form -->
      <form @submit.prevent="updatePassword">
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-700 sm:rounded-xl">
          <div class="px-4 py-6 sm:px-8 sm:py-8 border-b border-gray-900/10 dark:border-gray-700">
            <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Ganti Password</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Pastikan password Anda kuat dan aman</p>
          </div>

          <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 px-4 py-6 sm:px-8">
            <!-- Current Password -->
            <div class="sm:col-span-3">
              <label for="current_password" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Password Saat Ini</label>
              <div class="mt-2">
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  placeholder="Masukkan password saat ini"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ passwordForm.errors.current_password }}
              </p>
            </div>

            <!-- New Password -->
            <div class="sm:col-span-3">
              <label for="password" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Password Baru</label>
              <div class="mt-2">
                <input
                  id="password"
                  v-model="passwordForm.password"
                  type="password"
                  placeholder="Minimal 8 karakter"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ passwordForm.errors.password }}
              </p>
            </div>

            <!-- Password Confirmation -->
            <div class="col-span-full">
              <label for="password_confirmation" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Konfirmasi Password Baru</label>
              <div class="mt-2">
                <input
                  id="password_confirmation"
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  placeholder="Ulangi password baru"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ passwordForm.errors.password_confirmation }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-4 py-4 sm:px-8">
            <button
              type="submit"
              :disabled="passwordForm.processing"
              class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="passwordForm.processing">Mengubah...</span>
              <span v-else>Ganti Password</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>