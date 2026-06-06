/* eslint-disable @typescript-eslint/no-explicit-any */
declare module '@tanstack/react-query' {
  export const QueryClient: any;
  export const QueryClientProvider: any;
  export const useQuery: <T = any>(opts: any) => { data: T | undefined; isLoading: boolean; isError: boolean; error: any };
  export const useMutation: <TData = any, TError = any, TVariables = any>(opts: any) => {
    mutate: (vars?: TVariables) => void;
    mutateAsync: (vars?: TVariables) => Promise<TData>;
    isPending: boolean;
    isError: boolean;
    error: TError | null;
    data: TData | undefined;
  };
  export const useQueryClient: () => any;
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
    watch: (name?: keyof T | (keyof T)[]) => any;
    setValue: (name: keyof T, value: any, options?: { shouldDirty?: boolean; shouldValidate?: boolean }) => void;
    getValues: (name?: keyof T) => any;
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
