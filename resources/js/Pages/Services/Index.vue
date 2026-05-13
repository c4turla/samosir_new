<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 p-4">
            <!-- Header -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Menu Jasa</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola semua layanan jasa</p>
                </div>
            </div>

            <!-- Service Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Peralatan -->
                <Link
                    :href="route('equipment-services.index')"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border-l-4 border-blue-500 group"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Peralatan</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Kelola jasa penyewaan peralatan</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.equipment.count }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">pesanan</span>
                    </div>
                </Link>

                <!-- Ice Cruiser -->
                <Link
                    :href="route('ice-cruiser-services.index')"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border-l-4 border-cyan-500 group"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg">
                            <svg class="w-8 h-8 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-cyan-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Ice Cruiser</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Kelola jasa ice cruiser</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ stats.iceCruiser.count }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">pesanan</span>
                    </div>
                </Link>

                <!-- Air -->
                <Link
                    :href="route('water-services.index')"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border-l-4 border-blue-400 group"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-8 h-8 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Air</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Kelola jasa penyediaan air</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-blue-500 dark:text-blue-400">{{ stats.water.count }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">pesanan</span>
                    </div>
                </Link>
            </div>

            <!-- Recent Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Equipment Orders -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                        Pesanan Peralatan
                    </h3>
                    <div class="space-y-3">
                        <div v-if="recentEquipment.length === 0" class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">
                            Tidak ada pesanan
                        </div>
                        <div
                            v-for="order in recentEquipment"
                            :key="'eq-' + order.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                            @click="goToService('equipment', order.id)"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ order.order_number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.renter_name }}</p>
                            </div>
                            <StatusBadge :status="order.status" />
                        </div>
                    </div>
                    <Link
                        :href="route('equipment-services.index')"
                        class="mt-4 block text-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                        Lihat semua →
                    </Link>
                </div>

                <!-- Ice Cruiser Orders -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="w-3 h-3 bg-cyan-500 rounded-full mr-2"></span>
                        Pesanan Ice Cruiser
                    </h3>
                    <div class="space-y-3">
                        <div v-if="recentIceCruiser.length === 0" class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">
                            Tidak ada pesanan
                        </div>
                        <div
                            v-for="order in recentIceCruiser"
                            :key="'ice-' + order.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                            @click="goToService('ice-cruiser', order.id)"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ order.order_number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.renter_name }}</p>
                            </div>
                            <StatusBadge :status="order.status" />
                        </div>
                    </div>
                    <Link
                        :href="route('ice-cruiser-services.index')"
                        class="mt-4 block text-center text-sm text-cyan-600 hover:text-cyan-700 dark:text-cyan-400"
                    >
                        Lihat semua →
                    </Link>
                </div>

                <!-- Water Orders -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="w-3 h-3 bg-blue-400 rounded-full mr-2"></span>
                        Pesanan Air
                    </h3>
                    <div class="space-y-3">
                        <div v-if="recentWater.length === 0" class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">
                            Tidak ada pesanan
                        </div>
                        <div
                            v-for="order in recentWater"
                            :key="'water-' + order.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                            @click="goToService('water', order.id)"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ order.order_number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.customer_name || order.renter_name || '-' }}</p>
                            </div>
                            <StatusBadge :status="order.status" />
                        </div>
                    </div>
                    <Link
                        :href="route('water-services.index')"
                        class="mt-4 block text-center text-sm text-blue-500 hover:text-blue-600 dark:text-blue-400"
                    >
                        Lihat semua →
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            equipment: { count: 0 },
            iceCruiser: { count: 0 },
            water: { count: 0 }
        })
    },
    recentEquipment: Array,
    recentIceCruiser: Array,
    recentWater: Array
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Jasa', href: '#' }
];

const StatusBadge = {
    props: ['status'],
    setup(props) {
        const getStatusClass = (status) => {
            const classes = {
                'order': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                'processed': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                'completed': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                'in_progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                'done': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
        };

        const getStatusLabel = (status) => {
            const labels = {
                'order': 'Pesanan',
                'processed': 'Diproses',
                'completed': 'Selesai',
                'cancelled': 'Dibatalkan',
                'pending': 'Tertunda',
                'in_progress': 'Dalam Proses',
                'done': 'Selesai'
            };
            return labels[status] || status;
        };

        return { getStatusClass, getStatusLabel };
    },
    template: `
        <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(status)]">
            {{ getStatusLabel(status) }}
        </span>
    `
};

const goToService = (type, id) => {
    const routes = {
        'equipment': 'equipment-services.show',
        'ice-cruiser': 'ice-cruiser-services.show',
        'water': 'water-services.show'
    };
    router.visit(route(routes[type], id));
};
</script>
