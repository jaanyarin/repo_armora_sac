import { create } from 'zustand';
import { authApi } from '../api/endpoints';
import type { AuthState, Usuario } from '../types';

interface AuthStore extends AuthState {
  loadUser: () => Promise<void>;
}

export const useAuthStore = create<AuthStore>((set, get) => ({
  user: null,
  token: localStorage.getItem('auth_token'),
  isAuthenticated: !!localStorage.getItem('auth_token'),

  login: async (login: string, password: string) => {
    const response = await authApi.login(login, password);
    const { token, user } = response.data;
    localStorage.setItem('auth_token', token);
    set({ user, token, isAuthenticated: true });
  },

  logout: async () => {
    try {
      await authApi.logout();
    } catch {
      // ignore logout errors
    }
    localStorage.removeItem('auth_token');
    set({ user: null, token: null, isAuthenticated: false });
    window.location.href = '/login';
  },

  loadUser: async () => {
    const { token, isAuthenticated } = get();
    if (!token || !isAuthenticated) return;
    try {
      const response = await authApi.me();
      set({ user: response.data.user as Usuario });
    } catch {
      localStorage.removeItem('auth_token');
      set({ user: null, token: null, isAuthenticated: false });
    }
  },
}));
