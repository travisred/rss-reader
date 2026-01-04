import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        token: localStorage.getItem('token') || null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(email, password) {
            const response = await axios.post('/login', { email, password });
            this.setAuth(response.data);
        },

        async register(name, email, password, password_confirmation) {
            const response = await axios.post('/register', {
                name,
                email,
                password,
                password_confirmation,
            });
            this.setAuth(response.data);
        },

        async logout() {
            try {
                await axios.post('/logout');
            } catch (error) {
                console.error('Logout error:', error);
            }
            this.clearAuth();
        },

        async fetchUser() {
            const response = await axios.get('/me');
            this.user = response.data.user;
            localStorage.setItem('user', JSON.stringify(this.user));
        },

        setAuth(data) {
            this.token = data.token;
            this.user = data.user;
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;
        },

        clearAuth() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
        },
    },
});
