/**
 * Adds the current session CSRF token to same-origin state-changing fetches.
 * This keeps legacy admin modules compatible with centralized CSRF middleware.
 */
(() => {
    'use strict';

    if (window.__veloxCsrfFetchInstalled) return;
    window.__veloxCsrfFetchInstalled = true;

    const nativeFetch = window.fetch.bind(window);
    const safeMethods = new Set(['GET', 'HEAD', 'OPTIONS']);

    window.fetch = (input, init) => {
        const options = init ? { ...init } : {};
        const requestUrl = typeof input === 'string'
            ? input
            : (input && input.url ? input.url : window.location.href);
        const requestMethod = String(
            options.method || (input && input.method) || 'GET'
        ).toUpperCase();

        let url;
        try {
            url = new URL(requestUrl, window.location.href);
        } catch (error) {
            return nativeFetch(input, init);
        }

        if (url.origin === window.location.origin && !safeMethods.has(requestMethod)) {
            const inheritedHeaders = input instanceof Request ? input.headers : undefined;
            const headers = new Headers(options.headers || inheritedHeaders || {});
            const token = window.CSRF_TOKEN ||
                document.querySelector('meta[name="csrf-token"]')?.content ||
                '';

            if (token) {
                headers.set('X-CSRF-Token', token);
            }

            options.headers = headers;
            options.credentials = options.credentials || 'same-origin';
        }

        return nativeFetch(input, options);
    };
})();
