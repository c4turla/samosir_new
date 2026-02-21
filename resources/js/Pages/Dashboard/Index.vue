<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { ref, provide, watch, onMounted } from 'vue';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import {
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent
} from 'echarts/components';
import { LineChart, BarChart, PieChart } from 'echarts/charts';
import VChart, { THEME_KEY } from 'vue-echarts';

// Register ECharts components
use([
    CanvasRenderer,
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent,
    LineChart,
    BarChart,
    PieChart
]);

// Provide theme based on dark mode
provide(THEME_KEY, ref(localStorage.getItem('darkMode') === 'true' ? 'dark' : 'light'));

defineOptions({
  layout: AppLayout
});

const props = defineProps({
    stats: Object,
    recentArrivals: Array,
    recentDepartures: Array,
    pendingVessels: Array,
    monthlyStats: Array,
    vesselStatusDistribution: Object,
    arrivalStatusDistribution: Object,
    topLandingSites: Array,
});

// Chart Filters
const timeFilter = ref('monthly');
const filterOptions = [
    { value: 'weekly', label: 'Mingguan' },
    { value: 'monthly', label: 'Bulanan' },
    { value: 'yearly', label: 'Tahunan' },
];

// Chart Options
const arrivalsChartOption = ref({});
const departuresChartOption = ref({});
const vesselStatusOption = ref({});
const landingSitesOption = ref({});

// Format tanggal ke format Indonesia
const formatTanggal = (dateString) => {
    if (!dateString) return '-';
    
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', options);
    } catch (error) {
        return dateString;
    }
};

// Format tanggal pendek
const formatTanggalPendek = (dateString) => {
    if (!dateString) return '-';
    
    const options = { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric' 
    };
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', options);
    } catch (error) {
        return dateString;
    }
};

// Format data for charts
const formatChartData = (stats, filter) => {
    if (!stats || stats.length === 0) return { categories: [], arrivals: [], departures: [] };
    
    const categories = stats.map(item => {
        if (filter === 'weekly') return `Minggu ${item.week || item.month}`;
        if (filter === 'yearly') return `Tahun ${item.year || item.month}`;
        return item.month_name || item.month;
    });
    
    const arrivals = stats.map(item => item.arrivals || 0);
    const departures = stats.map(item => item.departures || 0);
    
    return { categories, arrivals, departures };
};

// Vessel Status Pie Chart
const updateVesselStatusChart = () => {
    vesselStatusOption.value = {
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: {c} ({d}%)'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            data: ['Disetujui', 'Pending', 'Ditolak']
        },
        series: [
            {
                name: 'Status Kapal',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: true,
                    position: 'outside',
                    formatter: '{b}: {c}'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 16,
                        fontWeight: 'bold'
                    },
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)',
                    },
                },
                data: [
                    { value: props.vesselStatusDistribution?.approved || 0, name: 'Disetujui', itemStyle: { color: '#10B981' } },
                    { value: props.vesselStatusDistribution?.pending || 0, name: 'Pending', itemStyle: { color: '#F59E0B' } },
                    { value: props.vesselStatusDistribution?.rejected || 0, name: 'Ditolak', itemStyle: { color: '#EF4444' } }
                ]
            }
        ]
    };
};

// Landing Sites Bar Chart
const updateLandingSitesChart = () => {
    if (!props.topLandingSites || props.topLandingSites.length === 0) {
        landingSitesOption.value = {};
        return;
    }

    const sites = props.topLandingSites.map((item, index) => `Top ${index + 1}`);
    const counts = props.topLandingSites.map(item => item.count);
    const names = props.topLandingSites.map(item => item.landing_site?.name || '-');

    landingSitesOption.value = {
        tooltip: {
            trigger: 'axis',
            formatter: function(params) {
                return `${names[params[0].dataIndex]}: ${params[0].value} kedatangan`;
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: sites,
            axisLabel: {
                rotate: 0
            }
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: 'Kedatangan',
                type: 'bar',
                data: counts,
                itemStyle: {
                    borderRadius: [8, 8, 0, 0],
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [
                            { offset: 0, color: '#3B82F6' },
                            { offset: 1, color: '#1E40AF' }
                        ]
                    }
                },
                emphasis: {
                    itemStyle: {
                        color: '#60A5FA',
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)',
                    }
                }
            }
        ]
    };
};

