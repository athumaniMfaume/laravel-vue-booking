// src/stores/auth.js
import { defineStore } from 'pinia';
import api from '../utils/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || '',
    redirect_to: null,
  }),

  actions: {
    async login(credentials) {
      const response = await api.post('/login', credentials);

      this.token = response.data.access_token; // Make sure backend sends access_token here
      this.user = response.data.user;
      this.redirect_to = response.data.redirect_to;

      localStorage.setItem('token', this.token);
      // Do NOT set api.defaults.headers.common['Authorization'] here - interceptor will handle it
    },

    logout() {
      this.user = null;
      this.token = '';
      this.redirect_to = null;
      localStorage.removeItem('token');
      // No need to delete api.defaults.headers.common['Authorization'], interceptor reads localStorage
    },

    async fetchUser() {
      if (!this.token) return;

      try {
        // No need to set Authorization header manually, interceptor handles it
        const response = await api.get('/user');
        this.user = response.data;
      } catch {
        this.logout();
      }
    },
  },
});
