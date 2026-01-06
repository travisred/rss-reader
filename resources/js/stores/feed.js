import { defineStore } from 'pinia';
import axios from 'axios';

export const useFeedStore = defineStore('feed', {
    state: () => ({
        feeds: [],
        currentFeed: null,
        items: [],
        currentItem: null,
        loading: false,
        itemsLoading: false,
        currentPage: 1,
        lastPage: 1,
        filter: 'all', // all, unread, starred
    }),

    getters: {
        unreadCount: (state) => {
            return (state.feeds || []).reduce((sum, feed) => sum + (feed.unread_count || 0), 0);
        },

        feedsByFolder: (state) => {
            const grouped = {};
            (state.feeds || []).forEach(feed => {
                const folder = feed.folder || 'Uncategorized';
                if (!grouped[folder]) {
                    grouped[folder] = [];
                }
                grouped[folder].push(feed);
            });
            return grouped;
        },
    },

    actions: {
        async fetchFeeds() {
            this.loading = true;
            try {
                const response = await axios.get('/feeds');
                this.feeds = response.data.feeds || [];
            } catch (error) {
                console.error('Error fetching feeds:', error);
                this.feeds = [];
            } finally {
                this.loading = false;
            }
        },

        async addFeed(url, folder = null) {
            const response = await axios.post('/feeds', { url, folder });
            await this.fetchFeeds();
            return response.data;
        },

        async deleteFeed(feedId) {
            await axios.delete(`/feeds/${feedId}`);
            await this.fetchFeeds();
        },

        async refreshFeed(feedId) {
            await axios.post(`/feeds/${feedId}/refresh`);
            await this.fetchFeeds();
            if (this.currentFeed?.id === feedId) {
                await this.fetchItems({ feed_id: feedId });
            }
        },

        async fetchItems(params = {}) {
            this.itemsLoading = true;
            try {
                const response = await axios.get('/items', { params });
                this.items = response.data.data || [];
                this.currentPage = response.data.current_page || 1;
                this.lastPage = response.data.last_page || 1;
            } catch (error) {
                console.error('Error fetching items:', error);
                this.items = [];
            } finally {
                this.itemsLoading = false;
            }
        },

        async fetchItem(itemId) {
            const response = await axios.get(`/items/${itemId}`);
            this.currentItem = response.data;
            // Update item in list
            const index = this.items.findIndex(item => item.id === itemId);
            if (index !== -1) {
                this.items[index] = response.data;
            }
        },

        async markAsRead(itemId) {
            // Optimistically update UI immediately
            const item = this.items.find(i => i.id === itemId);
            if (item && item.user_items && item.user_items[0]) {
                item.user_items[0].is_read = true;
            }
            if (this.currentItem?.id === itemId) {
                if (this.currentItem.user_items && this.currentItem.user_items[0]) {
                    this.currentItem.user_items[0].is_read = true;
                }
            }

            // Then update backend
            await axios.post(`/items/${itemId}/read`);
            await this.fetchFeeds(); // Update unread counts
        },

        async markAsUnread(itemId) {
            await axios.post(`/items/${itemId}/unread`);
            const item = this.items.find(i => i.id === itemId);
            if (item && item.user_items && item.user_items[0]) {
                item.user_items[0].is_read = false;
            }
            await this.fetchFeeds();
        },

        async toggleStar(itemId) {
            const response = await axios.post(`/items/${itemId}/star`);
            const item = this.items.find(i => i.id === itemId);
            if (item && item.user_items && item.user_items[0]) {
                item.user_items[0].is_starred = response.data.is_starred;
            }
        },

        async markAllAsRead(feedId = null) {
            await axios.post('/items/mark-all-read', { feed_id: feedId });
            await this.fetchFeeds();
            await this.fetchItems(this.getFilterParams());
        },

        setFilter(filter) {
            this.filter = filter;
            this.fetchItems(this.getFilterParams());
        },

        setCurrentFeed(feed) {
            this.currentFeed = feed;
            this.fetchItems({ feed_id: feed?.id, ...this.getFilterParams() });
        },

        getFilterParams() {
            const params = {};
            if (this.filter === 'unread') {
                params.is_read = false;
            } else if (this.filter === 'starred') {
                params.is_starred = true;
            }
            return params;
        },
    },
});
