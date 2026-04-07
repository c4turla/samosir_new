<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
// Safer way to access current user
const currentUser = computed(() => page.props.auth?.user || {});

const isOpen = ref(false);
const activeTab = ref('conversations'); // conversations, contacts, chat
const conversations = ref([]);
const contacts = ref([]);
const activeConversation = ref(null);
const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const messageContainer = ref(null);
const onlineUsers = ref(new Set());

const isOnline = (userId) => onlineUsers.value.has(userId);

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        if (activeTab.value === 'conversations') {
            fetchConversations();
        } else if (activeTab.value === 'contacts') {
            fetchContacts();
        }
        
        if (window.Echo) {
            joinPresenceChannel();
        }
    }
};

const joinPresenceChannel = () => {
    if (!window.Echo) return;
    
    window.Echo.join('chat-presence')
        .here((users) => {
            users.forEach(user => onlineUsers.value.add(user.id));
        })
        .joining((user) => {
            onlineUsers.value.add(user.id);
        })
        .leaving((user) => {
            onlineUsers.value.delete(user.id);
        });
};

const fetchConversations = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/chat/conversations');
        conversations.value = response.data;
    } catch (error) {
        console.error('Error fetching conversations:', error);
    } finally {
        isLoading.value = false;
    }
};

const fetchContacts = async () => {
    activeTab.value = 'contacts';
    isLoading.value = true;
    try {
        const response = await axios.get('/chat/users');
        contacts.value = response.data;
    } catch (error) {
        console.error('Error fetching contacts:', error);
    } finally {
        isLoading.value = false;
    }
};

const openConversation = async (conversation) => {
    if (!conversation) return;
    activeConversation.value = conversation;
    activeTab.value = 'chat';
    isLoading.value = true;
    
    try {
        const response = await axios.get(`/chat/conversations/${conversation.id}/messages`);
        messages.value = response.data;
        scrollToBottom();
        
        // Listen for new messages
        if (window.Echo) {
            window.Echo.private(`chat.${conversation.id}`)
                .listen('.message.sent', (e) => {
                    if (activeConversation.value?.id === e.message.conversation_id) {
                        messages.value.push(e.message);
                        scrollToBottom();
                    }
                    fetchConversations(); // Update list item info
                });
        }
    } catch (error) {
        console.error('Error fetching messages:', error);
    } finally {
        isLoading.value = false;
    }
};

