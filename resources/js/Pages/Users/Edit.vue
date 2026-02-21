<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role: props.user.role,
  phone: props.user.phone || '',
  address: props.user.address || '',
  password: ''
})

const updateUser = () => {
  form.put(route('users.update', props.user.id))
}
</script>

<template>
  <AppLayout>
    <Head title="Edit User" />

    <div class="mb-6">
      <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
        <Link :href="route('users.index')" class="hover:text-gray-700 dark:hover:text-gray-200">
          Users
        </Link>
        <span>/</span>
        <span class="text-gray-900 dark:text-white">Edit User</span>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">Edit User</h1>
      <p class="text-gray-600 dark:text-gray-400">Edit informasi user {{ user.name }}</p>
    </div>

    <form @submit.prevent="updateUser">
      <div class="space-y-12 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-700 sm:rounded-xl">
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 px-4 py-6 sm:p-8">
          <!-- Name -->
          <div class="sm:col-span-3">
            <label for="name" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
            <div class="mt-2">
              <input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="Masukkan nama lengkap"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
              />
            </div>
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div class="sm:col-span-3">
            <label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Email</label>
            <div class="mt-2">
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="user@example.com"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
              />
            </div>
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Role -->
          <div class="sm:col-span-3">
            <label for="role" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Role</label>
            <div class="mt-2">
              <select
                id="role"
                v-model="form.role"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
              >
                <option value="petugas">Petugas</option>
                <option value="syahbandar">Syahbandar</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <p v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.role }}
            </p>
          </div>

          <!-- Phone -->
          <div class="sm:col-span-3">
            <label for="phone" class="block text-sm/6 font-medium text-gray-900 dark:text-white">No. Telepon</label>
            <div class="mt-2">
              <input
                id="phone"
                v-model="form.phone"
                type="text"
                placeholder="08123456789"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
              />
            </div>
            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.phone }}
            </p>
          </div>

          <!-- Address -->
          <div class="col-span-full">
            <label for="address" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Alamat</label>
            <div class="mt-2">
              <textarea
                id="address"
                v-model="form.address"
                rows="3"
                placeholder="Masukkan alamat lengkap"
                class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
              ></textarea>
            </div>
            <p v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.address }}
            </p>
          </div>

          <!-- Change Password Section -->
          <div class="col-span-full">
            <div class="border-t border-gray-900/10 dark:border-gray-700 pt-8">
              <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Ganti Password</h2>
              <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                Kosongkan jika tidak ingin mengubah password user ini.
              </p>

              <div class="mt-2">
                <label for="password" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Password Baru</label>
                <div class="mt-2">
                  <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                    class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                  />
                </div>
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.password }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-4 py-4 sm:px-8">
          <Link
            :href="route('users.index')"
            class="text-sm/6 font-semibold text-gray-900 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Update User</span>
          </button>
        </div>
      </div>
    </form>
  </AppLayout>
</template>