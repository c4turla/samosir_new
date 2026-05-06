<script setup>
import { ref, computed, onMounted, nextTick, watch, onUnmounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    initialConversations: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

// UI State
const activeTab = ref('messages'); // messages, active
const searchQuery = ref('');
const selectedConversationId = ref(null);
const messages = ref([]);
const newMessage = ref('');
const isLoadingMessages = ref(false);
const messageContainer = ref(null);
const conversations = ref(props.initialConversations);

// New Chat State
const showNewChatModal = ref(false);
const allUsers = ref([]);
const isFetchingUsers = ref(false);
const userSearchQuery = ref('');

// Helper for stable avatar colors
const avatarColors = [
    '#ff8a65', '#ef5350', '#26a69a', '#ba68c8', '#9575cd', 
    '#90a4ae', '#4db6ac', '#81c784', '#aed581', '#ffd54f'
];

const getAvatarColor = (name) => {
    if (!name) return avatarColors[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return avatarColors[Math.abs(hash) % avatarColors.length];
};

const getParticipant = (conversation) => {
    if (!conversation || !conversation.participants) return { name: 'Unknown' };
    return conversation.participants.find(p => p.id !== currentUser.value.id) || conversation.participants[0] || { name: 'Unknown' };
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true }).toLowerCase();
    } else if (days === 1) {
        return 'Yesterday';
    } else if (days < 7) {
        return date.toLocaleDateString([], { weekday: 'short' });
    } else {
        return date.toLocaleDateString([], { day: 'numeric', month: 'short' });
    }
};

const filteredConversations = computed(() => {
    let list = conversations.value;
    if (searchQuery.value) {
        list = list.filter(c => getParticipant(c).name.toLowerCase().includes(searchQuery.value.toLowerCase()));
    }
    return list;
});

const filteredUsers = computed(() => {
    if (!userSearchQuery.value) return allUsers.value;
    return allUsers.value.filter(u => u.name.toLowerCase().includes(userSearchQuery.value.toLowerCase()));
});

const selectedConversation = computed(() => {
    return conversations.value.find(c => c.id === selectedConversationId.value);
});

// Fetching Data
const fetchMessages = async (conversationId) => {
    isLoadingMessages.value = true;
    try {
        const response = await axios.get(`/chat/conversations/${conversationId}/messages`);
        messages.value = response.data;
        scrollToBottom();
    } catch (error) {
        console.error('Error fetching messages:', error);
    } finally {
        isLoadingMessages.value = false;
    }
};

const fetchUsers = async () => {
    isFetchingUsers.value = true;
    try {
        const response = await axios.get('/chat/users');
        allUsers.value = response.data;
    } catch (error) {
        console.error('Error fetching users:', error);
    } finally {
        isFetchingUsers.value = false;
    }
};

const selectConversation = (conv) => {
    if (selectedConversationId.value) {
        window.Echo.leave(`chat.${selectedConversationId.value}`);
    }

    selectedConversationId.value = conv.id;
    fetchMessages(conv.id);

    // Join Echo Channel
    window.Echo.private(`chat.${conv.id}`)
        .listen('.message.sent', (e) => {
            if (selectedConversationId.value === conv.id) {
                if (!messages.value.find(m => m.id === e.message.id)) {
                    messages.value.push(e.message);
                    scrollToBottom();
                }
            }
            // Update latest message in sidebar
            const c = conversations.value.find(chat => chat.id === conv.id);
            if (c) {
                c.latest_message = e.message;
                c.last_message_at = e.message.created_at;
                // Move to top
                const otherChats = conversations.value.filter(chat => chat.id !== conv.id);
                conversations.value = [c, ...otherChats];
            }
        });
};

