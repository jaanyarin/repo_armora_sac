import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { CartItem } from '../types';

interface CartStore {
  items: CartItem[];
  add: (item: CartItem) => void;
  remove: (productoId: number) => void;
  updateCantidad: (productoId: number, cantidad: number) => void;
  clear: () => void;
  total: () => number;
  itemCount: () => number;
}

export const useCartStore = create<CartStore>()(
  persist(
    (set, get) => ({
      items: [],
      add: (newItem) => set((state) => {
        const existing = state.items.find(i => i.producto.id === newItem.producto.id);
        if (existing) {
          return {
            items: state.items.map(i =>
              i.producto.id === newItem.producto.id
                ? { ...i, cantidad: i.cantidad + newItem.cantidad }
                : i
            ),
          };
        }
        return { items: [...state.items, newItem] };
      }),
      remove: (productoId) => set((state) => ({
        items: state.items.filter(i => i.producto.id !== productoId),
      })),
      updateCantidad: (productoId, cantidad) => set((state) => ({
        items: state.items
          .map(i => i.producto.id === productoId ? { ...i, cantidad } : i)
          .filter(i => i.cantidad > 0),
      })),
      clear: () => set({ items: [] }),
      total: () => get().items.reduce((sum, i) => sum + i.cantidad * i.precio_unitario, 0),
      itemCount: () => get().items.reduce((sum, i) => sum + i.cantidad, 0),
    }),
    { name: 'armora-portal-cart' },
  ),
);