const startChatWith = async (user) => {
    if (!user) return;
    isLoading.value = true;
    try {
        const response = await axios.post('/chat/messages', {
            receiver_id: user.id,
            message: 'Halo, saya ingin bertanya.'
        });
        
        await fetchConversations();
        const conv = conversations.value.find(c => c.id === response.data.conversation_id);
        if (conv) {
            openConversation(conv);
        } else {
            // Fallback if conversation not in list yet
            fetchConversations();
            activeTab.value = 'conversations';
        }
    } catch (error) {
        console.error('Error starting chat:', error);
    } finally {
        isLoading.value = false;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !activeConversation.value) return;

    const messageText = newMessage.value;
    newMessage.value = '';

    try {
        const response = await axios.post('/chat/messages', {
            conversation_id: activeConversation.value.id,
            message: messageText
        });
        
        messages.value.push(response.data);
        scrollToBottom();
        fetchConversations();
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const getParticipant = (conversation) => {
    if (!conversation || !conversation.participants) return { name: 'User' };
    return conversation.participants.find(p => p.id !== currentUser.value?.id) || { name: 'User' };
};

onMounted(() => {
    if (isOpen.value) fetchConversations();
});

onUnmounted(() => {
    if (activeConversation.value && window.Echo) {
        window.Echo.leave(`chat.${activeConversation.value.id}`);
    }
    if (window.Echo) {
        window.Echo.leave('chat-presence');
    }
});
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50">
        <!-- Toggle Button -->
        <button 
            @click="toggleChat"
            class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform cursor-pointer"
            :class="isOpen ? 'rotate-90' : ''"
        >
            <svg v-if="!isOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            
            <!-- Notification Badge -->
            <span v-if="conversations.some(c => !c.read_at && c.latest_message && c.latest_message.sender_id !== currentUser.id)" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 border-2 border-white rounded-full"></span>
        </button>

        <!-- Chat Window -->
        <div 
            v-if="isOpen"
            class="absolute bottom-16 right-0 w-80 sm:w-96 h-[500px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden transition-all duration-300"
        >
            <!-- Header -->
            <div class="p-4 bg-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button v-if="activeTab === 'chat' || activeTab === 'contacts'" @click="activeTab = 'conversations'" class="hover:bg-blue-500 p-1 rounded transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div>
                        <h3 class="font-bold text-sm">
                            <template v-if="activeTab === 'chat'">{{ getParticipant(activeConversation)?.name }}</template>
                            <template v-else-if="activeTab === 'contacts'">Cari Kontak</template>
                            <template v-else>Pusat Bantuan Samosir</template>
                        </h3>
                        <p class="text-[10px] opacity-80 flex items-center gap-1">
                            <template v-if="activeTab === 'chat'">
                                <span v-if="isOnline(getParticipant(activeConversation)?.id)" class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                {{ isOnline(getParticipant(activeConversation)?.id) ? 'Online' : 'Offline' }}
                            </template>
                            <template v-else>Komunikasi antar pengguna</template>
                        </p>
                    </div>
                </div>
                <div v-if="activeTab === 'conversations'">
                    <button @click="fetchContacts" class="bg-blue-500 hover:bg-blue-400 p-1.5 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 custom-scrollbar" ref="messageContainer">
                <!-- Loading State -->
                <div v-if="isLoading && activeTab !== 'chat'" class="flex flex-col items-center justify-center h-full text-gray-400 gap-2">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="text-xs">Memuat data...</p>
                </div>

                <!-- Conversations List -->
                <div v-else-if="activeTab === 'conversations'" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-for="conv in conversations" :key="conv.id" @click="openConversation(conv)" class="p-3 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-colors flex items-center gap-3 relative">
                        <div class="relative">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold">
                                {{ getParticipant(conv)?.name?.charAt(0) || 'U' }}
                            </div>
                            <span v-if="isOnline(getParticipant(conv)?.id)" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center mb-0.5">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ getParticipant(conv)?.name }}</h4>
                                <span class="text-[10px] text-gray-500">{{ conv.latest_message ? new Date(conv.latest_message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '' }}</span>
                            </div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                                {{ conv.latest_message ? conv.latest_message.body : 'Mulai percakapan...' }}
                            </p>
                        </div>
                    </div>
                    <div v-if="!isLoading && conversations.length === 0" class="p-10 text-center text-gray-500">
                        <div class="mb-3 flex justify-center">
                            <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-xs">Belum ada percakapan aktif.</p>
                        <button @click="fetchContacts" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-full text-xs font-bold hover:bg-blue-700 transition-colors">Cari Kontak</button>
                    </div>
                </div>

                <!-- Contacts List -->
                <div v-else-if="activeTab === 'contacts'" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-for="user in contacts" :key="user.id" @click="startChatWith(user)" class="p-3 hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-colors flex items-center gap-3">
                        <div class="relative">
                            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold">
                                {{ user.name?.charAt(0) || 'U' }}
                            </div>
                            <span v-if="isOnline(user.id)" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-white">{{ user.name }}</h4>
                            <p class="text-[10px] text-gray-500 uppercase">{{ user.role?.replace('_', ' ') }}</p>
                        </div>
                    </div>
                    <div v-if="!isLoading && contacts.length === 0" class="p-10 text-center text-gray-500">
                        <p class="text-xs">Tidak ada kontak lain ditemukan.</p>
                    </div>
                </div>

                <!-- Messages Room -->
                <div v-else-if="activeTab === 'chat'" class="p-4 flex flex-col gap-3 min-h-full">
                    <div v-if="isLoading" class="flex justify-center p-4">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                    </div>
                    <div v-for="msg in messages" :key="msg.id" :class="msg.sender_id === currentUser.id ? 'items-end' : 'items-start'" class="flex flex-col">
                        <div 
                            :class="msg.sender_id === currentUser.id ? 'bg-blue-600 text-white rounded-l-lg rounded-tr-lg' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-r-lg rounded-tl-lg shadow-sm border border-gray-100 dark:border-gray-700'"
                            class="max-w-[85%] px-3 py-2 text-xs leading-relaxed"
                        >
                            {{ msg.body }}
                        </div>
                        <span class="text-[8px] text-gray-400 mt-1 uppercase tracking-tighter">{{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer / Input -->
            <div v-if="activeTab === 'chat'" class="p-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <input 
                        v-model="newMessage"
                        type="text" 
                        placeholder="Tulis pesan..."
                        class="flex-1 bg-gray-50 dark:bg-gray-900 border-none rounded-full px-4 py-2 text-xs focus:ring-1 focus:ring-blue-500 dark:text-white"
                        autocomplete="off"
                    />
                    <button type="submit" :disabled="!newMessage.trim()" class="bg-blue-600 p-2 rounded-full text-white hover:bg-blue-700 disabled:opacity-50 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
}
</style>
