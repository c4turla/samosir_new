<script setup>
import { ref, onUnmounted, watch } from 'vue'
import { route } from 'ziggy-js'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    user: {
        type: Object,
        default: () => ({})
    },
    darkMode: {
        type: Boolean,
        default: false
    },
    isSidebarOpen: {
        type: Boolean,
        default: true
    },
    isSidebarCollapsed: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['toggle-dark-mode', 'toggle-sidebar-collapse', 'logout'])

const showProfileDropdown = ref(false)
const dropdownRef = ref(null)

// Search State
const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
const showSearchDropdown = ref(false)
const searchDropdownRef = ref(null)
let debounceTimeout = null

const toggleDropdown = (event) => {
    event.stopPropagation()
    showProfileDropdown.value = !showProfileDropdown.value
}

const closeDropdown = () => {
    showProfileDropdown.value = false
}

const closeSearchDropdown = () => {
    showSearchDropdown.value = false
}

const handleLogout = () => {
    closeDropdown()
    emit('logout')
}

const handleClickOutside = (event) => {
    let clickedOutside = false
    
    if (showProfileDropdown.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown()
        clickedOutside = true
    }
    
    if (showSearchDropdown.value && searchDropdownRef.value && !searchDropdownRef.value.contains(event.target)) {
        closeSearchDropdown()
        clickedOutside = true
    }
    
    return clickedOutside
}

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

document.addEventListener('click', handleClickOutside)

watch(searchQuery, (newQuery) => {
    if (!newQuery.trim()) {
        searchResults.value = []
        showSearchDropdown.value = false
        return
    }

    isSearching.value = true
    showSearchDropdown.value = true

    clearTimeout(debounceTimeout)
    debounceTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/api/search?q=${encodeURIComponent(newQuery)}`)
            if (response.ok) {
                searchResults.value = await response.json()
            }
        } catch (e) {
            console.error('Search error', e)
        } finally {
            isSearching.value = false
        }
    }, 300)
})

// Navigation helper will close the dropdown
const handleSearchNavigation = (item) => {
    closeSearchDropdown()
    searchQuery.value = ''
}
</script>

<template>
    <header class="sticky top-0 z-10 bg-white dark:bg-gray-800 shadow-sm">
        <div class="flex items-center justify-between px-3 sm:px-4 lg:px-6 h-14">
            <!-- Left Section -->
            <div class="flex items-center space-x-3">
                <!-- Collapse Sidebar Button -->
                <button
                    v-show="isSidebarOpen"
                    @click="emit('toggle-sidebar-collapse')"
                    class="hidden lg:block text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                    :title="isSidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                >
                    <svg v-if="isSidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M20 12H4" />
                    </svg>
                </button>
                <!-- Search -->
                <div class="hidden md:flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-1.5 relative" ref="searchDropdownRef">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="searchQuery"
                        @focus="searchQuery.trim().length > 0 && (showSearchDropdown = true)"
                        type="text"
                        placeholder="Cari kapal, jenis ikan..."
                        class="bg-transparent border-none outline-none ml-2 text-xs text-gray-700 dark:text-gray-200 placeholder-gray-400 w-52 sm:w-64 focus:ring-0 p-0"
                    />
                    
                    <!-- Loading Spinner -->
                    <svg v-if="isSearching" class="animate-spin ml-2 h-4 w-4 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    
                    <!-- Search Dropdown -->
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95 translate-y-[-10px]"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-[-10px]"
                    >
                        <div
                            v-if="showSearchDropdown && searchQuery.trim().length > 0"
                            class="absolute left-0 top-full mt-2 w-[400px] max-h-[70vh] overflow-y-auto rounded-lg bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-gray-900/5 dark:ring-gray-700 divide-y divide-gray-100 dark:divide-gray-700 z-[9999]"
                        >
                            <!-- Empty State -->
                            <div v-if="!isSearching && searchResults.length === 0" class="p-5 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Tidak ada hasil ditemukan</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
                            </div>
                            
                            <!-- Search Results -->
                            <template v-else>
                                <div v-for="(group, idx) in searchResults" :key="idx" class="py-2">
                                    <h3 class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/80 sticky top-0">
                                        {{ group.group }}
                                    </h3>
                                    <ul class="mt-1">
                                        <li v-for="item in group.items" :key="item.url">
                                            <Link
                                                :href="item.url"
                                                @click="handleSearchNavigation(item)"
                                                class="flex flex-col px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer group"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ item.title }}</span>
                                                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                </div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ item.subtitle }}</span>
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Dark Mode Toggle -->
                <button
                    @click="emit('toggle-dark-mode')"
                    :class="[
                        'p-2 rounded-lg transition-all duration-200',
                        darkMode
                            ? 'bg-gray-700 text-yellow-400'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                    title="Toggle Dark Mode"
                >
                    <svg v-if="darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                </button>

                <!-- Notifications -->
                <button
                    class="relative p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    title="Notifications"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- User Menu with Dropdown -->
                <div ref="dropdownRef" class="relative">
                    <!-- Desktop Version -->
                    <button
                        @click="toggleDropdown"
                        class="hidden sm:flex items-center space-x-3 pl-3 sm:pl-4 border-l border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg px-3 py-2 transition-colors"
                    >
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name || 'Admin' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ user?.role || 'Administrator' }}</p>
                        </div>
                        <div class="w-9 h-9 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
                        </div>
                        <svg
                            :class="['w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200', showProfileDropdown ? 'rotate-180' : '']"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Mobile Version -->
                    <button
                        @click="toggleDropdown"
                        class="sm:hidden flex items-center space-x-2 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors"
                    >
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showProfileDropdown"
                            class="absolute right-0 mt-2 w-56 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-900/5 dark:ring-gray-700"
                            style="z-index: 9999;"
                        >
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name || 'Admin' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user?.email || '' }}</p>
                            </div>
                            <div class="py-1">
                                <a
                                    :href="route('profile.show')"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile
                                </a>
                                <a
                                    :href="route('settings.index')"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 00-1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 001.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Settings
                                </a>
                            </div>
                            <div class="border-t border-gray-100 dark:border-gray-700 py-1">
                                <button
                                    @click="handleLogout"
                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </header>
</template>