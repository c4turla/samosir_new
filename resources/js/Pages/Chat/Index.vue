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

// Online Presence State
const onlineUsers = ref(new Set());

const isOnline = (userId) => onlineUsers.value.has(userId);

const joinPresenceChannel = () => {
    if (!window.Echo) return;
    window.Echo.join('chat-presence')
        .here((users) => {
            const ids = new Set(users.map(u => u.id));
            onlineUsers.value = ids;
        })
        .joining((user) => {
            onlineUsers.value = new Set([...onlineUsers.value, user.id]);
        })
        .leaving((user) => {
            const next = new Set(onlineUsers.value);
            next.delete(user.id);
            onlineUsers.value = next;
        })
        .error((error) => {
            console.warn('Presence channel error:', error);
        });
};

// Active users list (users who are online and have conversations)
const activeOnlineUsers = computed(() => {
    const participantIds = new Set();
    const participants = [];
    conversations.value.forEach(conv => {
        const p = getParticipant(conv);
        if (p && p.id && !participantIds.has(p.id) && isOnline(p.id)) {
            participantIds.add(p.id);
            participants.push(p);
        }
    });
    return participants;
});

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
        })
        .listen('.message.updated', (e) => {
            if (selectedConversationId.value === conv.id) {
                const idx = messages.value.findIndex(m => m.id === e.message.id);
                if (idx !== -1) {
                    messages.value[idx] = e.message;
                }
            }
        })
        .listen('.message.deleted', (e) => {
            if (selectedConversationId.value === conv.id) {
                const idx = messages.value.findIndex(m => m.id === e.message.id);
                if (idx !== -1) {
                    messages.value[idx] = e.message;
                }
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
    if ((!newMessage.value.trim() && !selectedFile.value) || !selectedConversationId.value) return;
    
    const messageBody = newMessage.value;
    newMessage.value = '';
    const fileToSend = selectedFile.value;
    selectedFile.value = null;
    filePreview.value = null;

    try {
        const formData = new FormData();
        formData.append('conversation_id', selectedConversationId.value);
        if (messageBody.trim()) formData.append('message', messageBody);
        if (fileToSend) formData.append('file', fileToSend);

        const response = await axios.post('/chat/messages', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
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

// File Attachment State
const selectedFile = ref(null);
const filePreview = ref(null);
const fileInputRef = ref(null);

const onFileSelected = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    selectedFile.value = file;
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => { filePreview.value = e.target.result; };
        reader.readAsDataURL(file);
    } else {
        filePreview.value = null;
    }
    // Reset input so same file can be selected again
    event.target.value = '';
};

const clearFile = () => {
    selectedFile.value = null;
    filePreview.value = null;
};

const isImage = (mimeType) => mimeType && mimeType.startsWith('image/');

const fileIconClass = (mimeType) => {
    if (!mimeType) return 'ri-file-line';
    if (mimeType.includes('pdf')) return 'ri-file-pdf-line text-red-500';
    if (mimeType.includes('word') || mimeType.includes('document')) return 'ri-file-word-line text-blue-500';
    if (mimeType.includes('sheet') || mimeType.includes('excel')) return 'ri-file-excel-line text-green-600';
    if (mimeType.includes('zip') || mimeType.includes('rar')) return 'ri-file-zip-line text-yellow-500';
    return 'ri-file-line text-gray-500';
};

// Delete Conversation
const showDeleteConfirm = ref(false);
const conversationToDelete = ref(null);

// Delete Message
const showDeleteMessageConfirm = ref(false);
const messageToDelete = ref(null);

// Edit Message
const editingMessageId = ref(null);
const editMessageText = ref('');
const editMessageInputRef = ref(null);

const confirmDeleteConversation = (conv) => {
    conversationToDelete.value = conv;
    showDeleteConfirm.value = true;
};

// Delete Message
const confirmDeleteMessage = (msg) => {
    messageToDelete.value = msg;
    showDeleteMessageConfirm.value = true;
};

const deleteMessage = async () => {
    if (!messageToDelete.value) return;
    const msgId = messageToDelete.value.id;
    showDeleteMessageConfirm.value = false;
    try {
        const response = await axios.delete(`/chat/messages/${msgId}`);
        const idx = messages.value.findIndex(m => m.id === msgId);
        if (idx !== -1) {
            messages.value[idx] = response.data;
        }
    } catch (error) {
        console.error('Error deleting message:', error);
    }
    messageToDelete.value = null;
};

// Edit Message
const startEditMessage = (msg) => {
    editingMessageId.value = msg.id;
    editMessageText.value = msg.body;
    nextTick(() => {
        if (editMessageInputRef.value) {
            editMessageInputRef.value.focus();
        }
    });
};

const cancelEditMessage = () => {
    editingMessageId.value = null;
    editMessageText.value = '';
};

const saveEditMessage = async (msg) => {
    if (!editMessageText.value.trim()) return;
    try {
        const response = await axios.put(`/chat/messages/${msg.id}`, {
            body: editMessageText.value.trim()
        });
        const idx = messages.value.findIndex(m => m.id === msg.id);
        if (idx !== -1) {
            messages.value[idx] = response.data;
        }
    } catch (error) {
        console.error('Error editing message:', error);
    }
    editingMessageId.value = null;
    editMessageText.value = '';
};

const deleteConversation = async () => {
    if (!conversationToDelete.value) return;
    const convId = conversationToDelete.value.id;
    showDeleteConfirm.value = false;
    conversationToDelete.value = null;

    try {
        await axios.delete(`/chat/conversations/${convId}`);
        // Remove from list
        conversations.value = conversations.value.filter(c => c.id !== convId);
        // Deselect if it was selected
        if (selectedConversationId.value === convId) {
            selectedConversationId.value = null;
            messages.value = [];
            if (window.Echo) window.Echo.leave(`chat.${convId}`);
        }
    } catch (error) {
        console.error('Error deleting conversation:', error);
    }
};

// Full Screen Image
const fullScreenImage = ref(null);

const openFullScreenImage = (url) => {
    fullScreenImage.value = url;
};

const closeFullScreenImage = () => {
    fullScreenImage.value = null;
};

// Visual Viewport tracking for mobile keyboard
const keyboardHeightOffset = ref(0);

const updateViewport = () => {
    if (!window.visualViewport) return;
    const vv = window.visualViewport;
    
    if (window.innerWidth < 768) {
        // Calculate offset between layout viewport and visual viewport
        const offset = window.innerHeight - vv.height;
        keyboardHeightOffset.value = offset > 40 ? offset : 0;
        
        if (keyboardHeightOffset.value > 0) {
            setTimeout(() => {
                scrollToBottom();
            }, 100);
        }
    } else {
        keyboardHeightOffset.value = 0;
    }
};

const handleInputFocus = () => {
    if (window.innerWidth < 768) {
        setTimeout(() => {
            scrollToBottom();
        }, 150);
    }
};

const mobileContainerStyle = computed(() => {
    if (typeof window === 'undefined') return {};
    if (window.innerWidth >= 768) return {};
    
    return {
        bottom: `${keyboardHeightOffset.value}px`,
        transition: 'none'
    };
});

const inputAreaStyle = computed(() => {
    if (typeof window === 'undefined') return {};
    if (window.innerWidth >= 768) return {};
    
    if (keyboardHeightOffset.value === 0) {
        return {
            paddingBottom: 'calc(env(safe-area-inset-bottom) + 4px)'
        };
    }
    return {
        paddingBottom: '4px'
    };
});

onMounted(() => {
    if (typeof window !== 'undefined') {
        if (window.innerWidth >= 768) {
            if (conversations.value && conversations.value.length > 0) {
                selectConversation(conversations.value[0]);
            }
        }
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', updateViewport);
            window.visualViewport.addEventListener('scroll', updateViewport);
        }
        updateViewport();
    }
    fetchUsers();
    // Join presence channel on page load
    joinPresenceChannel();
});

onUnmounted(() => {
    if (selectedConversationId.value) {
        window.Echo.leave(`chat.${selectedConversationId.value}`);
    }
    if (window.Echo) {
        window.Echo.leave('chat-presence');
    }
    if (typeof window !== 'undefined' && window.visualViewport) {
        window.visualViewport.removeEventListener('resize', updateViewport);
        window.visualViewport.removeEventListener('scroll', updateViewport);
    }
});
</script>

<template>
    <Head title="Chat" />

    <AppLayout>
        <!-- Mobile: fixed full-screen below header. Desktop: normal flow -->
        <div :style="mobileContainerStyle" class="fixed inset-x-0 bottom-0 top-[3.5rem] md:static md:top-auto md:inset-x-auto md:bottom-auto md:h-[calc(100vh-160px)] chat-container bg-white dark:bg-gray-900 md:rounded-3xl md:shadow-2xl overflow-hidden flex flex-col md:flex-row md:transition-all md:duration-500 md:border md:border-gray-100 md:dark:border-gray-800 z-20">
            <!-- Sidebar (Contact List) -->
            <div :class="[
                'w-full md:w-[350px] border-r border-gray-100 dark:border-gray-800 flex-col overflow-hidden transition-all duration-300 bg-white dark:bg-gray-900',
                'md:flex',
                selectedConversationId ? 'hidden' : 'flex'
            ]">
                <!-- Header & Search -->
                <div class="px-4 pt-5 pb-3 flex-shrink-0">
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
                                :class="['pb-2 text-base font-bold transition-all relative group flex items-center gap-2', activeTab === 'active' ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500']"
                            >
                                Active
                                <span v-if="activeOnlineUsers.length > 0" class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-black bg-green-500 text-white rounded-full shadow-sm">{{ activeOnlineUsers.length }}</span>
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

                <!-- Conversation / Active List -->
                <div class="flex-1 overflow-y-auto chat-list px-3 pb-6">
                    <template v-if="activeTab === 'messages'">
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
                                <!-- Online Indicator -->
                                <div 
                                    v-if="isOnline(getParticipant(conv).id)" 
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
                    </template>

                    <!-- Active Tab: Only online users -->
                    <template v-else-if="activeTab === 'active'">
                        <div v-if="activeOnlineUsers.length > 0" class="space-y-1">
                            <div
                                v-for="user in activeOnlineUsers"
                                :key="user.id"
                                @click="startConversation(user.id)"
                                class="flex items-center px-4 py-3 rounded-2xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group"
                            >
                                <div class="relative flex-shrink-0">
                                    <div 
                                        class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-bold text-base shadow-lg"
                                        :style="{ background: `linear-gradient(135deg, ${getAvatarColor(user.name)}, ${getAvatarColor(user.name)}dd)` }"
                                    >
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full animate-pulse"></div>
                                </div>
                                <div class="ml-3 flex-grow">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">{{ user.name }}</p>
                                    <p class="text-[10px] text-green-500 font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block"></span> Online
                                    </p>
                                </div>
                                <i class="ri-chat-1-line text-gray-300 group-hover:text-green-500 transition-colors text-lg"></i>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-12 text-center px-6">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-4">
                                <i class="ri-user-unfollow-line text-3xl text-gray-300"></i>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada yang online</h4>
                            <p class="text-xs text-gray-500 mt-1">Semua pengguna sedang offline</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div :class="[
                'flex-1 flex-col overflow-hidden bg-gray-50 dark:bg-[#0a0a0c] transition-colors duration-300',
                'md:flex',
                selectedConversationId ? 'flex' : 'hidden'
            ]">
                <template v-if="selectedConversationId">
                    <!-- Chat Header -->
                    <div class="px-5 py-3 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800 flex items-center justify-between sticky top-0 z-10 transition-colors">
                        <div class="flex items-center">
                            <!-- Back Button (Mobile only) -->
                            <button 
                                @click="selectedConversationId = null" 
                                class="md:hidden mr-3 w-8 h-8 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all shadow-sm"
                            >
                                <i class="ri-arrow-left-line text-lg"></i>
                            </button>
                            <div class="relative flex-shrink-0 group cursor-pointer">
                                <div 
                                    class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg transition-all group-hover:rotate-6"
                                    :style="{ background: `linear-gradient(135deg, ${getAvatarColor(getParticipant(selectedConversation).name)}, ${getAvatarColor(getParticipant(selectedConversation).name)}dd)` }"
                                >
                                    {{ getParticipant(selectedConversation).name.charAt(0).toUpperCase() }}
                                </div>
                                <!-- Online/Offline Indicator -->
                                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 border-2 border-white dark:border-gray-900 rounded-full"
                                    :class="isOnline(getParticipant(selectedConversation).id) ? 'bg-green-500' : 'bg-gray-400'"
                                ></div>
                            </div>
                            <div class="ml-3">
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm tracking-tight leading-tight">{{ getParticipant(selectedConversation).name }}</h3>
                                <div class="flex items-center mt-0.5">
                                    <template v-if="isOnline(getParticipant(selectedConversation).id)">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 shadow-sm shadow-green-500/50 animate-pulse"></span>
                                        <p class="text-[10px] font-semibold text-green-500 uppercase tracking-widest">Online</p>
                                    </template>
                                    <template v-else>
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Offline</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Header Actions: Delete only -->
                        <div class="flex items-center">
                            <button 
                                @click="confirmDeleteConversation(selectedConversation)"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-500 transition-all"
                                title="Hapus percakapan"
                            >
                                <i class="ri-delete-bin-line text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Feed -->
                    <div ref="messageContainer" class="flex-grow overflow-y-auto px-5 py-5 space-y-3 scroll-smooth">
                        <div v-if="isLoadingMessages" class="flex justify-center items-center h-full">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
                        </div>
                        <template v-else>
                            <template v-for="(msg, index) in messages" :key="msg.id">
                                <!-- Message Received -->
                                <div v-if="msg.sender_id !== currentUser.id" class="flex items-end space-x-2 mb-2 animate-fade-in-up">
                                    <div class="w-7 h-7 rounded-xl flex-shrink-0 flex items-center justify-center text-white font-bold text-[10px] shadow-md transition-all"
                                         :class="[index < messages.length - 1 && messages[index+1].sender_id !== currentUser.id ? 'opacity-0' : 'opacity-100']"
                                         :style="{ background: `linear-gradient(135deg, ${getAvatarColor(msg.sender?.name || 'User')}, ${getAvatarColor(msg.sender?.name || 'User')}dd)` }">
                                        {{ (msg.sender?.name || 'U').charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="max-w-[75%] flex flex-col items-start">
                                        <!-- Image attachment -->
                                        <img v-if="msg.type === 'image' && msg.file_full_url" :src="msg.file_full_url" :alt="msg.file_name" class="max-w-[200px] rounded-xl shadow-sm mb-1 cursor-pointer" @click="openFullScreenImage(msg.file_full_url)" />
                                        <!-- File attachment -->
                                        <a v-else-if="msg.type === 'file' && msg.file_full_url" :href="msg.file_full_url" :download="msg.file_name" target="_blank" class="flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2 mb-1 hover:bg-gray-50 transition-colors">
                                            <i :class="[fileIconClass(msg.file_type), 'text-lg flex-shrink-0']"></i>
                                            <span class="text-xs text-gray-700 dark:text-gray-200 truncate max-w-[160px]">{{ msg.file_name }}</span>
                                            <i class="ri-download-line text-gray-400 text-sm"></i>
                                        </a>
                                        <!-- Text body -->
                                        <div v-if="msg.body" class="bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 px-3 py-2 rounded-2xl rounded-bl-none shadow-sm border border-gray-100 dark:border-gray-700/50 text-xs leading-relaxed transition-all hover:shadow-md">
                                            {{ msg.body }}
                                        </div>
                                        <span v-if="index === messages.length - 1 || messages[index+1].sender_id === currentUser.id" class="text-[9px] font-medium text-gray-400 dark:text-gray-500 ml-1 mt-1 uppercase tracking-tighter">{{ formatTime(msg.created_at) }}</span>
                                    </div>
                                </div>

                                <!-- Message Sent -->
                                <div v-else class="flex items-end justify-end space-x-2 mb-2 animate-fade-in-up group">
                                    <div class="max-w-[75%] flex flex-col items-end">
                                        <!-- Action buttons (edit/delete) - shown on hover for non-deleted messages -->
                                        <div v-if="!msg.is_deleted" class="flex items-center space-x-1 mb-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="startEditMessage(msg)" class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all" title="Edit pesan">
                                                <i class="ri-pencil-line text-xs"></i>
                                            </button>
                                            <button @click="confirmDeleteMessage(msg)" class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all" title="Hapus pesan">
                                                <i class="ri-delete-bin-line text-xs"></i>
                                            </button>
                                        </div>
                                        <!-- Image attachment -->
                                        <img v-if="msg.type === 'image' && msg.file_full_url && !msg.is_deleted" :src="msg.file_full_url" :alt="msg.file_name" class="max-w-[200px] rounded-xl shadow-md mb-1 cursor-pointer" @click="openFullScreenImage(msg.file_full_url)" />
                                        <!-- File attachment -->
                                        <a v-else-if="msg.type === 'file' && msg.file_full_url && !msg.is_deleted" :href="msg.file_full_url" :download="msg.file_name" target="_blank" class="flex items-center gap-2 bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-900/30 rounded-xl px-3 py-2 mb-1 hover:bg-green-100 transition-colors">
                                            <i :class="[fileIconClass(msg.file_type), 'text-lg flex-shrink-0']"></i>
                                            <span class="text-xs text-gray-700 dark:text-gray-200 truncate max-w-[160px]">{{ msg.file_name }}</span>
                                            <i class="ri-download-line text-gray-400 text-sm"></i>
                                        </a>
                                        <!-- Text body - Edit Mode -->
                                        <div v-if="editingMessageId === msg.id && !msg.is_deleted" class="w-full">
                                            <div class="flex items-center gap-2 bg-white dark:bg-gray-800 border border-green-300 dark:border-green-700 rounded-2xl px-3 py-1 shadow-sm">
                                                <input 
                                                    ref="editMessageInputRef"
                                                    v-model="editMessageText"
                                                    @keyup.enter="saveEditMessage(msg)"
                                                    @keyup.escape="cancelEditMessage"
                                                    type="text" 
                                                    class="flex-grow bg-transparent border-none focus:ring-0 focus:outline-none text-xs text-gray-700 dark:text-gray-200 py-1.5 outline-none shadow-none"
                                                >
                                                <button @click="saveEditMessage(msg)" class="text-green-500 hover:text-green-600 transition-colors" title="Simpan">
                                                    <i class="ri-check-line text-sm"></i>
                                                </button>
                                                <button @click="cancelEditMessage" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors" title="Batal">
                                                    <i class="ri-close-line text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Text body - Normal or Deleted -->
                                        <div v-else-if="msg.body" 
                                            :class="[
                                                'px-3 py-2 rounded-2xl rounded-br-none shadow-md text-xs leading-relaxed transition-all',
                                                msg.is_deleted 
                                                    ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 italic shadow-none border border-gray-200 dark:border-gray-600' 
                                                    : 'bg-gradient-to-br from-green-500 to-green-600 text-white shadow-green-500/10'
                                            ]">
                                            {{ msg.body }}
                                        </div>
                                        <!-- Edited indicator -->
                                        <div v-if="msg.is_edited && !msg.is_deleted" class="text-[8px] text-gray-400 dark:text-gray-500 italic mt-0.5">(diedit)</div>
                                        <!-- Time and status -->
                                        <div class="flex items-center mt-1 space-x-1.5">
                                            <span v-if="index === messages.length - 1 || messages[index+1].sender_id !== currentUser.id" class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">{{ formatTime(msg.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>

                    <!-- Message Input Area -->
                    <div :style="inputAreaStyle" class="bg-white dark:bg-gray-900 border-t border-gray-50 dark:border-gray-800 transition-colors">
                        <!-- File Preview -->  
                        <div v-if="selectedFile" class="px-4 pt-3 flex items-center gap-2">
                            <img v-if="filePreview" :src="filePreview" class="h-12 w-12 object-cover rounded-lg border border-gray-200" />
                            <div v-else class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
                                <i :class="[fileIconClass(selectedFile.type), 'text-base']"></i>
                                <span class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-[200px]">{{ selectedFile.name }}</span>
                            </div>
                            <button @click="clearFile" class="ml-auto text-gray-400 hover:text-red-500 transition-colors">
                                <i class="ri-close-line text-lg"></i>
                            </button>
                        </div>
                        <div class="flex items-center px-4 py-2">
                            <!-- File Input (hidden) -->
                            <input ref="fileInputRef" type="file" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip" @change="onFileSelected" />
                            <!-- Attach Button -->
                            <button @click="fileInputRef.click()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-green-500 transition-colors flex-shrink-0" title="Lampirkan file">
                                <i class="ri-attachment-2 text-base"></i>
                            </button>
                            <div class="flex-grow mx-2 bg-gray-50 dark:bg-gray-800/50 rounded-2xl px-3 py-1 flex items-center focus-within:bg-white dark:focus-within:bg-gray-800 transition-all">
                                <input 
                                    v-model="newMessage"
                                    @keyup.enter="handleSend"
                                    @focus="handleInputFocus"
                                    type="text" 
                                    placeholder="Tulis pesan..." 
                                    class="flex-grow bg-transparent border-none focus:ring-0 focus:outline-none text-xs text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-600 py-2 outline-none shadow-none"
                                >
                            </div>
                            <button 
                                @click="handleSend"
                                :disabled="!newMessage.trim() && !selectedFile"
                                :class="['w-8 h-8 rounded-xl flex items-center justify-center shadow-md transition-all transform active:scale-90 flex-shrink-0 outline-none focus:outline-none border-none', (newMessage.trim() || selectedFile) ? 'bg-green-500 hover:bg-green-600 text-white shadow-green-500/30' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed']"
                            >
                                <i class="ri-send-plane-2-fill text-sm"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <div v-else class="hidden md:flex flex-col items-center justify-center h-full text-center p-10">
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

        <!-- Delete Conversation Confirm Modal -->
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteConfirm" class="fixed inset-0 z-[110] flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteConfirm = false"></div>
                <div class="bg-white dark:bg-gray-900 w-full max-w-sm rounded-2xl shadow-2xl relative p-6 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center flex-shrink-0">
                            <i class="ri-delete-bin-line text-xl text-red-500"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Hapus Percakapan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Percakapan dengan <span class="font-semibold">{{ conversationToDelete ? getParticipant(conversationToDelete)?.name : '' }}</span> akan dihapus permanen.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button 
                            @click="showDeleteConfirm = false"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="deleteConversation"
                            class="px-4 py-2 text-xs font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors shadow-sm shadow-red-500/20"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Delete Message Confirm Modal -->
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteMessageConfirm" class="fixed inset-0 z-[110] flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteMessageConfirm = false"></div>
                <div class="bg-white dark:bg-gray-900 w-full max-w-sm rounded-2xl shadow-2xl relative p-6 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center flex-shrink-0">
                            <i class="ri-delete-bin-line text-xl text-red-500"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Hapus Pesan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Pesan ini akan dihapus. Pesan akan ditandai sebagai "Pesan ini telah dihapus".
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button 
                            @click="showDeleteMessageConfirm = false"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="deleteMessage"
                            class="px-4 py-2 text-xs font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors shadow-sm shadow-red-500/20"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

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
        <!-- Full Screen Image Modal -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="fullScreenImage" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/90 backdrop-blur-sm" @click="closeFullScreenImage">
                <!-- Close Button -->
                <button 
                    @click="closeFullScreenImage" 
                    class="absolute top-6 right-6 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all focus:outline-none"
                    title="Tutup (Esc)"
                >
                    <i class="ri-close-line text-2xl font-bold"></i>
                </button>
                
                <!-- Image Container -->
                <div class="max-w-[90vw] max-h-[90vh] overflow-hidden" @click.stop>
                    <img :src="fullScreenImage" class="w-full h-full object-contain select-none" />
                </div>
            </div>
        </Transition>
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