const startConversation = async (userId) => {
    showNewChatModal.value = false;
    try {
        const response = await axios.post('/chat/conversations/get', {
            receiver_id: userId
        });
        
        const existingConv = conversations.value.find(c => c.id === response.data.id);
        if (!existingConv) {
            conversations.value.unshift(response.data);
        }
        
        selectConversation(response.data);
    } catch (error) {
        console.error('Error starting conversation:', error);
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const handleSend = async () => {
    if (!newMessage.value.trim() || !selectedConversationId.value) return;
    
    const messageBody = newMessage.value;
    newMessage.value = '';

    try {
        const response = await axios.post('/chat/messages', {
            conversation_id: selectedConversationId.value,
            message: messageBody
        });
        
        if (!messages.value.find(m => m.id === response.data.id)) {
            messages.value.push(response.data);
            scrollToBottom();
        }

        const c = conversations.value.find(chat => chat.id === selectedConversationId.value);
        if (c) {
            c.latest_message = response.data;
            c.last_message_at = response.data.created_at;
        }

    } catch (error) {
        console.error('Error sending message:', error);
        newMessage.value = messageBody;
    }
};

onMounted(() => {
    if (conversations.value && conversations.value.length > 0) {
        selectConversation(conversations.value[0]);
    }
    fetchUsers();
});

onUnmounted(() => {
    if (selectedConversationId.value) {
        window.Echo.leave(`chat.${selectedConversationId.value}`);
    }
});
</script>

<template>
    <Head title="Chat" />

    <AppLayout>
        <div class="chat-container bg-white dark:bg-gray-900 rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row h-[calc(100vh-160px)] transition-all duration-500 border border-gray-100 dark:border-gray-800">
            <!-- Sidebar -->
            <div class="chat-sidebar w-full md:w-[350px] border-r border-gray-50 dark:border-gray-800 flex flex-col h-full overflow-hidden transition-colors duration-300">
                <!-- Header & Search -->
                <div class="px-6 pt-8 pb-4 flex-shrink-0">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex space-x-6">
                            <button 
                                @click="activeTab = 'messages'"
                                :class="['pb-2 text-base font-bold transition-all relative group', activeTab === 'messages' ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500']"
                            >
                                Messages
                                <div :class="['absolute bottom-0 left-0 h-[3px] bg-green-500 rounded-full transition-all duration-300', activeTab === 'messages' ? 'w-full' : 'w-0 group-hover:w-1/2']"></div>
                            </button>
                            <button 
                                @click="activeTab = 'active'"
                                :class="['pb-2 text-base font-bold transition-all relative group', activeTab === 'active' ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500']"
                            >
                                Active
                                <div :class="['absolute bottom-0 left-0 h-[3px] bg-green-500 rounded-full transition-all duration-300', activeTab === 'active' ? 'w-full' : 'w-0 group-hover:w-1/2']"></div>
                            </button>
                        </div>
                        <button 
                            @click="showNewChatModal = true"
                            class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 transition-all shadow-sm"
                        >
                            <i class="ri-add-line text-xl"></i>
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <i class="ri-search-2-line text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search conversations..." 
                            class="w-full bg-gray-50 dark:bg-gray-800/50 border-none rounded-2xl py-3 pl-12 pr-5 text-sm text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:ring-0 focus:outline-none transition-all shadow-none"
                        >
                    </div>
                </div>

                <!-- Chat List -->
                <div class="flex-grow overflow-y-auto chat-list px-3 pb-6 h-full">
                    <div 
                        v-for="conv in filteredConversations" 
                        :key="conv.id"
                        @click="selectConversation(conv)"
                        :class="['flex items-center px-4 py-4 rounded-2xl cursor-pointer transition-all duration-300 mb-1 group border', selectedConversationId === conv.id ? 'bg-green-50/50 dark:bg-green-900/20 border-green-500/20 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-800 border-transparent']"
                    >
                        <!-- Avatar Container -->
                        <div class="relative flex-shrink-0">
                            <div 
                                class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-lg transform transition-transform group-hover:scale-105"
                                :style="{ background: `linear-gradient(135deg, ${getAvatarColor(getParticipant(conv).name)}, ${getAvatarColor(getParticipant(conv).name)}dd)` }"
                            >
                                {{ getParticipant(conv).name.charAt(0).toUpperCase() }}
                            </div>
                            <div 
                                v-if="true" 
                                class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full shadow-sm"
                            ></div>
                        </div>

                        <!-- Info Content -->
                        <div class="ml-4 flex-grow overflow-hidden">
                            <div class="flex justify-between items-center mb-0.5">
                                <h3 :class="['font-bold truncate text-[14px] transition-colors', selectedConversationId === conv.id ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-gray-100']">
                                    {{ getParticipant(conv).name }}
                                </h3>
                                <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 whitespace-nowrap uppercase tracking-wider">{{ formatTime(conv.last_message_at || conv.updated_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <p :class="['text-[11px] truncate flex-grow', conv.is_typing ? 'text-green-500 italic font-semibold' : 'text-gray-500 dark:text-gray-400']">
                                    {{ conv.latest_message?.body || 'No messages yet' }}
                                </p>
                                <div v-if="conv.unread_count > 0" class="ml-2 bg-green-500 text-white text-[9px] font-black w-5 h-5 rounded-lg flex items-center justify-center shadow-lg shadow-green-500/20 transform animate-bounce-subtle">
                                    {{ conv.unread_count }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredConversations.length === 0" class="flex flex-col items-center justify-center py-10 text-center px-6">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-4">
                            <i class="ri-chat-history-line text-3xl text-gray-300"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">No chats found</h4>
                        <p class="text-xs text-gray-500 mt-1">Start a new conversation</p>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="chat-main flex-grow flex flex-col bg-[#fbfbfd] dark:bg-[#0a0a0c] overflow-hidden transition-colors duration-500">
                <template v-if="selectedConversationId">
                    <!-- Chat Header -->
                    <div class="px-8 py-5 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800 flex items-center justify-between sticky top-0 z-10 transition-colors">
                        <div class="flex items-center">
                            <div class="relative flex-shrink-0 group cursor-pointer">
                                <div 
                                    class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-xl transition-all group-hover:rotate-6"
                                    :style="{ background: `linear-gradient(135deg, ${getAvatarColor(getParticipant(selectedConversation).name)}, ${getAvatarColor(getParticipant(selectedConversation).name)}dd)` }"
                                >
                                    {{ getParticipant(selectedConversation).name.charAt(0).toUpperCase() }}
                                </div>
                                <div 
                                    v-if="true" 
                                    class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-4 border-white dark:border-gray-900 rounded-full shadow-md"
                                ></div>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-black text-gray-900 dark:text-white text-lg tracking-tight leading-tight">{{ getParticipant(selectedConversation).name }}</h3>
                                <div class="flex items-center mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2 shadow-sm shadow-green-500/50"></span>
                                    <p class="text-[11px] font-bold text-green-500 uppercase tracking-widest">Online</p>
                                </div>
                            </div>
                        </div>

                        <!-- Header Actions -->
                        <div class="flex items-center space-x-2">
                            <button class="w-11 h-11 rounded-xl flex items-center justify-center text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300 transition-all border border-transparent hover:border-gray-100 dark:hover:border-gray-700 outline-none focus:outline-none">
                                <i class="ri-phone-line text-xl"></i>
                            </button>
                            <button class="w-11 h-11 rounded-xl flex items-center justify-center text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300 transition-all border border-transparent hover:border-gray-100 dark:hover:border-gray-700 outline-none focus:outline-none">
                                <i class="ri-vidicon-line text-xl"></i>
                            </button>
                            <div class="w-[1px] h-6 bg-gray-100 dark:bg-gray-800 mx-2"></div>
                            <button class="w-11 h-11 rounded-xl flex items-center justify-center text-gray-400 dark:text-gray-500 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-all border border-transparent hover:border-red-100 dark:hover:border-red-900/30 outline-none focus:outline-none">
                                <i class="ri-delete-bin-line text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Feed -->
                    <div ref="messageContainer" class="flex-grow overflow-y-auto px-10 py-10 space-y-8 scroll-smooth">
                        <div v-if="isLoadingMessages" class="flex justify-center items-center h-full">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
                        </div>
                        <template v-else>
                            <template v-for="(msg, index) in messages" :key="msg.id">
                                <!-- Message Received -->
                                <div v-if="msg.sender_id !== currentUser.id" class="flex items-end space-x-4 mb-6 animate-fade-in-up">
                                    <div class="w-10 h-10 rounded-2xl flex-shrink-0 flex items-center justify-center text-white font-bold text-xs shadow-lg transition-all"
                                         :class="[index < messages.length - 1 && messages[index+1].sender_id !== currentUser.id ? 'opacity-0 translate-y-2' : 'opacity-100 translate-y-0']"
                                         :style="{ background: `linear-gradient(135deg, ${getAvatarColor(msg.sender?.name || 'User')}, ${getAvatarColor(msg.sender?.name || 'User')}dd)` }">
                                        {{ (msg.sender?.name || 'U').charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="max-w-[75%] flex flex-col items-start">
                                        <div class="bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 p-4.5 rounded-3xl rounded-bl-none shadow-sm border border-gray-100 dark:border-gray-700/50 text-[15px] leading-relaxed transition-all hover:shadow-md">
                                            {{ msg.body }}
                                        </div>
                                        <span v-if="index === messages.length - 1 || messages[index+1].sender_id === currentUser.id" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 ml-1 mt-2 uppercase tracking-tighter">{{ formatTime(msg.created_at) }}</span>
                                    </div>
                                </div>

                                <!-- Message Sent -->
                                <div v-else class="flex items-end justify-end space-x-4 mb-6 animate-fade-in-up">
                                    <div class="max-w-[75%] flex flex-col items-end">
                                        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-4.5 rounded-3xl rounded-br-none shadow-xl shadow-green-500/10 text-[15px] leading-relaxed transition-all hover:shadow-green-500/20 active:scale-[0.98]">
                                            {{ msg.body }}
                                        </div>
                                        <div class="flex items-center mt-2 space-x-3">
                                            <span v-if="index === messages.length - 1 || messages[index+1].sender_id !== currentUser.id" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tighter">{{ formatTime(msg.created_at) }}</span>
                                            <div v-if="index === messages.length - 1 || messages[index+1].sender_id !== currentUser.id" class="w-6 h-6 rounded-xl bg-indigo-500 flex items-center justify-center text-white font-black text-[9px] shadow-lg shadow-indigo-500/20 transform rotate-12">Me</div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>

                    <!-- Message Input Area -->
                    <div class="p-8 bg-white dark:bg-gray-900 border-t border-gray-50 dark:border-gray-800 transition-colors">
                        <div class="flex items-center bg-gray-50 dark:bg-gray-800/50 border border-transparent rounded-[2rem] px-6 py-1 transition-all focus-within:bg-white dark:focus-within:bg-gray-800 focus-within:shadow-2xl focus-within:shadow-green-500/5 group">
                            <div class="flex items-center space-x-2 mr-4 text-gray-400">
                                <button class="w-10 h-10 rounded-full hover:bg-white dark:hover:bg-gray-700 hover:text-green-500 transition-all outline-none focus:outline-none">
                                    <i class="ri-camera-line text-xl"></i>
                                </button>
                            </div>
                            
                            <input 
                                v-model="newMessage"
                                @keyup.enter="handleSend"
                                type="text" 
                                placeholder="Type something here..." 
                                class="flex-grow bg-transparent border-none focus:ring-0 focus:outline-none text-base text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-600 py-3 font-medium outline-none shadow-none"
                            >

                            <div class="flex items-center space-x-2 ml-4">
                                <button class="w-10 h-10 rounded-full text-gray-400 hover:bg-white dark:hover:bg-gray-700 hover:text-green-500 transition-all outline-none focus:outline-none">
                                    <i class="ri-attachment-line text-xl"></i>
                                </button>
                                <button 
                                    @click="handleSend"
                                    :disabled="!newMessage.trim()"
                                    :class="['w-12 h-12 rounded-2xl flex items-center justify-center shadow-2xl transition-all transform active:scale-90 ml-2 outline-none focus:outline-none border-none', newMessage.trim() ? 'bg-green-500 hover:bg-green-600 text-white shadow-green-500/30' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed']"
                                >
                                    <i class="ri-send-plane-2-fill text-2xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <div v-else class="flex flex-col items-center justify-center h-full text-center p-10">
                    <div class="w-32 h-32 bg-gray-50 dark:bg-gray-800 rounded-[3rem] flex items-center justify-center mb-6 shadow-inner">
                        <i class="ri-chat-smile-3-line text-6xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Welcome back, {{ currentUser.name }}!</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-xs">Select a conversation from the left to start messaging.</p>
                    <button 
                        @click="showNewChatModal = true"
                        class="mt-6 bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-2xl font-bold shadow-xl shadow-green-500/20 transition-all transform hover:scale-105 active:scale-95"
                    >
                        Start New Chat
                    </button>
                </div>
            </div>
        </div>

        <!-- New Chat Modal -->
        <div v-if="showNewChatModal" class="fixed inset-0 z-[100] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showNewChatModal = false"></div>
            <div class="bg-white dark:bg-gray-900 w-full max-w-md rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col max-h-[80vh] animate-fade-in-up">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">New Message</h3>
                        <button @click="showNewChatModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i class="ri-close-line text-2xl"></i>
                        </button>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <i class="ri-user-search-line text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                        </div>
                        <input 
                            v-model="userSearchQuery"
                            type="text" 
                            placeholder="Search by name..." 
                            class="w-full bg-gray-50 dark:bg-gray-800/50 border-none rounded-2xl py-3 pl-12 pr-5 text-sm text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:ring-0 focus:outline-none transition-all shadow-none"
                        >
                    </div>
                </div>
                <div class="flex-grow overflow-y-auto p-4">
                    <div v-if="isFetchingUsers" class="flex justify-center py-10">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
                    </div>
                    <div v-else-if="filteredUsers.length > 0" class="space-y-1">
                        <div 
                            v-for="user in filteredUsers" 
                            :key="user.id"
                            @click="startConversation(user.id)"
                            class="flex items-center p-4 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-all group"
                        >
                            <div 
                                class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-lg"
                                :style="{ background: `linear-gradient(135deg, ${getAvatarColor(user.name)}, ${getAvatarColor(user.name)}dd)` }"
                            >
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="ml-4">
                                <h4 class="font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">{{ user.name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ user.role || 'User' }}</p>
                            </div>
                            <i class="ri-arrow-right-s-line ml-auto text-gray-300 group-hover:text-green-500 transition-all opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0"></i>
                        </div>
                    </div>
                    <div v-else class="text-center py-10">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-3xl flex items-center justify-center mx-auto mb-4">
                            <i class="ri-user-unfollow-line text-3xl text-gray-300"></i>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No users found matching your search.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.chat-container {
    height: calc(100vh - 160px);
    min-height: 600px;
}

/* Custom Scrollbar */
.chat-list::-webkit-scrollbar,
.chat-main .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.chat-list::-webkit-scrollbar-thumb,
.chat-main .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.dark .chat-list::-webkit-scrollbar-thumb,
.dark .chat-main .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.1);
}

.chat-list::-webkit-scrollbar-track,
.chat-main .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes bounceSubtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.animate-bounce-subtle {
    animation: bounceSubtle 2s infinite ease-in-out;
}

/* Premium Dark Mode Adjustments */
.dark .chat-main {
    background: radial-gradient(circle at top right, #0a0a0c, #000000);
}

.dark .bg-white {
    background-color: #111827;
}

.dark .border-gray-100 {
    border-color: #1f2937;
}

/* Fix input outline and ring globally for this component */
input:focus, button:focus, textarea:focus {
    outline: none !important;
    box-shadow: none !important;
    border-color: transparent !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chat-sidebar {
        height: 100%;
    }
    .chat-main {
        display: none;
    }
}
</style>
