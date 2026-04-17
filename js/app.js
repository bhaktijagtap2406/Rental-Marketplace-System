/**
 * RentSmart — Global App Utilities
 * Session-based auth (PHP sessions via cookies, same-origin).
 * No localStorage tokens needed — the server tracks the session.
 */

const App = {

    /**
     * Fetch from the PHP API. All requests are same-origin so session
     * cookies are automatically included by the browser.
     *
     * Returns { data, error } so callers never need try/catch.
     */
    async api(endpoint, options = {}) {
        const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
        // Remove Content-Type for FormData (browser sets it with boundary)
        if (options.body instanceof FormData) delete headers['Content-Type'];

        try {
            const resp = await fetch('/api' + endpoint, {
                credentials: 'same-origin',   // sends session cookie
                ...options,
                headers,
            });

            let data;
            const ct = resp.headers.get('Content-Type') || '';
            data = ct.includes('application/json') ? await resp.json() : await resp.text();

            if (!resp.ok) {
                const msg = (data && data.error) ? data.error : `HTTP ${resp.status}`;
                return { data: null, error: msg };
            }
            return { data, error: null };
        } catch (err) {
            return { data: null, error: err.message || 'Network error' };
        }
    },

    /**
     * Show a toast notification.
     * @param {string} message
     * @param {'success'|'error'} type
     */
    toast(message, type = 'success') {
        // Remove any existing toast
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();

        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px;flex-shrink:0;">
                ${type === 'success'
                    ? '<polyline points="20 6 9 17 4 12"/>'
                    : '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'}
            </svg>
            <span>${message}</span>`;
        document.body.appendChild(t);

        // Auto-remove after 3 s
        setTimeout(() => {
            t.classList.add('fade-out');
            t.addEventListener('animationend', () => t.remove(), { once: true });
        }, 3000);
    },
};

// Expose globally
window.App = App;
