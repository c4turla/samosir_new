<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import Highcharts from 'highcharts';
import 'highcharts/highcharts-more';



defineOptions({
  layout: AppLayout
});

const props = defineProps({
    stats: Object,
    recentArrivals: Array,
    recentDepartures: Array,
    pendingVessels: Array,
    monthlyStats: Array,
    weeklyStats: Array,
    yearlyStats: Array,
    vesselStatusDistribution: Object,
    arrivalStatusDistribution: Object,
    topSitesWeekly: Array,
    topSitesMonthly: Array,
    topSitesYearly: Array,
    topFishWeekly: Array,
    topFishMonthly: Array,
    topFishYearly: Array,
});

// Chart Filters
const timeFilter = ref('monthly');
const filterOptions = [
    { value: 'weekly', label: 'Mingguan' },
    { value: 'monthly', label: 'Bulanan' },
    { value: 'yearly', label: 'Tahunan' },
];

// Chart refs
const arrivalsChartRef = ref(null);
const fishSpeciesChartRef = ref(null);
const landingSitesChartRef = ref(null);

// Chart instances
let arrivalsChartInstance = null;
let fishSpeciesChartInstance = null;
let landingSitesChartInstance = null;

// Dark mode detection
const isDarkMode = ref(localStorage.getItem('darkMode') === 'true');

// Chart render counter to force re-render
const chartRenderKey = ref(0);

// Listen for dark mode changes
const handleDarkModeChange = () => {
    isDarkMode.value = localStorage.getItem('darkMode') === 'true';
    updateAllCharts();
};

// Update chart themes
const updateChartTheme = (options, isDark) => {
    const theme = isDark ? {
        chart: {
            backgroundColor: 'transparent',
            style: { color: '#ffffff' }
        },
        title: { style: { color: '#ffffff' } },
        xAxis: {
            labels: { style: { color: '#d1d5db' } },
            title: { style: { color: '#9ca3af' } },
            gridLineColor: '#374151'
        },
        yAxis: {
            labels: { style: { color: '#d1d5db' } },
            title: { style: { color: '#9ca3af' } },
            gridLineColor: '#374151'
        },
        legend: {
            itemStyle: { color: '#d1d5db' }
        },
        tooltip: {
            backgroundColor: 'rgba(31, 41, 55, 0.95)',
            style: { color: '#ffffff' },
            borderColor: '#4b5563'
        }
    } : {
        chart: {
            backgroundColor: 'transparent',
            style: { color: '#1f2937' }
        },
        title: { style: { color: '#1f2937' } },
        xAxis: {
            labels: { style: { color: '#4b5563' } },
            title: { style: { color: '#6b7280' } },
            gridLineColor: '#e5e7eb'
        },
        yAxis: {
            labels: { style: { color: '#4b5563' } },
            title: { style: { color: '#6b7280' } },
            gridLineColor: '#e5e7eb'
        },
        legend: {
            itemStyle: { color: '#4b5563' }
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            style: { color: '#1f2937' },
            borderColor: '#3b82f6'
        }
    };

    return Highcharts.merge(options, theme);
};

// Render arrivals chart
const renderArrivalsChart = () => {
    if (!arrivalsChartRef.value) return;
    
    // Destroy existing chart
    if (arrivalsChartInstance) {
        arrivalsChartInstance.destroy();
    }
    
    // Get data based on filter
    let stats;
    if (timeFilter.value === 'weekly') {
        stats = props.weeklyStats;
    } else if (timeFilter.value === 'yearly') {
        stats = props.yearlyStats;
    } else {
        stats = props.monthlyStats;
    }
    
    console.log('Rendering chart with filter:', timeFilter.value);
    console.log('Stats data:', stats);
    
    const arrivalsData = formatChartData(stats, timeFilter.value);
    console.log('Formatted data:', arrivalsData);
    
    const options = updateChartTheme(getArrivalsChartConfig(arrivalsData), isDarkMode.value);
    
    arrivalsChartInstance = Highcharts.chart(arrivalsChartRef.value, options);
};

// Donut chart colors
const fishChartColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];

// Get current fish data based on filter
const getCurrentFishData = () => {
    if (timeFilter.value === 'weekly') return props.topFishWeekly || [];
    if (timeFilter.value === 'yearly') return props.topFishYearly || [];
    return props.topFishMonthly || [];
};

// Get total weight of all top fish
const getTotalFishWeight = () => {
    return getCurrentFishData().reduce((sum, item) => sum + item.total_weight, 0);
};

