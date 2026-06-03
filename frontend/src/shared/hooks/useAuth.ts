import { create } from 'zustand';
import { authApi } from '../api/endpoints';
import type { AuthState } from '../types';

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  token: localStorage.getItem('auth_token'),
  isAuthenticated: !!localStorage.getItem('auth_token'),

  login: async (username: string, password: string) => {
    const response = await authApi.login(username, password);
    const { token, user } = response.data;
    localStorage.setItem('auth_token', token);
    set({ user, token, isAuthenticated: true });
  },

  logout: () => {
    localStorage.removeItem('auth_token');
    set({ user: null, token: null, isAuthenticated: false });
    window.location.href = '/login';
  },
}));
