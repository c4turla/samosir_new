<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const toasts = ref([])
let toastIdCount = 0

// Remove a toast by id
const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
}

// Add a toast
const addToast = (message, type = 'success') => {
    const id = toastIdCount++
    toasts.value.push({
        id,
        message,
        type,
    })
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        removeToast(id)
    }, 4000)
}

// Watch for flash messages from Inertia
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        addToast(flash.success, 'success')
        // Clean up the prop so it doesn't fire again unnecessarily
        page.props.flash.success = null
    }
    if (flash?.error) {
        addToast(flash.error, 'error')
        page.props.flash.error = null
    }
}, { deep: true, immediate: true })
</script>

<template>
    <teleport to="body">
        <div class="fixed top-4 right-4 z-50 flex flex-col items-end space-y-2 pointer-events-none">
            <TransitionGroup 
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
                move-class="transition-transform duration-300"
            >
                <div 
                    v-for="toast in toasts" 
                    :key="toast.id"
                    class="w-80 sm:w-96 max-w-md bg-white dark:bg-gray-800 shadow-xl rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden flex relative"
                >
                    <div class="p-4 flex items-start w-full">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg v-if="toast.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <!-- Error Icon -->
                            <svg v-else-if="toast.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1 pt-0.5">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ toast.type === 'success' ? 'Berhasil!' : 'Terjadi Kesalahan' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ toast.message }}
                            </p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button 
                                @click="removeToast(toast.id)"
                                type="button" 
                                class="inline-flex rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Animated Progress Bar -->
                    <div 
                        :class="['absolute bottom-0 left-0 h-1 bg-gradient-to-r animate-shrink', toast.type === 'success' ? 'from-green-400 to-green-500' : 'from-red-400 to-red-500']"
                    ></div>
                </div>
            </TransitionGroup>
        </div>
    </teleport>
</template>

<style scoped>
.animate-shrink {
    width: 100%;
    animation: shrink 4s linear forwards;
}
@keyframes shrink {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
</style>
