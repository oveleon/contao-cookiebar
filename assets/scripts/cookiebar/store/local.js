/**
 * Local storage cache for the Cookiebar
 */
export default class Storage {
    /**
     * @param {Object} settings
     */
    constructor(settings) {
        this.settings = settings;
        this.identifier = settings.token + '_' + settings.configId;

        if (null !== localStorage.getItem(this.identifier)) {
            return;
        }

        localStorage.setItem(
            this.identifier,
            JSON.stringify({
                configId: settings.configId,
                pageId: settings.pageId,
                version: -1,
                saved: -1,
                cookies: [],
            }),
        );
    }

    /**
     * @returns {Object}
     */
    get() {
        return JSON.parse(localStorage.getItem(this.identifier));
    }

    /**
     * @param {Object} storage
     */
    set(storage) {
        localStorage.setItem(this.identifier, JSON.stringify(storage));
    }

    /**
     * @param {int|string} cookie
     * @returns {boolean}
     */
    isset(cookie) {
        const cookies = this.get().cookies;

        if (null === cookies) {
            return false;
        }

        if (typeof cookie === 'number') {
            return cookies.includes(cookie);
        }

        for (const { id, token } of Object.values(this.settings.cookies)) {
            if (token && token.includes(cookie)) {
                return cookies.includes(id);
            }
        }

        return cookies.includes(cookie.toString());
    }
}
