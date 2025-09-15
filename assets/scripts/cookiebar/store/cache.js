/**
 * Verifies a cookie and saves the option into the cookiebar cache
 */
export default class Cache {
    constructor() {
        this.cache = {};
    }

    /**
     * @param {Object} cookie
     * @param {*} index
     * @param {string} type
     */
    verify(cookie, index, type) {
        const token = `${cookie.id}${index}`;
        const list = (this.cache[type] ??= []);

        if (list.includes(token)) {
            return true;
        }

        list.push(token);
        return false;
    }
}
