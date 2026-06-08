// Helper mapper mapping Starter Kit route helpers to Ziggy route function
declare const route: any;

interface MockRoute {
    url: string;
    method: string;
    toString(): string;
}

const safeRoute = (name: string, params?: any): string => {
    if (typeof route !== 'undefined') {
        try {
            return route(name, params);
        } catch (e) {
            return '';
        }
    }
    return '';
};

export const home = (): MockRoute => {
    return {
        url: '/',
        method: 'get',
        toString() { return '/'; }
    };
};

export const login = (): MockRoute => {
    return {
        url: safeRoute('login'),
        method: 'get',
        toString() { return safeRoute('login'); }
    };
};

export const register = (): MockRoute => {
    return {
        url: safeRoute('register'),
        method: 'get',
        toString() { return safeRoute('register'); }
    };
};

export const logout = (): MockRoute => {
    return {
        url: safeRoute('logout'),
        method: 'post',
        toString() { return safeRoute('logout'); }
    };
};

export const dashboard = (): MockRoute => {
    return {
        url: safeRoute('dashboard'),
        method: 'get',
        toString() { return safeRoute('dashboard'); }
    };
};
