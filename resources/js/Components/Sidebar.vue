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

// Hover state for collapsed sidebar flyouts
const hoveredMenu = ref(null)
const hoverTop = ref(0)
let hoverTimeout = null

const onMouseEnter = (item, index, event) => {
    if (!props.isCollapsed) return
    clearTimeout(hoverTimeout)
    
    // Get the Top position of the hovered element.
    const rect = event.currentTarget.getBoundingClientRect()
    hoverTop.value = rect.top
    hoveredMenu.value = { item, index }
}

const onMouseLeave = () => {
    if (!props.isCollapsed) return
    hoverTimeout = setTimeout(() => {
        hoveredMenu.value = null
    }, 150)
}

const onDropdownMouseEnter = () => {
    clearTimeout(hoverTimeout)
}

const onDropdownMouseLeave = () => {
    if (!props.isCollapsed) return
    hoverTimeout = setTimeout(() => {
        hoveredMenu.value = null
    }, 150)
}

// Watch for currentPath changes to auto-open dropdowns
watch(() => props.currentPath, () => {
    initializeOpenStates()
    hoveredMenu.value = null // Close flyout on navigation
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
        <div class="flex items-center justify-between h-14 px-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
<transition name="fade">
    <div v-show="!isCollapsed" class="transition-all duration-300">
        <h1 class="text-sm font-bold text-gray-900 dark:text-white">
            SAMOSIR <span class="text-[10px] font-medium">V.3.0</span>
        </h1>
        <p class="text-[10px] text-gray-500 dark:text-gray-400">PPN Sibolga</p>
    </div>
</transition>
            </div>
            <button
                @click="emit('close')"
                class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="p-1.5 space-y-0.5 overflow-y-auto h-[calc(100vh-7rem)]">
            <template v-for="(item, index) in menuItems" :key="item.title">
                <!-- Single Menu Item -->
                <Link
                    v-if="!item.items"
                    :href="item.to"
                    @mouseenter="(e) => onMouseEnter(item, index, e)"
                    @mouseleave="onMouseLeave"
                    :class="[
                        'flex items-center rounded-lg transition-all duration-200',
                        isCollapsed ? 'justify-center px-2.5 py-2' : 'space-x-2.5 px-3 py-2',
                        currentPath === item.to
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]"
                >
                    <i :class="[item.icon, 'text-lg flex-shrink-0']"></i>
                    <transition name="fade">
                        <span v-show="!isCollapsed" class="text-xs font-medium">{{ item.title }}</span>
                    </transition>
                </Link>

                <!-- Menu with Subitems -->
                <div v-else class="relative"
                    @mouseenter="(e) => onMouseEnter(item, index, e)"
                    @mouseleave="onMouseLeave"
                >
                    <button
                        @click="isCollapsed ? emit('toggle-collapse') : toggleDropdown(index)"
                        :class="[
                            'w-full flex items-center rounded-lg transition-all duration-200',
                            isCollapsed ? 'justify-center px-2.5 py-2' : 'justify-between px-3 py-2',
                            item.items.some(sub => currentPath === sub.to)
                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                        ]"
                    >
                        <div :class="['flex items-center', isCollapsed ? '' : 'space-x-2.5']">
                            <i :class="[item.icon, 'text-lg flex-shrink-0']"></i>
                            <transition name="fade">
                                <span v-show="!isCollapsed" class="text-xs font-medium">{{ item.title }}</span>
                            </transition>
                        </div>
                        <transition name="fade">
                            <svg
                                v-show="!isCollapsed"
                                :class="['w-4 h-4 transition-transform duration-200 flex-shrink-0', isOpen(index) ? 'rotate-180' : '']"
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
                            class="mt-1 ml-3 space-y-0.5 overflow-hidden"
                        >
                            <Link
                                v-for="subItem in item.items"
                                :key="subItem.title"
                                :href="subItem.to"
                                :class="[
                                    'flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-all duration-200',
                                    currentPath === subItem.to
                                        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                                ]"
                            >
                                <i :class="[subItem.icon, 'text-base']"></i>
                                <span class="text-xs">{{ subItem.title }}</span>
                            </Link>
                        </div>
                    </transition>
                </div>
            </template>
        </nav>

        <!-- User Section Slot -->
        <div class="absolute bottom-0 left-0 right-0 p-2 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <slot name="user-profile"></slot>
        </div>
    </aside>

    <!-- Floating Menu for collapsed sidebar -->
    <Teleport to="body">
        <transition name="fade">
            <div 
                v-if="isCollapsed && hoveredMenu"
                class="fixed z-[100] left-[72px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-1.5 min-w-[200px]"
                :style="{ top: hoverTop + 'px' }"
                @mouseenter="onDropdownMouseEnter"
                @mouseleave="onDropdownMouseLeave"
            >
                <!-- Dropdown for Items WITH Subitems -->
                <template v-if="hoveredMenu.item.items">
                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 font-medium text-sm text-gray-900 dark:text-white">
                        {{ hoveredMenu.item.title }}
                    </div>
                    <div class="py-1 max-h-[50vh] overflow-y-auto">
                        <Link
                            v-for="subItem in hoveredMenu.item.items"
                            :key="subItem.title"
                            :href="subItem.to"
                            :class="[
                                'flex items-center space-x-2.5 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors',
                                currentPath === subItem.to
                                    ? 'text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/10'
                                    : 'text-gray-600 dark:text-gray-400'
                            ]"
                        >
                            <i :class="[subItem.icon, 'text-sm']"></i>
                            <span class="text-xs">{{ subItem.title }}</span>
                        </Link>
                    </div>
                </template>

                <!-- Tooltip for Single Items -->
                <template v-else>
                    <div class="px-4 py-2 font-medium text-sm text-gray-900 dark:text-white">
                        {{ hoveredMenu.item.title }}
                    </div>
                </template>
            </div>
        </transition>
    </Teleport>
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