import { DEFAULT_CONFIG } from './data/defaults';
import { cookiebarInitEvent, cookiebarSaveEvent } from './events/customEvents';
import { extend } from './lib/extend.js';
import { serialize } from './lib/serialize.js';
import {
    consoleLog,
    createScript,
    getHostname,
    getTime,
    insertAtPosition,
    isExpired,
    isFocusable,
    isPageAllowed,
    isTrackingAllowed,
    onResourceLoaded,
    sortCookiesByLoadingOrder,
} from './lib/utils';
import ConsentLogger from './modules/consentLogger.js';
import { add as addModule, call as callModule } from './modules/module';
import Cache from './store/cache';
import Storage from './store/local';
import Session from './store/session';

/**
 * @typedef {Object} ResourceEvent
 * @property {string} src - The URL or identifier of the resource
 * @property {() => void} callback - Function to call when the resource is loaded
 */

/**
 * @callback addModuleCallback
 * @callback onResourceLoadedCallback
 */
export class ContaoCookiebar {
    /** @type {Object} */
    settings;

    /** @type {boolean} */
    #visible = false;

    /** @type {HTMLElement|null} */
    #dom;

    /** @type {Object.<string, any>} */
    modules = {};

    /** @type {Array<string>} */
    loadedResources = [];

    /** @type {ResourceEvent[]} */
    resourcesEvents = [];

    /**
     * @param {Object} settings
     */
    constructor(settings) {
        this.settings = extend(true, DEFAULT_CONFIG, settings);
        this.#dom = document.querySelector(this.settings.selector);
        this.cache = new Cache();
        this.storage = new Storage(this.settings);
        this.session = new Session();
        const storage = this.storage.get();

        // Set visibility
        if (
            !this.session.isDismissed() &&
            !this.settings.hideOnInit &&
            (parseInt(storage.version) !== parseInt(this.settings.version) ||
                parseInt(storage.configId) !== parseInt(this.settings.configId) ||
                isExpired(storage.saved, this)) &&
            isTrackingAllowed(this) &&
            isPageAllowed(this)
        ) {
            this.#visible = true;
        }

        // Inputs
        this.inputs = [];
        /** @type {NodeListOf<HTMLInputElement>} inputs */
        const inputs = this.#dom.querySelectorAll('input[name="cookie[]"]');

        inputs.forEach((input) => {
            if (!input.disabled) {
                this.inputs.push(input);
            }
        });

        // Trigger logger info
        if (this.settings.disableTracking) {
            consoleLog(
                'The execution of scripts is prevented. Please log out of the backend to test scripts, or disable the setting within the cookiebar config.',
            );
        }

        if (this.settings.consentLog) {
            this.consentlogger = new ConsentLogger(this.settings, this.storage);
        }

        this.#registerEvents(); // Register events
        this.#registerTriggerEvents(); // Register trigger events
        this.focusTrap = this.focusTrap.bind(this);
        this.#initClose();

        if (this.settings.focusTrap) {
            this.#initFocusTrap();
        }

        if (this.settings.blocking) {
            this.#registerInertObserver();
        }

        sortCookiesByLoadingOrder(this);
        this.#validateCookies(storage.cookies);
        this.#checkVisibility();
        this.#setConfigurations();
        this.#loadScripts();

        // Restore temporary status
        this.updateUserInterface();

        window.dispatchEvent(cookiebarInitEvent(this));
    }

    /**
     * @deprecated get() has been deprecated, use the class instance instead
     */
    get() {
        console.warn('cookiebar.get() is deprecated. Use the class instance instead.');
        return this;
    }

    /**
     * @returns {*}
     */
    getStorage() {
        return this.storage.get();
    }

    /**
     * @deprecated issetCookie() has been deprecated, use cookieExists() instead
     *
     * @param {int|string} cookie
     * @returns {boolean}
     */
    issetCookie = function (cookie) {
        console.warn('cookiebar.issetCookie() is deprecated. Use cookiebar.cookieExists() instead.');
        return this.storage.isset(cookie);
    };

    /**
     * @param {int|string} cookie
     * @returns {boolean}
     */
    cookieExists(cookie) {
        return this.storage.isset(cookie);
    }

