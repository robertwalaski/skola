import { usePage } from '@inertiajs/vue3';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Reports a finished exercise to POST /attempts (see AttemptController) so a
 * logged-in user's points are counted server-side. Guests keep playing with
 * their existing localStorage-only scoring - nothing is sent for them.
 */
export function useAttempts() {
    const page = usePage();

    async function record(course, section, exerciseKey, correct) {
        if (!page.props.auth.user) return;
        try {
            await fetch('/attempts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ course, section, exercise_key: exerciseKey, correct }),
            });
        } catch {
            // best-effort - a dropped attempt costs a few points, not worth surfacing to the player
        }
    }

    return { record };
}
