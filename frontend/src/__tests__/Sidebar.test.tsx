import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import Sidebar from '../Admin/components/Sidebar';

describe('Sidebar accordion behavior', () => {
  it('opening one section closes the previously open one', async () => {
    const user = userEvent.setup();
    render(
      <MemoryRouter initialEntries={['/admin']}>
        <Sidebar onClose={() => {}} />
      </MemoryRouter>,
    );

    const ventas = screen.getByText('Ventas');
    const compras = screen.getByText('Compras y Proveedores');

    await user.click(ventas);
    expect(screen.getByText('Crear Venta Productos')).toBeDefined();

    await user.click(compras);
    expect(screen.getByText('Crear Compra')).toBeDefined();
    expect(screen.queryByText('Crear Venta Productos')).toBeNull();
  });

  it('clicking the open section again closes it', async () => {
    const user = userEvent.setup();
    render(
      <MemoryRouter initialEntries={['/admin']}>
        <Sidebar onClose={() => {}} />
      </MemoryRouter>,
    );

    const ventas = screen.getByText('Ventas');
    await user.click(ventas);
    expect(screen.getByText('Crear Venta Productos')).toBeDefined();

    await user.click(ventas);
    expect(screen.queryByText('Crear Venta Productos')).toBeNull();
  });

  it('auto-opens the section that contains the current path on mount', () => {
    render(
      <MemoryRouter initialEntries={['/admin/compras']}>
        <Sidebar onClose={() => {}} />
      </MemoryRouter>,
    );

    expect(screen.getByText('Crear Compra')).toBeDefined();
    expect(screen.queryByText('Crear Venta Productos')).toBeNull();
  });
});