    /**
     * @param {HTMLFrameElement} element
     * @param {int} cookieId
     * @param {string} url
     */
    unblock(element, cookieId, url) {
        if (element.tagName.toLowerCase() === 'iframe') {
            element.src = url;
        } else if (element.tagName.toLowerCase()) {
            window.location.href = url;
        }

        this.push(cookieId);
        this.#unblockIframe(cookieId);
    }

    /**
     * @param {int} cookieId
     * @param {addModuleCallback} callback
     * @param {*} objContent
     * @returns {boolean}
     *
     * @deprecated Deprecated since Cookiebar 2.3, to be removed in Version 3.0
     *             No replacement has been added yet.
     */
    addModule(cookieId, callback, objContent) {
        return addModule(cookieId, callback, objContent, this);
    }

    /**
     * @param {int|string} cookieId
     * @param {onResourceLoadedCallback} callback
     */
    onResourceLoaded(cookieId, callback) {
        onResourceLoaded(cookieId, callback, this);
    }

    /**
     * @param {int|boolean} restore
     */
    show(restore) {
        this.#visible = true;
        this.#checkVisibility();

        if (!!restore) {
            this.updateUserInterface();
        }
    }

    hide() {
        this.#visible = false;
        this.#checkVisibility();
    }

    /**
     * @param {KeyboardEvent} e
     */
    focusTrap(e) {
        if (!(e.key === 'Tab' || e.keyCode === 9)) return;

        if (!this.focused) {
            this.focused = true;
            this.firstFocus?.classList.remove('cc-hide-focus');
        }

        if (document.activeElement === this.lastFocus && !e.shiftKey) {
            e.preventDefault();
            this.firstFocus?.focus();
        }

        if (document.activeElement === this.firstFocus && e.shiftKey) {
            e.preventDefault();
            this.lastFocus?.focus();
        }

        if (
            document.activeElement === this.toggleOpener &&
            !isFocusable(this.lastFocus) &&
            this.toggleOpener.ariaExpanded === 'false' &&
            !e.shiftKey
        ) {
            e.preventDefault();
            this.firstFocus?.focus();
        }
    }

    #initClose() {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '✕';
        btn.ariaLabel = 'Close';
        btn.classList.add('cc-close');

        this.#dom.querySelector('.cc-inner').appendChild(btn);

        const close = () => {
            this.session.setDismissed();
            this.hide();
        };

