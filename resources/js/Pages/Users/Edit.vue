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

    <div class="max-w-4xl mx-auto">
      <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit User</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
          Edit informasi user {{ user.name }}
        </p>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form @submit.prevent="updateUser" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
              <label for="name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="Masukkan nama lengkap"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.name }}
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email <span class="text-red-500">*</span>
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="user@example.com"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
              />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Role -->
            <div>
              <label for="role" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                Role <span class="text-red-500">*</span>
              </label>
              <select
                id="role"
                v-model="form.role"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
              >
                <option value="petugas">Petugas</option>
                <option value="syahbandar">Syahbandar</option>
                <option value="admin">Admin</option>
              </select>
              <div v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.role }}
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label for="phone" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                No. Telepon
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="text"
                placeholder="08123456789"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
              />
              <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.phone }}
              </div>
            </div>

            <!-- Address -->
            <div class="col-span-full">
              <label for="address" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                Alamat
              </label>
              <textarea
                id="address"
                v-model="form.address"
                rows="3"
                placeholder="Masukkan alamat lengkap"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 resize-none"
              ></textarea>
              <div v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.address }}
              </div>
            </div>

            <!-- Change Password Section -->
            <div class="col-span-full">
              <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 pb-2">
                  Ganti Password
                </h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                  Kosongkan jika tidak ingin mengubah password user ini.
                </p>

                <div>
                  <label for="password" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Password Baru
                  </label>
                  <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700"
                  />
                  <div v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.password }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <Link
              :href="route('users.index')"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 text-sm"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 text-sm"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Update User</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>