// Format weight
const formatWeight = (weight) => {
    if (weight >= 1000) return (weight / 1000).toFixed(1) + ' ton';
    return weight.toLocaleString('id-ID') + ' kg';
};

// Get current sites data based on filter
const getCurrentSitesData = () => {
    if (timeFilter.value === 'weekly') return props.topSitesWeekly || [];
    if (timeFilter.value === 'yearly') return props.topSitesYearly || [];
    return props.topSitesMonthly || [];
};

// Render fish species donut chart
const renderFishSpeciesChart = () => {
    if (!fishSpeciesChartRef.value) return;
    
    // Destroy existing chart
    if (fishSpeciesChartInstance) {
        fishSpeciesChartInstance.destroy();
    }
    
    const fishData = getCurrentFishData();
    
    const options = updateChartTheme(getFishSpeciesChartConfig(fishData), isDarkMode.value);
    
    fishSpeciesChartInstance = Highcharts.chart(fishSpeciesChartRef.value, options);
};

// Render landing sites chart
const renderLandingSitesChart = () => {
    if (!landingSitesChartRef.value) return;
    
    // Destroy existing chart
    if (landingSitesChartInstance) {
        landingSitesChartInstance.destroy();
    }
    
    const sitesData = getCurrentSitesData();
    const options = updateChartTheme(getLandingSitesChartConfig(sitesData), isDarkMode.value);
    
    landingSitesChartInstance = Highcharts.chart(landingSitesChartRef.value, options);
};

// Update all charts with current theme
const updateAllCharts = async () => {
    // Force re-render by incrementing key
    chartRenderKey.value++;
    
    await nextTick();
    
    renderArrivalsChart();
    renderFishSpeciesChart();
    renderLandingSitesChart();
};

// Get chart configurations
const getArrivalsChartConfig = (data) => {
    // Debug: log data
    console.log('Filter:', timeFilter.value);
    console.log('Categories:', data.categories);
    console.log('Arrivals:', data.arrivals);
    console.log('Departures:', data.departures);
    
    return {
        chart: {
            type: 'area',
            style: {
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
            }
        },
        title: { text: null },
        xAxis: {
            type: 'category',
            categories: data.categories,
            labels: {
                rotation: timeFilter.value === 'yearly' ? 0 : -45,
                style: { fontSize: '10px' }
            }
        },
        yAxis: {
            title: { text: 'Jumlah', style: { fontSize: '11px' } }
        },
        tooltip: {
            shared: true,
            crosshairs: true,
            useHTML: true,
            style: { fontSize: '11px' }
        },
        legend: {
            enabled: true,
            align: 'center',
            verticalAlign: 'bottom',
            layout: 'horizontal',
            itemStyle: { fontSize: '11px' }
        },
        plotOptions: {
            area: {
                stacking: 'undefined',
                lineColor: '#666666',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#666666',
                    radius: 4
                }
            }
        },
        credits: { enabled: false },
        series: [
            {
                name: 'Kedatangan',
                data: data.arrivals,
                color: '#3B82F6',
                fillOpacity: 0.3,
                zIndex: 2,
                type: 'area'
            },
            {
                name: 'Keberangkatan',
                data: data.departures,
                color: '#EF4444',
                fillOpacity: 0.3,
                zIndex: 1,
                type: 'area'
            }
        ]
    };
};

const getFishSpeciesChartConfig = (fishData) => {
    const seriesData = fishData.map((item, index) => ({
        name: item.species_name || item.local_name || '-',
        y: item.total_weight,
        color: fishChartColors[index % fishChartColors.length],
    }));

    return {
        chart: {
            type: 'pie',
            style: {
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
            }
        },
        title: { text: null },
        tooltip: {
            pointFormat: '<b>{point.percentage:.1f}%</b> ({point.y} kg)',
            style: { fontSize: '11px' }
        },
        plotOptions: {
            pie: {
                innerSize: '60%',
                allowPointSelect: true,
                cursor: 'pointer',
                borderRadius: 6,
                borderWidth: 2,
                borderColor: isDarkMode.value ? '#1f2937' : '#ffffff',
                dataLabels: {
                    enabled: false
                },
                showInLegend: false
            }
        },
        legend: { enabled: false },
        credits: { enabled: false },
        series: [{
            name: 'Berat',
            colorByPoint: true,
            data: seriesData.length > 0 ? seriesData : [{ name: 'Tidak ada data', y: 1, color: '#d1d5db' }]
        }]
    };
};