        btn.addEventListener('click', close);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                close();
            }
        });
    }

    #save(event) {
        const arrCookies = [];
        const btn = event.currentTarget;
        let mode = 0;

        if (btn.hasAttribute('data-accept-all')) {
            mode = 1;
        } else if (btn.hasAttribute('data-deny-all')) {
            mode = 2;
        }

        this.#inert(false);

        this.inputs.forEach((input) => {
            if (mode === 2) {
                input.checked = false;
            } else if (mode === 1 || input.checked) {
                arrCookies.push(parseInt(input.value));
                input.checked = true;
            }
        });

        // Overwrite current storage
        this.storage.set({
            configId: this.settings.configId,
            pageId: this.settings.pageId,
            version: this.settings.version,
            saved: getTime(),
            cookies: arrCookies,
        });

        // Validate new set of cookies
        this.#validateCookies(arrCookies, true);

        this.#setConfigurations();
        this.#loadScripts();

        // Add new log entry
        if (this.settings.consentLog) {
            this.consentlogger.log();
        }

        // Show iframes and call modules
        arrCookies.forEach((cookieId) => {
            // Iframes
            if (
                this.settings.cookies.hasOwnProperty('_' + cookieId) &&
                this.settings.cookies['_' + cookieId].type === 'iframe'
            ) {
                this.#unblockIframe(cookieId);
            }

            // Modules
            if (this.modules.hasOwnProperty('_' + cookieId)) {
                callModule(cookieId, this);
            }
        });

        // Add CSS class
        this.#dom.classList.add(this.settings.classes.onSave);

        window.dispatchEvent(cookiebarSaveEvent(this));

        this.updateUserInterface(true);
    }

    /**
     * @param {int|string} cookieId
     */
    push(cookieId) {
        const storage = this.storage.get();

        if (!storage.cookies.includes(cookieId)) {
            // Update storage
            storage.cookies.push(parseInt(cookieId));
            this.storage.set(storage);

            // Set new status
            this.settings.cookies['_' + cookieId].confirmed = true;

            // Add new log entry
            if (this.settings.consentLog) {
                this.consentlogger.log();
            }
        }
    }

    /**
     * @param {boolean} force
     */
    updateUserInterface(force = false) {
        if (!this.show && force !== true) {
            return;
        }

        const storage = this.storage.get();
        let cookies = [];

        if (storage.cookies && storage.cookies.length) {
            cookies = storage.cookies;
        } else if (storage.version === -1) {
            for (const cookieId in this.settings.cookies) {
                const cid = parseInt(cookieId.replace('_', ''));

                if (this.settings.cookies[cookieId].checked) {
                    cookies.push(cid);
                }
            }
        }

        cookies.forEach((cookieId) => {
            const input = this.#dom.querySelector('[id="c' + cookieId + '"]');

            if (!!input) {
                input.checked = true;
            }
        });

        /** @type {NodeListOf<HTMLInputElement>} */
        const arrGroupInputs = this.#dom.querySelectorAll('input[name="group[]"]');

        arrGroupInputs.forEach((groupInput) => {
            if (groupInput.disabled) {
                return;
            }

            groupInput.checked = false;
            groupInput.classList.remove(this.settings.classes.onGroupSplitSelection);

            /** @type {NodeListOf<HTMLInputElement>} */
            const inputs = groupInput.parentElement.querySelectorAll('input[name="cookie[]"]');
            const arrGroup = [];

            if (!!inputs) {
                inputs.forEach(function (input) {
                    if (!input.disabled) {
                        arrGroup.push(!!input.checked);
                    }
                });

                if (arrGroup.indexOf(false) === -1) {
                    groupInput.checked = true;
                } else if (arrGroup.indexOf(true) !== -1 && arrGroup.indexOf(false) !== -1) {
                    groupInput.classList.add(this.settings.classes.onGroupSplitSelection);
                }
            }
        });
    }

    /**
     * @param {any[]} arrCookies
     * @param {boolean} deleteCookies
     */
    #validateCookies(arrCookies, deleteCookies = false) {
        const arrDelete = [];

        for (const cookieId in this.settings.cookies) {
            if (!this.settings.cookies.hasOwnProperty(cookieId)) {
                continue;
            }

            const intCookieId = parseInt(cookieId.replace('_', ''));
            const previousState = !!this.settings.cookies[cookieId].confirmed;
            const currentState = arrCookies.includes(intCookieId);
            const deleteCookie = previousState !== currentState && !currentState;

            this.settings.cookies[cookieId].confirmed = currentState;

            if (true === deleteCookies && deleteCookie) {
                const token = this.settings.cookies[cookieId].token;

                if (null !== token) {
                    token.forEach((token) => {
                        arrDelete.push(token);
                    });
                }
            }
        }

        if (true === deleteCookies && arrDelete.length) {
            const request = new XMLHttpRequest();
            request.open('POST', '/cookiebar/delete', true);
            request.send(serialize({ tokens: arrDelete }));
        }
    }

    #setConfigurations() {
        for (const configId in this.settings.configs) {
            if (!this.settings.configs.hasOwnProperty(configId)) {
                continue;
            }

            const config = this.settings.configs[configId];

            const prefixed = Object.fromEntries(Object.entries(config.cookies).map(([k, v]) => [`_${k}`, v]));

            const confirmed = this.#checkCookieConfirmation(prefixed);

            if (null !== config.resources) {
                config.resources.forEach((resource, index) => {
                    // 1. load script only if one of the cookies was confirmed
                    // 2. load script only if one of the cookies was not confirmed
                    // 3. load script always
                    if (
                        (resource.mode === 1 && confirmed) ||
                        (resource.mode === 2 && !confirmed) ||
                        resource.mode === 3
                    ) {
                        if (this.cache.verify(config, index, 'config_resource')) {
                            return;
                        }

                        this.#addResource(resource);
                    }
                });
            }

            if (null !== config.scripts) {
                config.scripts.forEach((script, index) => {
                    // 1. load script only if one of the cookies was confirmed
                    // 2. load script only if one of the cookies was not confirmed
                    // 3. load script always
                    if ((script.mode === 1) === confirmed || (script.mode === 2) === !confirmed || script.mode === 3) {
                        if (this.cache.verify(config, index, 'config_script')) {
                            return;
                        }

                        this.#addScript(script);
                    }
                });
            }
        }
    }

    #loadScripts() {
        for (const cookieId in this.settings.cookies) {
            if (!this.settings.cookies.hasOwnProperty(cookieId)) {
                continue;
            }

            const cookie = this.settings.cookies[cookieId];

            if (null !== cookie.resources) {
                cookie.resources.forEach((resource, index) => {
                    // 1. load script if cookie confirmed
                    // 2. load script if cookie not confirmed
                    // 3. load script always
                    if (
                        (resource.mode === 1 && cookie.confirmed) ||
                        (resource.mode === 2 && !cookie.confirmed) ||
                        resource.mode === 3
                    ) {
                        if (this.cache.verify(cookie, index, 'resource')) {
                            return;
                        }

                        this.#addResource(resource);
                    }
                });
            }

            if (null !== cookie.scripts) {
                cookie.scripts.forEach((script, index) => {
                    // 1. load script if cookie confirmed
                    // 2. load script if cookie not confirmed
                    // 3. load script always
                    if (
                        (script.mode === 1) === cookie.confirmed ||
                        (script.mode === 2) === !cookie.confirmed ||
                        script.mode === 3
                    ) {
                        if (this.cache.verify(cookie, index, 'script')) {
                            return;
                        }

                        this.#addScript(script);
                    }
                });
            }
        }
    }

    /**
     * @param {Object} script
     */
    #addScript(script) {
        if (this.settings.disableTracking) {
            consoleLog('Script execution was stopped.');
            return;
        }

        // Create script tag
        script.script = createScript(script.script);

        // Insert at defined position
        insertAtPosition(script.script, script.position);
    }

    /**
     * @param resource
     */
    #addResource(resource) {
        if (this.settings.disableTracking) {
            consoleLog('Adding a resource was stopped.');
            return;
        }

        // Skip resources that are already available
        try {
            const scripts = document.querySelectorAll('script[src]');
            const host = getHostname(resource.src);
            for (let i = scripts.length; i--; ) {
                if (scripts[i].src.indexOf(host) !== -1 && host !== window.location.host) {
                    return false;
                }
            }
        } catch (e) {}

        // Load resource
        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.nonce = document.querySelector('script[nonce]')?.nonce ?? null;
        script.src = resource.src;
        script.onload = () => {
            // Mark resource as loaded
            this.loadedResources.push(resource.src);

            // Process resource events
            this.#processResourceEvents();
        };

        if (null !== resource.flags && resource.flags.length) {
            resource.flags.forEach((flag) => {
                if (typeof flag === 'object') {
                    script.setAttribute(flag[0], flag[1]);
                } else {
                    script[flag] = true;
                }
            });
        }

        document.head.append(script);
    }

    /**
     * @param {Object.<string, any>} cookies
     * @returns {boolean}
     */
    #checkCookieConfirmation(cookies) {
        let confirmed = false;

        for (const cookieId in cookies) {
            if (!this.settings.cookies.hasOwnProperty(cookieId)) {
                continue;
            }

            if (this.settings.cookies[cookieId].confirmed) {
                confirmed = true;
                break;
            }
        }

        return confirmed;
    }

    #registerTriggerEvents() {
        document.querySelectorAll('a.ccb-trigger, strong.ccb-trigger').forEach((btn) => {
            this.#applyTriggerEvent(btn);
        });

        // See #152
        new MutationObserver((mutationsList) => {
            for (const mutation of mutationsList) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((element) => {
                        if (element instanceof HTMLElement && element.matches('a.ccb-trigger, strong.ccb-trigger')) {
                            this.#applyTriggerEvent(element);
                        }
                    });
                }
            }
        }).observe(document, {
            attributes: false,
            childList: true,
            subtree: true,
        });
    }

    /**
     * @param {HTMLElement} el
     */
    #applyTriggerEvent(el) {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            this.show(el.classList.contains('ccb-prefill'));
        });
    }

    #registerEvents() {
        this.#dom.querySelectorAll('[data-save],[data-accept-all],[data-deny-all]').forEach((btn) => {
            btn.addEventListener('click', (e) => this.#save(e));
        });

        this.#dom.querySelectorAll('[data-toggle-cookies]').forEach((btn) => {
            btn.addEventListener('click', (e) => this.#toggleCookies(e));
        });

        this.#dom.querySelectorAll('[data-toggle-group]').forEach((btn) => {
            btn.addEventListener('click', (e) => this.#toggleGroup(e));
        });
    }

    #processResourceEvents() {
        if (!this.resourcesEvents.length) {
            return false;
        }

        this.resourcesEvents.forEach((event, index) => {
            if (this.loadedResources.indexOf(event.src) === -1) {
                return false;
            }

            event.callback();

            delete this.resourcesEvents[index];
        });
    }

    /**
     * @param {int|string} cookieId
     */
    #unblockIframe(cookieId) {
        const iframes = document.querySelectorAll('iframe[data-ccb-id="' + cookieId + '"]');

        iframes.forEach((iframe) => {
            // Trigger an iFrame reload
            iframe.src = iframe.src;
            iframe.removeAttribute('data-ccb-id');
        });

        this.updateUserInterface();
    }

    #initFocusTrap() {
        /** @type {NodeListOf<HTMLElement>} */
        const focusable = this.#dom.querySelectorAll(
            'a[href]:not([disabled]), button:not([disabled]), input[type="checkbox"]:not([disabled])',
        );

        this.toggleOpener = this.#dom.querySelector('[data-ft-opener]');
        this.firstFocus = focusable[0];
        this.lastFocus = focusable[focusable.length - 1];
    }

    /**
     * @param {boolean} state
     */
    #inert(state) {
        if (this.settings.blocking) {
            this.#dom?.parentElement.querySelectorAll(':scope >:not(script):not(.contao-cookiebar)')?.forEach((el) => {
                state ? el.setAttribute('inert', '') : el.removeAttribute('inert');
            });
        }

        if (!this.settings.focusTrap) return;

        if (state) {
            document.addEventListener('keydown', this.focusTrap);
            this.#dom.querySelector('.cc-inner').onanimationend = () => {
                this.focused = false;
                this.firstFocus?.classList.add('cc-hide-focus');
                this.firstFocus?.focus({ preventScroll: true });
            };
        } else {
            document.removeEventListener('keydown', this.focusTrap);
        }
    }

    // Check for children that are added whilst the page builds (race-condition)
    #registerInertObserver() {
        new MutationObserver((list) => {
            for (const mutation of list) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (
                            this.#visible &&
                            node.nodeType === Node.ELEMENT_NODE &&
                            node instanceof HTMLElement &&
                            !node.classList.contains('.contao-cookiebar') &&
                            !node.hasAttribute('inert')
                        ) {
                            node.setAttribute('inert', '');
                        }
                    });
                }
            }
        }).observe(this.#dom, {
            childList: true,
            subtree: false,
        });
    }

    #checkVisibility() {
        if (this.#visible) {
            this.#dom.classList.remove(this.settings.classes.onSave);
            this.#dom.classList.add(this.settings.classes.onShow);
            this.#inert(true);
        } else {
            this.#dom.classList.remove(this.settings.classes.onShow);
            this.#inert(false);
        }
    }

    /**
     * @param {Event} event
     */
    #toggleCookies(event) {
        /** @type {HTMLInputElement} */
        const element = event.currentTarget;
        const state = element.checked;

        /** @type {NodeListOf<HTMLInputElement>} */
        const inputs = element.parentElement.querySelectorAll('input[name="cookie[]"]');

        inputs.forEach((input) => {
            if (!input.disabled) {
                input.checked = state;
            }
        });

        element.classList.remove(this.settings.classes.onGroupSplitSelection);
    }

    /**
     * @param {Event} event
     */
    #toggleGroup(event) {
        /** @type {HTMLInputElement} */
        const element = event.currentTarget;
        const state = !element.classList.contains(this.settings.classes.onGroupToggle);

        element.setAttribute('aria-expanded', state ? 'true' : 'false');

        /** @type {NodeListOf<HTMLElement>} */
        const groups = element.parentElement.querySelectorAll(':scope > .toggle-group');

        groups.forEach((group) => {
            group.style.display = state ? 'block' : 'none';
        });

        element.classList.toggle(this.settings.classes.onGroupToggle);
    }
}
