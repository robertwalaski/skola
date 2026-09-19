import { usePage } from '@inertiajs/vue3';

/**
 * Reads the `translations` prop shared by HandleInertiaRequests
 * (app/Http/Middleware/HandleInertiaRequests.php, source: lang/{locale}.json).
 */
export function useTrans() {
    const page = usePage();

    function t(key, fallback = key) {
        return page.props.translations?.[key] ?? fallback;
    }

    return { t };
}
