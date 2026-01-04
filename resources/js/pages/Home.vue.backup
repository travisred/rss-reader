<template>
  <div class="h-screen flex flex-col bg-white">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
      <div class="flex items-center space-x-2 md:space-x-4">
        <!-- Mobile menu toggle -->
        <button
          @click="mobileView = 'sidebar'"
          class="md:hidden text-gray-600 hover:text-gray-900 p-1"
          v-if="mobileView !== 'sidebar'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
        <!-- Back button for mobile -->
        <button
          @click="handleMobileBack"
          class="md:hidden text-gray-600 hover:text-gray-900 p-1"
          v-if="mobileView !== 'sidebar'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <h1 class="text-xl md:text-2xl font-bold text-google-blue">RSS Reader</h1>
        <button
          @click="showAddFeedModal = true"
          class="hidden md:inline-block bg-google-blue text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm"
        >
          + Add Feed
        </button>
      </div>
      <div class="flex items-center space-x-2 md:space-x-4">
        <button
          @click="showAddFeedModal = true"
          class="md:hidden text-google-blue hover:text-blue-700 text-2xl"
          title="Add feed"
        >
          +
        </button>
        <span class="hidden md:inline text-sm text-gray-600">{{ authStore.user?.name }}</span>
        <button
          @click="handleLogout"
          class="text-sm text-gray-600 hover:text-gray-900"
        >
          Logout
        </button>
      </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <aside 
        :class="[
          'w-full md:w-64 bg-gray-50 border-r border-gray-200 overflow-y-auto scrollbar-thin',
          mobileView === 'sidebar' ? 'block' : 'hidden md:block'
        ]"
      >
        <nav class="p-4 space-y-2">
          <!-- Special Views -->
          <button
            @click="selectView('all')"
            :class="[
              'w-full text-left px-3 py-2 rounded-lg text-sm',
              currentView === 'all' ? 'bg-blue-100 text-google-blue font-medium' : 'hover:bg-gray-100'
            ]"
          >
            All Items
            <span v-if="feedStore.unreadCount > 0" class="float-right text-xs bg-google-blue text-white px-2 py-1 rounded-full">
              {{ feedStore.unreadCount }}
            </span>
          </button>

          <button
            @click="selectView('starred')"
            :class="[
              'w-full text-left px-3 py-2 rounded-lg text-sm',
              currentView === 'starred' ? 'bg-blue-100 text-google-blue font-medium' : 'hover:bg-gray-100'
            ]"
          >
            ⭐ Starred Items
          </button>

          <div class="border-t border-gray-200 my-4"></div>

          <!-- Feeds by Folder -->
          <div v-for="(feeds, folder) in feedStore.feedsByFolder" :key="folder" class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase px-3 mb-2">
              {{ folder }}
            </div>
            <button
              v-for="feed in feeds"
              :key="feed.id"
              @click="selectFeed(feed)"
              :class="[
                'w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between',
                feedStore.currentFeed?.id === feed.id ? 'bg-blue-100 text-google-blue font-medium' : 'hover:bg-gray-100'
              ]"
            >
              <span class="truncate">{{ feed.title || 'Untitled' }}</span>
              <span v-if="feed.unread_count > 0" class="text-xs bg-gray-300 text-gray-700 px-2 py-1 rounded-full ml-2">
                {{ feed.unread_count }}
              </span>
            </button>
          </div>
        </nav>
      </aside>

      <!-- Items List -->
      <div 
        :class="[
          'w-full md:w-96 bg-white border-r border-gray-200 flex flex-col',
          mobileView === 'items' ? 'block' : 'hidden md:flex'
        ]"
      >
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">
            {{ getViewTitle() }}
          </h2>
          <div class="flex items-center space-x-2">
            <button
              v-if="feedStore.currentFeed"
              @click="refreshCurrentFeed"
              class="text-sm text-gray-600 hover:text-gray-900"
              title="Refresh feed"
            >
              🔄
            </button>
            <button
              @click="feedStore.markAllAsRead(feedStore.currentFeed?.id)"
              class="text-sm text-gray-600 hover:text-gray-900"
            >
              Mark all as read
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-thin">
          <div v-if="feedStore.itemsLoading" class="p-8 text-center text-gray-500">
            Loading...
          </div>
          <div v-else-if="feedStore.items.length === 0" class="p-8 text-center text-gray-500">
            No items to display
          </div>
          <div v-else>
            <button
              v-for="item in feedStore.items"
              :key="item.id"
              @click="selectItem(item)"
              :class="[
                'w-full text-left p-4 border-b border-gray-100 hover:bg-gray-50',
                feedStore.currentItem?.id === item.id ? 'bg-blue-50' : '',
                isItemRead(item) ? 'opacity-60' : 'font-medium'
              ]"
            >
              <div class="text-sm text-google-blue mb-1">
                {{ item.feed.title }}
              </div>
              <div class="text-base mb-1">
                {{ item.title }}
              </div>
              <div class="text-xs text-gray-500">
                {{ formatDate(item.published_at) }}
                <span v-if="isItemStarred(item)" class="ml-2">⭐</span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Content Pane -->
      <main 
        :class="[
          'flex-1 bg-white overflow-y-auto scrollbar-thin',
          mobileView === 'content' ? 'block' : 'hidden md:block'
        ]"
      >
        <div v-if="!feedStore.currentItem" class="h-full flex items-center justify-center text-gray-500 p-4">
          Select an item to read
        </div>
        <article v-else class="max-w-4xl mx-auto p-4 md:p-8">
          <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
              {{ feedStore.currentItem.title }}
            </h1>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between text-sm text-gray-600 mb-4 gap-3">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-google-blue">{{ feedStore.currentItem.feed.title }}</span>
                <span>•</span>
                <span>{{ formatDate(feedStore.currentItem.published_at) }}</span>
                <span v-if="feedStore.currentItem.author">•</span>
                <span v-if="feedStore.currentItem.author">{{ feedStore.currentItem.author }}</span>
              </div>
              <div class="flex items-center space-x-3 flex-wrap gap-2">
                <button
                  @click="toggleStar"
                  class="text-xl hover:scale-110 transition-transform"
                  :title="isItemStarred(feedStore.currentItem) ? 'Unstar' : 'Star'"
                >
                  {{ isItemStarred(feedStore.currentItem) ? '⭐' : '☆' }}
                </button>
                <button
                  @click="toggleRead"
                  class="text-sm text-gray-600 hover:text-gray-900"
                >
                  {{ isItemRead(feedStore.currentItem) ? 'Mark unread' : 'Mark read' }}
                </button>
                <a
                  :href="feedStore.currentItem.url"
                  target="_blank"
                  class="text-sm text-google-blue hover:underline"
                >
                  Open original →
                </a>
              </div>
            </div>
          </div>

          <div class="feed-item-content prose prose-lg max-w-none" v-html="feedStore.currentItem.content || feedStore.currentItem.description"></div>
        </article>
      </main>
    </div>

    <!-- Add Feed Modal -->
    <div
      v-if="showAddFeedModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showAddFeedModal = false"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-bold mb-4">Add New Feed</h2>
        <form @submit.prevent="handleAddFeed" class="space-y-4">
          <div v-if="addFeedError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            {{ addFeedError }}
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Feed URL
            </label>
            <input
              v-model="newFeedUrl"
              type="url"
              required
              placeholder="https://example.com/feed.xml"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-google-blue focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Folder (optional)
            </label>
            <input
              v-model="newFeedFolder"
              type="text"
              placeholder="Technology"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-google-blue focus:border-transparent"
            />
          </div>
          <div class="flex space-x-3">
            <button
              type="submit"
              :disabled="addingFeed"
              class="flex-1 bg-google-blue text-white py-2 px-4 rounded-lg hover:bg-blue-600 disabled:opacity-50"
            >
              {{ addingFeed ? 'Adding...' : 'Add Feed' }}
            </button>
            <button
              type="button"
              @click="showAddFeedModal = false"
              class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useFeedStore } from '../stores/feed';

