import { describe, it, expect, beforeEach } from 'vitest';
import { useAuthStore } from '../shared/hooks/useAuth';
import type { Usuario } from '../shared/types';

describe('useAuthStore', () => {
  beforeEach(() => {
    localStorage.clear();
    useAuthStore.setState({ user: null, token: null, isAuthenticated: false });
  });

  it('starts with unauthenticated state when no token', () => {
    const state = useAuthStore.getState();
    expect(state.isAuthenticated).toBe(false);
    expect(state.user).toBeNull();
    expect(state.token).toBeNull();
  });

  it('starts authenticated when token exists in localStorage', () => {
    localStorage.setItem('auth_token', 'test-token');
    useAuthStore.setState({
      token: 'test-token',
      isAuthenticated: true,
    });
    const state = useAuthStore.getState();
    expect(state.isAuthenticated).toBe(true);
    expect(state.token).toBe('test-token');
  });

  it('sets authenticated state on login', async () => {
    const mockUser: Usuario = {
      id: 1,
      codigo: 'USR001',
      username: 'testuser',
      name: 'Test User',
      nombre_completo: 'Test User',
      email: 'test@example.com',
      dni: null,
      ruc: null,
      telefono: null,
      activo: true,
      ultimo_acceso: null,
      roles: [],
      permissions: [],
      created_at: '2026-01-01T00:00:00Z',
    };

    useAuthStore.setState({
      user: mockUser,
      token: 'new-token',
      isAuthenticated: true,
    });

    const state = useAuthStore.getState();
    expect(state.isAuthenticated).toBe(true);
    expect(state.user).toEqual(mockUser);
    expect(state.token).toBe('new-token');
  });

  it('clears state on logout', async () => {
    const mockUser: Usuario = {
      id: 1,
      codigo: 'USR001',
      username: 'test',
      name: 'Test',
      nombre_completo: 'Test',
      email: 'test@test.com',
      dni: null,
      ruc: null,
      telefono: null,
      activo: true,
      ultimo_acceso: null,
      roles: [],
      permissions: [],
      created_at: '2026-01-01T00:00:00Z',
    };

    useAuthStore.setState({
      user: mockUser,
      token: 'token',
      isAuthenticated: true,
    });

    await useAuthStore.getState().logout();

    const state = useAuthStore.getState();
    expect(state.isAuthenticated).toBe(false);
    expect(state.user).toBeNull();
    expect(state.token).toBeNull();
  });
});
