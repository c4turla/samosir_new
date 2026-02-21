<script setup>
import { ref, reactive, watch, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    menuItems: {
        type: Array,
        required: true
    },
    isOpen: {
        type: Boolean,
        default: true
    },
    isCollapsed: {
        type: Boolean,
        default: false
    },
    currentPath: {
        type: String,
        default: ''
    },
    user: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close', 'toggle-collapse'])

// Store open states for dropdowns
const openStates = reactive({})

// Initialize open states based on current path
const initializeOpenStates = () => {
    props.menuItems.forEach((item, index) => {
        if (item.items) {
            const hasActiveSubitem = item.items.some(sub => props.currentPath === sub.to)
            openStates[index] = hasActiveSubitem
        }
    })
}

// Initialize on mount
initializeOpenStates()

// Watch for currentPath changes to auto-open dropdowns
watch(() => props.currentPath, () => {
    initializeOpenStates()
})

// Check if a menu item is open
const isOpen = (index) => {
    return openStates[index] || false
}

// Toggle dropdown
const toggleDropdown = (index) => {
    openStates[index] = !openStates[index]
}
</script>

<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-30 bg-white dark:bg-gray-800 shadow-lg transition-all duration-300',
            isOpen ? 'translate-x-0' : '-translate-x-full',
            isCollapsed ? 'w-16' : 'w-64'
        ]"
        @mouseleave="isCollapsed ? null : null"
    >
        <!-- Logo Section -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-lg">S</span>
                </div>
                <transition name="fade">
                    <div v-show="!isCollapsed" class="transition-all duration-300">
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white">SAMOSIR</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pelabuhan Perikanan</p>
                    </div>
                </transition>
            </div>
            <button
                @click="emit('close')"
                class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="p-2 space-y-1 overflow-y-auto h-[calc(100vh-8rem)]">
            <template v-for="(item, index) in menuItems" :key="item.title">
                <!-- Single Menu Item -->
                <Link
                    v-if="!item.items"
                    :href="item.to"
                    :title="isCollapsed ? item.title : ''"
                    :class="[
                        'flex items-center rounded-lg transition-all duration-200',
                        isCollapsed ? 'justify-center px-3 py-3' : 'space-x-3 px-4 py-3',
                        currentPath === item.to
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]"
                >
                    <i :class="[item.icon, 'text-xl flex-shrink-0']"></i>
                    <transition name="fade">
                        <span v-show="!isCollapsed" class="font-medium">{{ item.title }}</span>
                    </transition>
                </Link>

                <!-- Menu with Subitems -->
                <div v-else class="relative">
                    <button
                        @click="isCollapsed ? emit('toggle-collapse') : toggleDropdown(index)"
                        :title="isCollapsed ? item.title : ''"
                        :class="[
                            'w-full flex items-center rounded-lg transition-all duration-200',
                            isCollapsed ? 'justify-center px-3 py-3' : 'justify-between px-4 py-3',
                            item.items.some(sub => currentPath === sub.to)
                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                        ]"
                    >
                        <div :class="['flex items-center', isCollapsed ? '' : 'space-x-3']">
                            <i :class="[item.icon, 'text-xl flex-shrink-0']"></i>
                            <transition name="fade">
                                <span v-show="!isCollapsed" class="font-medium">{{ item.title }}</span>
                            </transition>
                        </div>
                        <transition name="fade">
                            <svg
                                v-show="!isCollapsed"
                                :class="['w-5 h-5 transition-transform duration-200 flex-shrink-0', isOpen(index) ? 'rotate-180' : '']"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </transition>
                    </button>

                    <transition name="slide">
                        <div
                            v-show="isOpen(index) && !isCollapsed"
                            class="mt-1 ml-4 space-y-1 overflow-hidden"
                        >
                            <Link
                                v-for="subItem in item.items"
                                :key="subItem.title"
                                :href="subItem.to"
                                :class="[
                                    'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200',
                                    currentPath === subItem.to
                                        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                                ]"
                            >
                                <i :class="[subItem.icon, 'text-lg']"></i>
                                <span class="text-sm">{{ subItem.title }}</span>
                            </Link>
                        </div>
                    </transition>
                </div>
            </template>
        </nav>

        <!-- User Section Slot -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <slot name="user-profile"></slot>
        </div>
    </aside>
</template>

<style scoped>
/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Slide Transition */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
    max-height: 1000px;
    overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>