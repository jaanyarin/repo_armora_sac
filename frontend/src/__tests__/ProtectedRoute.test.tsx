import { describe, it, expect, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import { MemoryRouter, Routes, Route } from 'react-router-dom';
import ProtectedRoute from '../shared/components/ProtectedRoute';
import { useAuthStore } from '../shared/hooks/useAuth';
import type { Usuario } from '../shared/types';

function TestPage() {
  return <div>Protected Content</div>;
}

function LoginPage() {
  return <div>Login Page</div>;
}

describe('ProtectedRoute', () => {
  beforeEach(() => {
    useAuthStore.setState({ user: null, token: null, isAuthenticated: false });
  });

  it('renders children when authenticated', () => {
    useAuthStore.setState({
      user: { id: 1, codigo: 'USR001', username: 'test', name: 'Test', nombre_completo: 'Test', email: 'test@test.com', dni: null, ruc: null, telefono: null, activo: true, ultimo_acceso: null, roles: [], permissions: [], created_at: '2026-01-01T00:00:00Z' } satisfies Usuario,
      token: 'token',
      isAuthenticated: true,
    });

    render(
      <MemoryRouter initialEntries={['/admin']}>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/admin" element={<ProtectedRoute />}>
            <Route index element={<TestPage />} />
          </Route>
        </Routes>
      </MemoryRouter>,
    );

    expect(screen.getByText('Protected Content')).toBeDefined();
    expect(screen.queryByText('Login Page')).toBeNull();
  });

  it('redirects to login when not authenticated', () => {
    render(
      <MemoryRouter initialEntries={['/admin']}>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/admin" element={<ProtectedRoute />}>
            <Route index element={<TestPage />} />
          </Route>
        </Routes>
      </MemoryRouter>,
    );

    expect(screen.getByText('Login Page')).toBeDefined();
    expect(screen.queryByText('Protected Content')).toBeNull();
  });
});
