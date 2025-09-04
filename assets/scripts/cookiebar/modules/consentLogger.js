import { serialize } from '../lib/serialize';

/**
 * Logs the consent within tl_cookie_log
 */
export default class ConsentLogger {
    /**
     * @param {Object} settings
     * @param {import('../store/local.js').Storage} storage
     */
    constructor(settings, storage) {
        this.settings = settings;
        this.storage = storage;
    }

    log() {
        const params = serialize({
            referrer: window.location.pathname,
            configId: this.settings.configId,
            pageId: this.settings.pageId,
            cookies: this.storage.get()?.cookies,
        });

        fetch(`/cookiebar/log?${params}`, { method: 'GET' });
    }
}