const getLandingSitesChartConfig = (sitesData) => {
    const seriesData = sitesData.map((item, index) => ({
        name: item.site_name || '-',
        y: item.total_arrivals,
        color: fishChartColors[index % fishChartColors.length], // We can reuse the same premium colors
    }));

    return {
        chart: {
            type: 'pie',
            style: {
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
            }
        },
        title: { text: null },
        tooltip: {
            pointFormat: '<b>{point.percentage:.1f}%</b> ({point.y} kedatangan)',
            style: { fontSize: '11px' }
        },
        plotOptions: {
            pie: {
                innerSize: '60%',
                allowPointSelect: true,
                cursor: 'pointer',
                borderRadius: 6,
                borderWidth: 2,
                borderColor: isDarkMode.value ? '#1f2937' : '#ffffff',
                dataLabels: {
                    enabled: false
                },
                showInLegend: false
            }
        },
        legend: { enabled: false },
        credits: { enabled: false },
        series: [{
            name: 'Kedatangan',
            colorByPoint: true,
            data: seriesData.length > 0 ? seriesData : [{ name: 'Tidak ada data', y: 1, color: '#d1d5db' }]
        }]
    };
};

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
        if (filter === 'weekly') return item.day_name || item.day;
        if (filter === 'yearly') return `Tahun ${item.year}`;
        return item.month_name || item.month;
    });
    
    const arrivals = stats.map(item => item.arrivals || 0);
    const departures = stats.map(item => item.departures || 0);
    
    return { categories, arrivals, departures };
};

// Initialize charts on mount
onMounted(async () => {
    // Listen for dark mode changes
    window.addEventListener('storage', handleDarkModeChange);
    
    // Listen for custom event from AppLayout
    window.addEventListener('darkModeChanged', handleDarkModeChange);
    
    // Initialize charts
    await nextTick();
    updateAllCharts();
});

// Cleanup event listeners and charts
onUnmounted(() => {
    window.removeEventListener('storage', handleDarkModeChange);
    window.removeEventListener('darkModeChanged', handleDarkModeChange);
    
    // Destroy charts
    if (arrivalsChartInstance) {
        arrivalsChartInstance.destroy();
    }
    if (fishSpeciesChartInstance) {
        fishSpeciesChartInstance.destroy();
    }
    if (landingSitesChartInstance) {
        landingSitesChartInstance.destroy();
    }
});

// Update chart when time filter changes
watch(timeFilter, async () => {
    await nextTick();
    updateAllCharts();
});

