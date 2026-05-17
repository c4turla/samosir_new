<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { ref, watch, computed } from 'vue'

const props = defineProps({
    faqs: {
        type: Array,
        required: true
    }
});

const activeCategory = ref(props.faqs[0]?.category);
const expandedQuestion = ref(null);
const searchQuery = ref('');

const toggleQuestion = (id) => {
    expandedQuestion.value = expandedQuestion.value === id ? null : id;
};

// Filter FAQs based on search query
const filteredFaqs = computed(() => {
    if (!searchQuery.value.trim()) return props.faqs;
    const query = searchQuery.value.toLowerCase().trim();
    
    return props.faqs.map(category => {
        const matchingQuestions = category.questions.filter(faq => {
            return faq.question.toLowerCase().includes(query) || 
                   faq.answer.toLowerCase().includes(query);
        });
        return {
            ...category,
            questions: matchingQuestions
        };
    });
});

// Watch search query to auto-expand single results or auto-select first category with matches
watch(searchQuery, (newQuery) => {
    if (newQuery.trim()) {
        // Find first category that has matching questions and select it
        const firstCategoryWithResults = filteredFaqs.value.find(c => c.questions.length > 0);
        if (firstCategoryWithResults) {
            activeCategory.value = firstCategoryWithResults.category;
            
            // If there's exactly one question matching, expand it
            if (firstCategoryWithResults.questions.length === 1) {
                expandedQuestion.value = firstCategoryWithResults.questions[0].id;
            }
        }
    }
});
</script>

<template>
    <AppLayout>
        <Head title="Pusat Bantuan & FAQ - SAMOSIR" />
        
        <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center p-3 bg-blue-50 dark:bg-blue-900/30 rounded-2xl mb-4">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">
                    Bagaimana kami bisa membantu Anda?
                </h1>
                <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Temukan jawaban untuk pertanyaan umum terkait penggunaan Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga (SAMOSIR).
                </p>

                <!-- Search Input (Visual Only for now, but good for UI) -->
                <div class="mt-8 max-w-xl mx-auto relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        class="block w-full pl-11 pr-4 py-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                        placeholder="Cari pertanyaan atau topik..."
                    >
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Sidebar Navigation -->
                <div class="lg:col-span-3 sticky top-24">
                    <nav class="space-y-1">
                        <h3 class="px-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Kategori Topik</h3>
                        <button
                            v-for="category in filteredFaqs"
                            :key="category.category"
                            @click="activeCategory = category.category"
                            :class="[
                                'w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                                activeCategory === category.category
                                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 shadow-sm ring-1 ring-blue-500/20'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <i :class="[category.icon, 'mr-3 text-lg']"></i>
                            <span class="truncate">{{ category.category }}</span>
                            
                            <!-- Active Indicator -->
                            <svg v-if="activeCategory === category.category" class="ml-auto w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                    
                    <!-- Contact Card -->
                    <div class="mt-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/80 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Masih butuh bantuan?</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
                            Jika Anda tidak menemukan jawaban yang dicari, silakan hubungi tim dukungan kami.
                        </p>
                        <a href="/chat" class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                            Hubungi Admin via Chat
                        </a>
                    </div>
                </div>

                <!-- FAQ Accordion List -->
                <div class="lg:col-span-9 space-y-4">
                    <template v-for="category in filteredFaqs" :key="category.category">
                        <div v-show="activeCategory === category.category" class="space-y-4 animate-fadeIn">
                            
                            <!-- Category Title Header -->
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                    <i :class="[category.icon, 'text-xl']"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ category.category }}</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pertanyaan yang sering diajukan seputar {{ category.category.toLowerCase() }}</p>
                                </div>
                            </div>

                            <!-- Questions List -->
                            <div 
                                v-for="(faq, index) in category.questions" 
                                :key="faq.id"
                                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-all duration-200"
                                :class="{'shadow-md ring-1 ring-blue-500/10': expandedQuestion === faq.id}"
                            >
                                <button 
                                    @click="toggleQuestion(faq.id)"
                                    class="w-full flex items-center justify-between px-6 py-5 text-left focus:outline-none focus-visible:bg-gray-50 dark:focus-visible:bg-gray-700/50"
                                >
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white pr-4 leading-relaxed">
                                        {{ faq.question }}
                                    </span>
                                    <span class="flex-shrink-0 ml-2 rounded-full p-1 transition-colors" :class="expandedQuestion === faq.id ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600' : 'text-gray-400'">
                                        <svg 
                                            class="h-5 w-5 transform transition-transform duration-300 ease-in-out" 
                                            :class="{ 'rotate-180': expandedQuestion === faq.id }"
                                            fill="none" 
                                            viewBox="0 0 24 24" 
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </button>
                                
                                <div 
                                    class="overflow-hidden transition-all duration-300 ease-in-out"
                                    :style="{ maxHeight: expandedQuestion === faq.id ? '500px' : '0px', opacity: expandedQuestion === faq.id ? 1 : 0 }"
                                >
                                    <div class="px-6 pb-6 text-sm text-gray-600 dark:text-gray-400 leading-relaxed border-t border-gray-50 dark:border-gray-700/50 pt-4 mt-2 bg-gray-50/50 dark:bg-gray-800/30">
                                        {{ faq.answer }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Empty State for Search (Placeholder logic) -->
                            <div v-if="category.questions.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Tidak ditemukan</h3>
                                <p class="mt-1 text-sm text-gray-500">Tidak ada pertanyaan yang sesuai dengan kriteria pencarian.</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