const mobileView = ref('sidebar'); // sidebar, items, content
const router = useRouter();
const authStore = useAuthStore();
const feedStore = useFeedStore();

const currentView = ref('all');
const showAddFeedModal = ref(false);
const newFeedUrl = ref('');
const newFeedFolder = ref('');
const addingFeed = ref(false);
const addFeedError = ref('');

onMounted(async () => {
  await feedStore.fetchFeeds();
  await feedStore.fetchItems();
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const selectView = (view) => {
  currentView.value = view;
  feedStore.currentFeed = null;
  if (view === 'all') {
    feedStore.fetchItems();
  } else if (view === 'starred') {
    feedStore.fetchItems({ is_starred: true });
  // On mobile, show items list after selecting view
  mobileView.value = 'items';
};

const selectFeed = (feed) => {
  currentView.value = 'feed';
  feedStore.setCurrentFeed(feed);
  // On mobile, show items list after selecting feed
  mobileView.value = 'items';
};

const selectItem = async (item) => {
  await feedStore.fetchItem(item.id);
  // Automatically mark as read when viewing
  await feedStore.markAsRead(item.id);
  // On mobile, show content after selecting item
  mobileView.value = 'content';
};

const handleMobileBack = () => {
  if (mobileView.value === 'content') {
    mobileView.value = 'items';
  } else if (mobileView.value === 'items') {
    mobileView.value = 'sidebar';
  }
};

const refreshCurrentFeed = async () => {
  if (feedStore.currentFeed) {
    await feedStore.refreshFeed(feedStore.currentFeed.id);
  }
};

const toggleRead = async () => {
  if (!feedStore.currentItem) return;
  if (isItemRead(feedStore.currentItem)) {
    await feedStore.markAsUnread(feedStore.currentItem.id);
  } else {
    await feedStore.markAsRead(feedStore.currentItem.id);
  }
};

const toggleStar = async () => {
  if (!feedStore.currentItem) return;
  await feedStore.toggleStar(feedStore.currentItem.id);
};

const isItemRead = (item) => {
  return item.user_items && item.user_items[0]?.is_read;
};

const isItemStarred = (item) => {
  return item.user_items && item.user_items[0]?.is_starred;
};

const getViewTitle = () => {
  if (feedStore.currentFeed) {
    return feedStore.currentFeed.title;
  }
  if (currentView.value === 'starred') {
    return 'Starred Items';
  }
  return 'All Items';
};

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  const now = new Date();
  const diffMs = now - d;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 60) {
    return `${diffMins}m ago`;
  } else if (diffHours < 24) {
    return `${diffHours}h ago`;
  } else if (diffDays < 7) {
    return `${diffDays}d ago`;
  } else {
    return d.toLocaleDateString();
  }
};

const handleAddFeed = async () => {
  addingFeed.value = true;
  addFeedError.value = '';

  try {
    await feedStore.addFeed(newFeedUrl.value, newFeedFolder.value || null);
    showAddFeedModal.value = false;
    newFeedUrl.value = '';
    newFeedFolder.value = '';
    // Ensure feeds are refreshed
    await feedStore.fetchFeeds();
  } catch (err) {
    addFeedError.value = err.response?.data?.error || 'Failed to add feed';
  } finally {
    addingFeed.value = false;
  }
};
</script>
