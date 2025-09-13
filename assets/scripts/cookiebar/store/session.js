/**
 * Session storage for the Cookiebar
 */
export default class Session {
    #key = 'ccb_contao'
    #dismissed = '_dismissed';

    setDismissed() {
        sessionStorage.setItem(this.#key + this.#dismissed, 'true')
    }

    isDismissed() {
        return sessionStorage.getItem(this.#key + this.#dismissed) === 'true';
    }
}
