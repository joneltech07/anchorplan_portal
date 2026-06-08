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

export const edit = (): MockRoute => {
    return {
        url: safeRoute('profile.edit'),
        method: 'get',
        toString() { return safeRoute('profile.edit'); }
    };
};
