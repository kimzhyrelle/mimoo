import type { Auth } from '@/types/auth';

export interface PageProps {
    auth?: Auth;
    [key: string]: any;
}

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
    
    export interface PageProps {
        auth?: Auth;
        [key: string]: any;
    }
}
