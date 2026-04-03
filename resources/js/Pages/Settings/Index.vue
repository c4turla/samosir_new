<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps({
    settings: { type: Object, required: true },
    groupLabels: { type: Object, required: true },
    groupIcons: { type: Object, required: true },
    user: { type: Object, required: true }
})

const isAdmin = computed(() => props.user.role === 'admin')

// Active tab
const activeTab = ref(Object.keys(props.settings)[0] || 'app')

// Build form data from all settings
const buildFormData = () => {
    const data = {}
    Object.keys(props.settings).forEach(group => {
        props.settings[group].forEach(setting => {
            data[setting.key] = setting.value ?? ''
        })
    })
    return data
}

const form = useForm({
    settings: buildFormData()
})

// Track if form has changes
const hasChanges = ref(false)
const originalData = JSON.stringify(buildFormData())

watch(() => form.settings, (newVal) => {
    hasChanges.value = JSON.stringify(newVal) !== originalData
}, { deep: true })

const submit = () => {
    form.post('/settings', {
        preserveScroll: true,
        onSuccess: () => {
            hasChanges.value = false
        }
    })
}

// Flash message
const flash = computed(() => usePage().props.flash)

// Group descriptions
const groupDescriptions = {
    app: 'Konfigurasi informasi dasar aplikasi seperti nama dan deskripsi',
    port: 'Data pelabuhan perikanan yang dikelola',
    officials: 'Data pejabat pelabuhan untuk keperluan dokumen resmi',
    contact: 'Informasi kontak pelabuhan yang ditampilkan',
    document: 'Pengaturan kop surat dan footer untuk dokumen resmi',
    operational: 'Konfigurasi operasional harian pelabuhan',
}
</script>

<template>
    <AppLayout>
        <Head title="Pengaturan Aplikasi - SAMOSIR" />

        <div class="max-w-6xl mx-auto">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Aplikasi</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Konfigurasi seluruh pengaturan sistem SAMOSIR
                </p>
            </div>

            <!-- Admin Warning -->
            <div v-if="!isAdmin" class="mb-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-yellow-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Akses Terbatas</p>
                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-0.5">Hanya admin yang dapat mengubah pengaturan.</p>
                    </div>
                </div>
            </div>



            <div class="flex gap-6">
                <!-- Sidebar Tabs -->
                <div class="w-56 flex-shrink-0">
                    <nav class="bg-white dark:bg-gray-800 shadow rounded-lg p-2 space-y-0.5 sticky top-4">
                        <button
                            v-for="(label, group) in groupLabels"
                            :key="group"
                            @click="activeTab = group"
                            :class="[
                                'w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left transition-all duration-200 text-xs font-medium',
                                activeTab === group
                                    ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 shadow-sm'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                            ]"
                        >
                            <i :class="[groupIcons[group], 'text-base']"></i>
                            <span>{{ label }}</span>
                        </button>
                    </nav>
                </div>

                <!-- Settings Content -->
                <div class="flex-1">
                    <form @submit.prevent="submit">
                        <template v-for="(items, group) in settings" :key="group">
                            <div v-show="activeTab === group" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                                <!-- Group Header -->
                                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                            <i :class="[groupIcons[group], 'text-blue-600 dark:text-blue-400 text-lg']"></i>
                                        </div>
                                        <div>
                                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ groupLabels[group] }}</h2>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ groupDescriptions[group] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Settings Fields -->
                                <div class="p-6 space-y-5">
                                    <div v-for="setting in items" :key="setting.key">
                                        <label :for="setting.key" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                            {{ setting.label }}
                                        </label>

                                        <!-- Text Input -->
                                        <input
                                            v-if="setting.type === 'text' || setting.type === 'email'"
                                            :id="setting.key"
                                            v-model="form.settings[setting.key]"
                                            :type="setting.type === 'email' ? 'email' : 'text'"
                                            :disabled="!isAdmin"
                                            :placeholder="setting.label"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        />

                                        <!-- Number Input -->
                                        <input
                                            v-else-if="setting.type === 'number'"
                                            :id="setting.key"
                                            v-model="form.settings[setting.key]"
                                            type="number"
                                            :disabled="!isAdmin"
                                            :placeholder="setting.label"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        />

                                        <!-- Textarea -->
                                        <textarea
                                            v-else-if="setting.type === 'textarea'"
                                            :id="setting.key"
                                            v-model="form.settings[setting.key]"
                                            rows="3"
                                            :disabled="!isAdmin"
                                            :placeholder="setting.label"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        ></textarea>

                                        <!-- Select -->
                                        <select
                                            v-else-if="setting.type === 'select'"
                                            :id="setting.key"
                                            v-model="form.settings[setting.key]"
                                            :disabled="!isAdmin"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        >
                                            <option v-for="(label, val) in setting.options" :key="val" :value="val">
                                                {{ label }}
                                            </option>
                                        </select>

                                        <!-- Boolean Toggle -->
                                        <div v-else-if="setting.type === 'boolean'" class="flex items-center">
                                            <button
                                                type="button"
                                                :disabled="!isAdmin"
                                                @click="isAdmin && (form.settings[setting.key] = form.settings[setting.key] === '1' ? '0' : '1')"
                                                :class="[
                                                    'relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
                                                    form.settings[setting.key] === '1' ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600',
                                                    !isAdmin ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                                ]"
                                            >
                                                <span
                                                    :class="[
                                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                                        form.settings[setting.key] === '1' ? 'translate-x-5' : 'translate-x-0'
                                                    ]"
                                                ></span>
                                            </button>
                                            <span class="ml-3 text-xs text-gray-600 dark:text-gray-400">
                                                {{ form.settings[setting.key] === '1' ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>

                                        <!-- Description -->
                                        <p v-if="setting.description" class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">
                                            {{ setting.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Action Bar -->
                        <div v-if="isAdmin" class="mt-4 bg-white dark:bg-gray-800 shadow rounded-lg px-6 py-3 flex items-center justify-between sticky bottom-4">
                            <div class="flex items-center gap-2">
                                <transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0"
                                    enter-to-class="opacity-100"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100"
                                    leave-to-class="opacity-0"
                                >
                                    <span v-if="hasChanges" class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Ada perubahan yang belum disimpan
                                    </span>
                                </transition>
                            </div>
                            <button
                                type="submit"
                                :disabled="form.processing || !hasChanges"
                                :class="[
                                    'px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2',
                                    hasChanges
                                        ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                                ]"
                            >
                                <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>