// Watch for dark mode changes via localStorage
watch(() => localStorage.getItem('darkMode'), async () => {
    isDarkMode.value = localStorage.getItem('darkMode') === 'true';
    await nextTick();
    updateAllCharts();
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <!-- Page Title -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard SAMOSIR</h1>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga</p>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Filter:</label>
                    <select 
                        v-model="timeFilter"
                        class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Vessels -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-blue-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Total Kapal</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.total_vessels }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Vessels -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-green-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Kapal Aktif</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.active_vessels }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Vessels -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-yellow-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Pending Approval</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.pending_vessels }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-purple-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Total Pengguna</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.total_users }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row Stats -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Arrivals Today -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-indigo-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Kedatangan Hari Ini</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.arrivals_today }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departures Today -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-red-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Keberangkatan Hari Ini</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.departures_today }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vessels at Port -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-cyan-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Kapal Tambat</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.vessels_at_port }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vessels Unloading -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg transition-colors group hover:shadow-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-orange-500 rounded-md p-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Kapal Bongkar</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.vessels_unloading }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Arrivals & Departures Line Chart -->            
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Statistik Kedatangan & Keberangkatan</h2>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-1"></div>
                            <span class="text-[10px] text-gray-600 dark:text-gray-400">Kedatangan</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-500 rounded-full mr-1"></div>
                            <span class="text-[10px] text-gray-600 dark:text-gray-400">Keberangkatan</span>
                        </div>
                    </div>
                </div>
                <div ref="arrivalsChartRef" :key="`arrivals-${chartRenderKey}`" class="chart-sm"></div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Top Landing Sites Donut Chart -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Top Lokasi Pendaratan</h2>
                    <span class="text-[10px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                        {{ timeFilter === 'weekly' ? 'Minggu Ini' : timeFilter === 'monthly' ? 'Bulan Ini' : 'Tahun Ini' }}
                    </span>
                </div>
                <div v-if="getCurrentSitesData().length > 0" class="flex items-start gap-4">
                    <!-- Donut Chart -->
                    <div class="w-1/2 flex-shrink-0">
                        <div ref="landingSitesChartRef" :key="`landing-${chartRenderKey}`" style="height: 240px;"></div>
                    </div>
                    <!-- Landing Sites List -->
                    <div class="w-1/2 flex flex-col justify-center py-4">
                        <div class="space-y-3">
                            <div v-for="(site, index) in getCurrentSitesData()" :key="site.landing_site_id" 
                                 class="flex items-center justify-between group cursor-default hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg px-2 py-1.5 -mx-2 transition-colors">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1 dark:ring-offset-gray-800" 
                                          :style="{ backgroundColor: fishChartColors[index], ringColor: fishChartColors[index] + '40' }"></span>
                                    <span class="text-xs text-gray-700 dark:text-gray-300 truncate font-medium">{{ site.site_name }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ site.total_arrivals }}</span>
                                    <span class="text-[10px] text-gray-500 min-w-[32px]">kapal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center h-[240px] text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="text-sm">Belum ada data pendaratan</p>
                </div>
            </div>

            <!-- Top 5 Fish Species Donut Chart -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Data Per Jenis Ikan</h2>
                    <span class="text-[10px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                        {{ timeFilter === 'weekly' ? 'Minggu Ini' : timeFilter === 'monthly' ? 'Bulan Ini' : 'Tahun Ini' }}
                    </span>
                </div>
                <div v-if="getCurrentFishData().length > 0" class="flex items-start gap-4">
                    <!-- Donut Chart -->
                    <div class="w-1/2 flex-shrink-0">
                        <div ref="fishSpeciesChartRef" :key="`fish-${chartRenderKey}`" style="height: 240px;"></div>
                    </div>
                    <!-- Fish Species List -->
                    <div class="w-1/2 flex flex-col justify-center py-4">
                        <div class="space-y-3">
                            <div v-for="(fish, index) in getCurrentFishData()" :key="fish.fish_species_id" 
                                 class="flex items-center justify-between group cursor-default hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg px-2 py-1.5 -mx-2 transition-colors">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1 dark:ring-offset-gray-800" 
                                          :style="{ backgroundColor: fishChartColors[index], ringColor: fishChartColors[index] + '40' }"></span>
                                    <span class="text-xs text-gray-700 dark:text-gray-300 truncate font-medium">{{ fish.species_name }}</span>
                                </div>
                                <div class="flex items-baseline flex-shrink-0 ml-2">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ formatWeight(fish.total_weight) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Tangkapan</span>
                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ formatWeight(getTotalFishWeight()) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center h-[240px] text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="text-sm">Belum ada data tangkapan</p>
                </div>
            </div>
        </div>



            <!-- Tables Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Recent Arrivals -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg transition-colors">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Kedatangan Terbaru</h2>
                    </div>
                    <div class="overflow-x-auto max-h-64">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapal</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="arrival in recentArrivals" :key="arrival.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900 dark:text-white">{{ arrival.vessel?.vessel_name }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-900 dark:text-white">{{ formatTanggalPendek(arrival.arrival_date) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-full"
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
                                    <td colspan="3" class="px-4 py-3 text-center text-xs text-gray-500 dark:text-gray-400">
                                        Tidak ada data kedatangan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Departures -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg transition-colors">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Keberangkatan Terbaru</h2>
                    </div>
                    <div class="overflow-x-auto max-h-64">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapal</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="departure in recentDepartures" :key="departure.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900 dark:text-white">{{ departure.vessel?.vessel_name }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-900 dark:text-white">{{ formatTanggalPendek(departure.departure_date) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-full"
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
                                    <td colspan="3" class="px-4 py-3 text-center text-xs text-gray-500 dark:text-gray-400">
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
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pending Approval Kapal</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Kapal</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemilik</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">GT</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alat Tangkap</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="vessel in pendingVessels" :key="vessel.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white">{{ vessel.vessel_name }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 dark:text-white">{{ vessel.owner_name }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 dark:text-white">{{ vessel.gt }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 dark:text-white">{{ vessel.fishing_gear }}</div>
                                </td>
                            </tr>
                            <tr v-if="pendingVessels.length === 0">
                                <td colspan="4" class="px-4 py-3 text-center text-xs text-gray-500 dark:text-gray-400">
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
    height: 420px;
}

.chart-sm {
    height: 440px;
}

.highcharts-container {
    height: 100%;
}
</style>
