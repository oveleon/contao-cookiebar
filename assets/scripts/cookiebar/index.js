import { updateUserInterface } from './components/cookiebar';
import { DEFAULT_CONFIG } from './data/defaults';
import { cookiebarInitEvent, cookiebarSaveEvent } from './events/customEvents';
import { extend } from './lib/extend.js';
import { serialize } from './lib/serialize.js';
import {
    createScript,
    getHostname,
    getTime,
    insertAtPosition,
    isExpired,
    isFocusable,
    isPageAllowed,
    isTrackingAllowed,
    logger,
    onResourceLoaded,
    sortCookiesByLoadingOrder,
} from './lib/utils';
import { log } from './modules/log.js';
import { add as addModule, call as callModule } from './modules/module';
import { cache, getToken as getCacheToken } from './store/cache';
import { cookieExists, getStorage, setStorage } from './store/store';

export class ContaoCookiebar {
    'use strict';
    settings;
    cache = {};
    visible = false;
    #dom;
    #modules = {};
    #loadedResources = [];
    #resourcesEvents = [];

    constructor(settings) {
        this.settings = extend(true, DEFAULT_CONFIG, settings);
        this.#dom = document.querySelector(this.settings.selector);

        let storage = getStorage(this);

        // Set visibility
        if (
            !this.settings.hideOnInit &&
            (parseInt(storage.version) !== parseInt(this.settings.version) ||
                parseInt(storage.configId) !== parseInt(this.settings.configId) ||
                isExpired(storage.saved, this)) &&
            isTrackingAllowed(this) &&
            isPageAllowed(this)
        ) {
            this.visible = true;
        }

        // Inputs
        this.inputs = [];
        this.#dom.querySelectorAll('input[name="cookie[]"]').forEach((input) => {
            if (!input.disabled) {
                this.inputs.push(input);
            }
        });

        // Trigger logger info
        if (this.settings.disableTracking) {
            logger(
                'The execution of scripts is prevented. Please log out of the backend to test scripts, or disable the setting within the cookiebar config.',
            );
        }

        this.#registerEvents(); // Register events
        this.#registerTriggerEvents(); // Register trigger events

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
        updateUserInterface(this, this.#dom);

        window.dispatchEvent(cookiebarInitEvent(this));
    }

    publicMethod() {
        return 'hello world';
    }

    secondPublicMethod() {
        return 'goodbye world';
    }

    /**
     * @deprecated get() has been deprecated, use the class instance instead
     */
    get() {
        console.warn('cookiebar.get() is deprecated. Use the class instance instead.');
        return this;
    }

    getStorage() {
        return getStorage(this);
    }

    /**
     * @deprecated issetCookie() has been deprecated, use cookieExists() instead
     */
    issetCookie = function (varCookie) {
        console.warn('cookiebar.issetCookie() is deprecated. Use cookiebar.cookieExists() instead.');
        return cookieExists(varCookie, this);
    };

    cookieExists(varCookie) {
        return cookieExists(varCookie, this);
    }

    unblock(element, cookieId, url) {
        if (element.tagName.toLowerCase() === 'iframe') {
            element.src = url;
        } else if (element.tagName.toLowerCase()) {
            window.location.href = url;
        }

        this.push(cookieId);
        this.#unblockIframe(cookieId);
    }

    addModule(cookieId, callback, objContent) {
        return addModule(cookieId, callback, objContent, this);
    }

    onResourceLoaded(cookieId, callback) {
        onResourceLoaded(cookieId, callback, this);
    }

    show(restore) {
        this.visible = true;
        this.#checkVisibility();

        if (!!restore) {
            updateUserInterface(this, this.#dom);
        }
    }

    hide() {
        this.visible = false;
        this.#checkVisibility();
    }

    #save(event) {
        let arrCookies = [];
        let btn = event.currentTarget;
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
        setStorage(
            {
                configId: this.settings.configId,
                pageId: this.settings.pageId,
                version: this.settings.version,
                saved: getTime(),
                cookies: arrCookies,
            },
            this,
        );

        // Validate new set of cookies
        this.#validateCookies(arrCookies, true);

        this.#setConfigurations();
        this.#loadScripts();

        // Add new log entry
        log(this, getStorage(this));

        // Show iframes and call modules
        if (arrCookies.length) {
            arrCookies.forEach((cookieId) => {
                // Iframes
                if (
                    this.settings.cookies.hasOwnProperty('_' + cookieId) &&
                    this.settings.cookies['_' + cookieId].type === 'iframe'
                ) {
                    this.#unblockIframe(cookieId);
                }

                // Modules
                if (this.#modules.hasOwnProperty('_' + cookieId)) {
                    callModule(this, this.#dom, cookieId);
                }
            });
        }

        // Add CSS class
        this.#dom.classList.add(this.settings.classes.onSave);

        window.dispatchEvent(cookiebarSaveEvent(this));

        updateUserInterface(this, this.#dom, true);
    }

    push(cookieId) {
        let storage = getStorage(this);

        if (!storage.cookies.includes(cookieId)) {
            // Update storage
            storage.cookies.push(parseInt(cookieId));
            setStorage(storage, this);

            // Set new status
            this.settings.cookies['_' + cookieId].confirmed = true;

            // Add new log entry
            log(this, storage);
        }
    }

    #validateCookies(arrCookies, deleteCookies) {
        let arrDelete = [];

        for (let cookieId in this.settings.cookies) {
            const intCookieId = parseInt(cookieId.replace('_', ''));

            if (!this.settings.cookies.hasOwnProperty(cookieId)) {
                continue;
            }

            let previousState = !!this.settings.cookies[cookieId].confirmed;
            let currentState = arrCookies.includes(intCookieId);
            let deleteCookie = previousState !== currentState && !currentState;

            this.settings.cookies[cookieId].confirmed = currentState;

            if (true === deleteCookies && deleteCookie) {
                let token = this.settings.cookies[cookieId].token;

                if (null !== token) {
                    token.forEach((token) => {
                        arrDelete.push(token);
                    });
                }
            }
        }

        if (true === deleteCookies && arrDelete.length) {
            let request = new XMLHttpRequest();
            request.open('POST', '/cookiebar/delete', true);
            request.send(serialize({ tokens: arrDelete }));
        }
    }

    #setConfigurations() {
        for (let configId in this.settings.configs) {
            if (!this.settings.configs.hasOwnProperty(configId)) {
                continue;
            }

            let config = this.settings.configs[configId];

            const prefixed = Object.fromEntries(Object.entries(config.cookies).map(([k, v]) => [`_${k}`, v]));

            let confirmed = this.#checkCookieConfirmation(prefixed);

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
                        if (cache(getCacheToken(config, index), 'config_resource', this)) {
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
                        if (cache(getCacheToken(config, index), 'config_script', this)) {
                            return;
                        }

                        this.#addScript(script);
                    }
                });
            }
        }
    }

    #loadScripts() {
        let cookieId;
        for (cookieId in this.settings.cookies) {
            if (!this.settings.cookies.hasOwnProperty(cookieId)) {
                continue;
            }

            let cookie = this.settings.cookies[cookieId];

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
                        if (cache(getCacheToken(cookie, index), 'resource', this)) {
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
                        if (cache(getCacheToken(cookie, index), 'script', this)) {
                            return;
                        }

                        this.#addScript(script);
                    }
                });
            }
        }
    }

    #addScript(script) {
        if (this.settings.disableTracking) {
            logger('Script execution was stopped.');
            return;
        }

        // Create script tag
        script.script = createScript(script.script);

        // Insert at defined position
        insertAtPosition(script.script, script.position);
    }

    #addResource(resource) {
        if (this.settings.disableTracking) {
            logger('Adding a resource was stopped.');
            return;
        }

        // Skip resources that are already available
        try {
            let scripts = document.querySelectorAll('script[src]');
            let host = getHostname(resource.src);
            for (let i = scripts.length; i--; ) {
                if (scripts[i].src.indexOf(host) !== -1 && host !== window.location.host) {
                    return false;
                }
            }
        } catch (e) {}

        // Load resource
        let script = document.createElement('script');
        script.type = 'text/javascript';
        script.nonce = document.querySelector('script[nonce]')?.nonce ?? null;
        script.src = resource.src;
        script.onload = () => {
            // Mark resource as loaded
            this.#loadedResources.push(resource.src);

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

    #checkCookieConfirmation(cookies) {
        let confirmed = false;
        let cookieId;

        for (cookieId in cookies) {
            if (!this.settings.cookies.hasOwnProperty(cookieId)) {
                continue;
            }

            let cookie = this.settings.cookies[cookieId];

            if (cookie.confirmed) {
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
                        if (element.matches && element.matches('a.ccb-trigger, strong.ccb-trigger')) {
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

    #applyTriggerEvent(el) {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            this.show(el.classList.contains('ccb-prefill'));
        });
    }

    #registerEvents() {
        let btnToggleCookies = this.#dom.querySelectorAll('[data-toggle-cookies]');
        let btnToggleGroups = this.#dom.querySelectorAll('[data-toggle-group]');
        let btnAction = this.#dom.querySelectorAll('[data-save],[data-accept-all],[data-deny-all]');

        if (btnAction.length) {
            btnAction.forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    this.#save(e);
                });
            });
        }

        if (btnToggleCookies.length) {
            btnToggleCookies.forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    this.#toggleCookies(e);
                });
            });
        }

        if (btnToggleGroups.length) {
            btnToggleGroups.forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    this.#toggleGroup(e);
                });
            });
        }
    }

    #processResourceEvents() {
        if (!this.#resourcesEvents.length) {
            return false;
        }

        this.#resourcesEvents.forEach((event, index) => {
            if (this.#loadedResources.indexOf(event.src) === -1) {
                return false;
            }

            event.callback();

            delete this.#resourcesEvents[index];
        });
    }

    #unblockIframe(cookieId) {
        let iframes = document.querySelectorAll('iframe[data-ccb-id="' + cookieId + '"]');

        if (iframes.length) {
            iframes.forEach((iframe) => {
                iframe.src = iframe.src;
                iframe.removeAttribute('data-ccb-id');
            });
        }

        updateUserInterface(this, this.#dom);
    }

    #initFocusTrap() {
        const focusable = this.#dom.querySelectorAll(
            'a[href]:not([disabled]), button:not([disabled]), input[type="checkbox"]:not([disabled])',
        );

        this.toggleOpener = this.#dom.querySelector('[data-ft-opener]');
        this.firstFocus = focusable[0];
        this.lastFocus = focusable[focusable.length - 1];
    }

    #focusTrap(e) {
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

    #inert(state) {
        if (this.settings.blocking) {
            this.#dom?.parentElement.querySelectorAll(':scope >:not(script):not(.contao-cookiebar)')?.forEach((el) => {
                state ? el.setAttribute('inert', '') : el.removeAttribute('inert');
            });
        }

        if (!this.settings.focusTrap) return;

        if (state) {
            document.addEventListener('keydown', this.#focusTrap);
            this.#dom.querySelector('.cc-inner').onanimationend = () => {
                this.focused = false;
                this.firstFocus?.classList.add('cc-hide-focus');
                this.firstFocus?.focus({ preventScroll: true });
            };
        } else {
            document.removeEventListener('keydown', this.#focusTrap);
        }
    }

    // Check for children that are added whilst the page builds (race-condition)
    #registerInertObserver() {
        new MutationObserver((list) => {
            for (const mutation of list) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (
                            this.visible &&
                            node.nodeType === Node.ELEMENT_NODE &&
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
        if (this.visible) {
            this.#dom.classList.remove(this.settings.classes.onSave);
            this.#dom.classList.add(this.settings.classes.onShow);
            this.#inert(true);
        } else {
            this.#dom.classList.remove(this.settings.classes.onShow);
            this.#inert(false);
        }
    }

    #toggleCookies(event) {
        const element = event.currentTarget;
        let state = element.checked;
        let inputs = element.parentElement.querySelectorAll('input[name="cookie[]"]');

        if (inputs) {
            inputs.forEach((input) => {
                if (!input.disabled) {
                    input.checked = state;
                }
            });
        }

        element.classList.remove(this.settings.classes.onGroupSplitSelection);
    }

    #toggleGroup(event) {
        const element = event.currentTarget;
        let state = !element.classList.contains(this.settings.classes.onGroupToggle);

        element.setAttribute('aria-expanded', state ? 'true' : 'false');

        let groups = element.parentElement.querySelectorAll(':scope > .toggle-group');

        if (groups) {
            groups.forEach((group) => {
                group.style.display = state ? 'block' : 'none';
            });
        }

        element.classList.toggle(this.settings.classes.onGroupToggle);
    }
}