// Arrivals Line Chart
const updateArrivalsChart = () => {
    const data = formatChartData(props.monthlyStats, timeFilter.value);

    arrivalsChartOption.value = {
        tooltip: {
            trigger: 'axis',
            formatter: function(params) {
                let result = `<div style="font-weight:bold;margin-bottom:5px">${params[0].name}</div>`;
                params.forEach(param => {
                    result += `<div style="display:flex;justify-content:space-between;align-items:center;margin:2px 0">
                        <span style="margin-right:15px;display:inline-flex;align-items:center">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:8px;background:${param.color}"></span>
                            ${param.seriesName}
                        </span>
                        <span style="font-weight:bold">${param.value}</span>
                    </div>`;
                });
                return result;
            }
        },
        legend: {
            data: ['Kedatangan', 'Keberangkatan']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: data.categories,
            axisLabel: {
                rotate: timeFilter.value === 'yearly' ? 0 : 45
            }
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: 'Kedatangan',
                type: 'line',
                smooth: true,
                data: data.arrivals,
                lineStyle: {
                    width: 3,
                    color: '#3B82F6'
                },
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [
                            { offset: 0, color: 'rgba(59, 130, 246, 0.3)' },
                            { offset: 1, color: 'rgba(59, 130, 246, 0.05)' }
                        ]
                    }
                },
                itemStyle: {
                    color: '#3B82F6'
                },
                emphasis: {
                    focus: 'series',
                    itemStyle: {
                        color: '#60A5FA',
                        borderColor: '#3B82F6',
                        borderWidth: 2,
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)',
                    }
                }
            },
            {
                name: 'Keberangkatan',
                type: 'line',
                smooth: true,
                data: data.departures,
                lineStyle: {
                    width: 3,
                    color: '#EF4444'
                },
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [
                            { offset: 0, color: 'rgba(239, 68, 68, 0.3)' },
                            { offset: 1, color: 'rgba(239, 68, 68, 0.05)' }
                        ]
                    }
                },
                itemStyle: {
                    color: '#EF4444'
                },
                emphasis: {
                    focus: 'series',
                    itemStyle: {
                        color: '#F87171',
                        borderColor: '#EF4444',
                        borderWidth: 2,
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)',
                    }
                }
            }
        ]
    };
};

// Initialize charts on mount
onMounted(() => {
    updateArrivalsChart();
    updateVesselStatusChart();
    updateLandingSitesChart();
});

// Update chart when time filter changes
watch(timeFilter, () => {
    updateArrivalsChart();
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <!-- Page Title -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard SAMOSIR</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga</p>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter:</label>
                    <select 
                        v-model="timeFilter"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Vessels -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-blue-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Kapal</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_vessels }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Vessels -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-green-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Kapal Aktif</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.active_vessels }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Vessels -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-yellow-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending Approval</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.pending_vessels }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-purple-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Pengguna</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_users }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row Stats -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Arrivals Today -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-indigo-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Kedatangan Hari Ini</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.arrivals_today }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departures Today -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-red-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Keberangkatan Hari Ini</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.departures_today }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vessels at Port -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-cyan-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Kapal Tambat</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.vessels_at_port }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vessels Unloading -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-orange-500 rounded-md p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Kapal Bongkar</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.vessels_unloading }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Arrivals & Departures Line Chart -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Kedatangan & Keberangkatan</h2>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Kedatangan</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Keberangkatan</span>
                            </div>
                        </div>
                    </div>
                    <VChart :option="arrivalsChartOption" class="chart" autoresize />
                </div>

                <!-- Vessel Status Pie Chart -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 transition-colors">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribusi Status Kapal</h2>
                    <VChart :option="vesselStatusOption" class="chart" autoresize />
                </div>
            </div>

            <!-- Top Landing Sites Chart -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 transition-colors">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Lokasi Pendaratan</h2>
                <VChart :option="landingSitesOption" class="chart" autoresize />
            </div>

            <!-- Tables Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Arrivals -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg transition-colors">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Kedatangan Terbaru</h2>
                    </div>
                    <div class="overflow-x-auto max-h-64">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="arrival in recentArrivals" :key="arrival.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ arrival.vessel?.vessel_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ formatTanggalPendek(arrival.arrival_date) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                  'bg-blue-100 text-blue-800': arrival.status === 'TAMBAT',
                                                  'bg-yellow-100 text-yellow-800': arrival.status === 'BONGKAR',
                                                  'bg-green-100 text-green-800': arrival.status === 'SELESAI'
                                              }">
                                            {{ arrival.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="recentArrivals.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Tidak ada data kedatangan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Departures -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg transition-colors">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Keberangkatan Terbaru</h2>
                    </div>
                    <div class="overflow-x-auto max-h-64">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="departure in recentDepartures" :key="departure.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ departure.vessel?.vessel_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ formatTanggalPendek(departure.departure_date) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                  'bg-green-100 text-green-800': departure.status === 'Sesuai Jadwal',
                                                  'bg-yellow-100 text-yellow-800': departure.status === 'Terlambat',
                                                  'bg-red-100 text-red-800': departure.status === 'Dibatalkan'
                                              }">
                                            {{ departure.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="recentDepartures.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Tidak ada data keberangkatan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pending Vessels -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg transition-colors">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Approval Kapal</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Kapal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemilik</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">GT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alat Tangkap</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="vessel in pendingVessels" :key="vessel.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ vessel.vessel_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ vessel.owner_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ vessel.gt }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ vessel.fishing_gear }}</div>
                                </td>
                            </tr>
                            <tr v-if="pendingVessels.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada kapal yang menunggu approval
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</template>

<style scoped>
.chart {
    height: 320px;
}
</style>