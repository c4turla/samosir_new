<script setup>
const props = defineProps({
    user: {
        type: Object,
        default: () => ({})
    },
    isCollapsed: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['logout'])
</script>

<template>
    <div class="flex items-center justify-between">
        <div :class="['flex items-center', isCollapsed ? '' : 'space-x-3']">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white font-semibold">{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
            </div>
            <transition name="fade">
                <div v-show="!isCollapsed">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name || 'Admin' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ user?.role || 'Administrator' }}</p>
                </div>
            </transition>
        </div>
        <transition name="fade">
            <button
                v-show="!isCollapsed"
                @click="emit('logout')"
                class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors"
                title="Logout"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>