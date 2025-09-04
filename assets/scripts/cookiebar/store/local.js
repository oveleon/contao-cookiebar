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

        this.localStorage = localStorage.getItem(this.identifier);

        if (null === this.localStorage) {
            this.localStorage = JSON.stringify({
                configId: settings.configId,
                pageId: settings.pageId,
                version: -1,
                saved: -1,
                cookies: [],
            });
        }
    }

    /**
     * @returns {*}
     */
    get() {
        return JSON.parse(this.localStorage);
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

        /*for (let id in this.settings.cookies) {
            if (null !== this.settings.cookies[id].token && this.settings.cookies[id].token.indexOf(cookie) !== -1) {
                return cookies.indexOf(this.settings.cookies[id].id) !== -1;
            }
        }*/

        for (const { id, token } of Object.values(this.settings.cookies)) {
            if (token && token.includes(cookie)) {
                return cookies.includes(id);
            }
        }

        return cookies.includes(cookie.toString());
    }
}
