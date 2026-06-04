/* eslint-disable @typescript-eslint/no-explicit-any */
declare module '@tanstack/react-query' {
  export const QueryClient: any;
  export const QueryClientProvider: any;
  export const useQuery: any;
  export const useMutation: any;
  export const useQueryClient: any;
}

declare module '@hookform/resolvers/zod' {
  export const zodResolver: any;
}

declare module '@hookform/resolvers/zod/dist/zod.module.js' {
  export const zodResolver: any;
}

declare module 'react-hook-form' {
  import type { ReactNode } from 'react';
  export function useForm<T = any>(opts?: { resolver?: any; defaultValues?: Partial<T> }): {
    control: any;
    handleSubmit: (fn: (data: T) => void) => (e?: any) => Promise<void>;
    reset: (values?: Partial<T>) => void;
    formState: { errors: Record<string, { message?: string }>; isSubmitting?: boolean };
  };
  export function Controller(props: {
    control?: any;
    name: string;
    rules?: any;
    defaultValue?: any;
    render: (props: { field: any; fieldState?: any; formState?: any }) => ReactNode;
  }): ReactNode;
  export type Resolver = any;
}
