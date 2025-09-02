import { syncPreferences } from './components/cookiebar';
import { DEFAULT_CONFIG } from './data/defaults';
import { cookiebarInitEvent, cookiebarSaveEvent } from './events/cookiebarEvents';
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

const ContaoCookiebar = (function () {
    'use strict';

    return function (settings) {
        let p = {},
            cookiebar = {};

        const init = function () {
            // Defaults
            cookiebar.settings = extend(true, DEFAULT_CONFIG, settings);
            cookiebar.dom = document.querySelector(cookiebar.settings.selector);
            cookiebar.cache = {};
            cookiebar.modules = {};
            cookiebar.loadedResources = [];
            cookiebar.resourcesEvents = [];
            cookiebar.show = false;

            let storage = getStorage(cookiebar);

            // Set visibility
            if (
                !cookiebar.settings.hideOnInit &&
                (parseInt(storage.version) !== parseInt(cookiebar.settings.version) ||
                    parseInt(storage.configId) !== parseInt(cookiebar.settings.configId) ||
                    isExpired(storage.saved, cookiebar)) &&
                isTrackingAllowed(cookiebar) &&
                isPageAllowed(cookiebar)
            ) {
                cookiebar.show = true;
            }

            // Inputs
            cookiebar.inputs = [];
            cookiebar.dom.querySelectorAll('input[name="cookie[]"]').forEach(function (input, index) {
                if (!input.disabled) {
                    cookiebar.inputs.push(input);
                }
            });

            // Trigger logger info
            if (cookiebar.settings.disableTracking) {
                logger(
                    'The execution of scripts is prevented. Please log out of the backend to test scripts, or disable the setting within the cookiebar config.',
                );
            }

            registerEvents(); // Register events
            registerTriggerEvents(); // Register trigger events

            if (cookiebar.settings.focusTrap) {
                initFocusTrap();
            }

            if (cookiebar.settings.blocking) {
                registerInertObserver();
            }

            sortCookiesByLoadingOrder(cookiebar);
            validateCookies(storage.cookies);
            checkVisibility();
            setConfigurations();
            loadScripts();

            // Restore temporary status
            syncPreferences(cookiebar);

            window.dispatchEvent(cookiebarInitEvent(cookiebar));
        };

        const save = function (e) {
            let arrCookies = [];
            let btn = e.currentTarget;
            let mode = 0;

            if (btn.hasAttribute('data-accept-all')) {
                mode = 1;
            } else if (btn.hasAttribute('data-deny-all')) {
                mode = 2;
            }

            inert(false);

            cookiebar.inputs.forEach(function (input) {
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
                    configId: cookiebar.settings.configId,
                    pageId: cookiebar.settings.pageId,
                    version: cookiebar.settings.version,
                    saved: getTime(),
                    cookies: arrCookies,
                },
                cookiebar,
            );

            // Validate new set of cookies
            validateCookies(arrCookies, true);

            setConfigurations();
            loadScripts();

            // Add new log entry
            log(cookiebar, getStorage(cookiebar));

            // Show iframes and call modules
            if (arrCookies.length) {
                arrCookies.forEach(function (cookieId) {
                    // Iframes
                    if (
                        cookiebar.settings.cookies.hasOwnProperty('_' + cookieId) &&
                        cookiebar.settings.cookies['_' + cookieId].type === 'iframe'
                    ) {
                        unblockIframe(cookieId);
                    }

                    // Modules
                    if (cookiebar.modules.hasOwnProperty('_' + cookieId)) {
                        callModule(cookiebar, cookieId);
                    }
                });
            }

            // Add CSS class
            cookiebar.dom.classList.add(cookiebar.settings.classes.onSave);

            window.dispatchEvent(cookiebarSaveEvent(cookiebar));

            syncPreferences(cookiebar, true);
        };

        const push = function (cookieId) {
            let storage = getStorage(cookiebar);

            if (!storage.cookies.includes(cookieId)) {
                // Update storage
                storage.cookies.push(parseInt(cookieId));
                setStorage(storage, cookiebar);

                // Set new status
                cookiebar.settings.cookies['_' + cookieId].confirmed = true;

                // Add new log entry
                log(cookiebar, storage);
            }
        };

        const validateCookies = function (arrCookies, deleteCookies) {
            let arrDelete = [];

            for (let cookieId in cookiebar.settings.cookies) {
                const intCookieId = parseInt(cookieId.replace('_', ''));

                if (!cookiebar.settings.cookies.hasOwnProperty(cookieId)) {
                    continue;
                }

                let previousState = !!cookiebar.settings.cookies[cookieId].confirmed;
                let currentState = arrCookies.includes(intCookieId);
                let deleteCookie = previousState !== currentState && !currentState;

                cookiebar.settings.cookies[cookieId].confirmed = currentState;

                if (true === deleteCookies && deleteCookie) {
                    let token = cookiebar.settings.cookies[cookieId].token;

                    if (null !== token) {
                        token.forEach(function (token) {
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
        };

        const setConfigurations = function () {
            let configId;
            for (configId in cookiebar.settings.configs) {
                if (!cookiebar.settings.configs.hasOwnProperty(configId)) {
                    continue;
                }

                let config = cookiebar.settings.configs[configId];

                const prefixed = Object.fromEntries(Object.entries(config.cookies).map(([k, v]) => [`_${k}`, v]));

                let confirmed = checkCookieConfirmation(prefixed);

                if (null !== config.resources) {
                    config.resources.forEach(function (resource, index) {
                        // 1. load script only if one of the cookies was confirmed
                        // 2. load script only if one of the cookies was not confirmed
                        // 3. load script always
                        if (
                            (resource.mode === 1 && confirmed) ||
                            (resource.mode === 2 && !confirmed) ||
                            resource.mode === 3
                        ) {
                            if (cache(getCacheToken(config, index), 'config_resource', cookiebar)) {
                                return;
                            }

                            addResource(resource);
                        }
                    });
                }

                if (null !== config.scripts) {
                    config.scripts.forEach(function (script, index) {
                        // 1. load script only if one of the cookies was confirmed
                        // 2. load script only if one of the cookies was not confirmed
                        // 3. load script always
                        if (
                            (script.mode === 1) === confirmed ||
                            (script.mode === 2) === !confirmed ||
                            script.mode === 3
                        ) {
                            if (cache(getCacheToken(config, index), 'config_script', cookiebar)) {
                                return;
                            }

                            addScript(script);
                        }
                    });
                }
            }
        };

        const loadScripts = function () {
            let cookieId;
            for (cookieId in cookiebar.settings.cookies) {
                if (!cookiebar.settings.cookies.hasOwnProperty(cookieId)) {
                    continue;
                }

                let cookie = cookiebar.settings.cookies[cookieId];

                if (null !== cookie.resources) {
                    cookie.resources.forEach(function (resource, index) {
                        // 1. load script if cookie confirmed
                        // 2. load script if cookie not confirmed
                        // 3. load script always
                        if (
                            (resource.mode === 1 && cookie.confirmed) ||
                            (resource.mode === 2 && !cookie.confirmed) ||
                            resource.mode === 3
                        ) {
                            if (cache(getCacheToken(cookie, index), 'resource', cookiebar)) {
                                return;
                            }

                            addResource(resource);
                        }
                    });
                }

                if (null !== cookie.scripts) {
                    cookie.scripts.forEach(function (script, index) {
                        // 1. load script if cookie confirmed
                        // 2. load script if cookie not confirmed
                        // 3. load script always
                        if (
                            (script.mode === 1) === cookie.confirmed ||
                            (script.mode === 2) === !cookie.confirmed ||
                            script.mode === 3
                        ) {
                            if (cache(getCacheToken(cookie, index), 'script', cookiebar)) {
                                return;
                            }

                            addScript(script);
                        }
                    });
                }
            }
        };

        const addScript = function (script) {
            if (cookiebar.settings.disableTracking) {
                logger('Script execution was stopped.');
                return;
            }

            // Create script tag
            script.script = createScript(script.script);

            // Insert at defined position
            insertAtPosition(script.script, script.position);
        };

        const addResource = function (resource) {
            if (cookiebar.settings.disableTracking) {
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
                cookiebar.loadedResources.push(resource.src);

                // Process resource events
                processResourceEvents();
            };

            if (null !== resource.flags && resource.flags.length) {
                resource.flags.forEach(function (flag) {
                    if (typeof flag === 'object') {
                        script.setAttribute(flag[0], flag[1]);
                    } else {
                        script[flag] = true;
                    }
                });
            }

            document.head.append(script);
        };

        const checkCookieConfirmation = function (cookies) {
            let confirmed = false;
            let cookieId;

            for (cookieId in cookies) {
                if (!cookiebar.settings.cookies.hasOwnProperty(cookieId)) {
                    continue;
                }

                let cookie = cookiebar.settings.cookies[cookieId];

                if (cookie.confirmed) {
                    confirmed = true;
                    break;
                }
            }

            return confirmed;
        };

        const registerTriggerEvents = function () {
            document.querySelectorAll('a.ccb-trigger, strong.ccb-trigger').forEach(function (btn) {
                applyTriggerEvent(btn);
            });

            // See #152
            new MutationObserver(function (mutationsList) {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function (element) {
                            if (element.matches && element.matches('a.ccb-trigger, strong.ccb-trigger')) {
                                applyTriggerEvent(element);
                            }
                        });
                    }
                }
            }).observe(document, {
                attributes: false,
                childList: true,
                subtree: true,
            });
        };

        const applyTriggerEvent = function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                p.show(el.classList.contains('ccb-prefill'));
            });
        };

        const registerEvents = function () {
            let btnToggleCookies = cookiebar.dom.querySelectorAll('[data-toggle-cookies]');
            let btnToggleGroups = cookiebar.dom.querySelectorAll('[data-toggle-group]');
            let btnAction = cookiebar.dom.querySelectorAll('[data-save],[data-accept-all],[data-deny-all]');

            if (btnAction.length) {
                btnAction.forEach(function (btn) {
                    btn.addEventListener('click', save);
                });
            }

            if (btnToggleCookies.length) {
                btnToggleCookies.forEach(function (btn) {
                    btn.addEventListener('click', toggleCookies);
                });
            }

            if (btnToggleGroups.length) {
                btnToggleGroups.forEach(function (btn) {
                    btn.addEventListener('click', toggleGroup);
                });
            }
        };

        const processResourceEvents = function () {
            if (!cookiebar.resourcesEvents.length) {
                return false;
            }

            cookiebar.resourcesEvents.forEach(function (event, index) {
                if (cookiebar.loadedResources.indexOf(event.src) === -1) {
                    return false;
                }

                event.callback();

                delete cookiebar.resourcesEvents[index];
            });
        };

        const unblockIframe = function (cookieId) {
            let iframes = document.querySelectorAll('iframe[data-ccb-id="' + cookieId + '"]');

            if (iframes.length) {
                iframes.forEach(function (iframe) {
                    iframe.src = iframe.src;
                    iframe.removeAttribute('data-ccb-id');
                });
            }

            syncPreferences(cookiebar);
        };

        const initFocusTrap = function (cookiebar) {
            const focusable = cookiebar.dom.querySelectorAll(
                'a[href]:not([disabled]), button:not([disabled]), input[type="checkbox"]:not([disabled])',
            );

            cookiebar.toggleOpener = cookiebar.dom.querySelector('[data-ft-opener]');
            cookiebar.firstFocus = focusable[0];
            cookiebar.lastFocus = focusable[focusable.length - 1];
        };

        const focusTrap = function (e) {
            if (!(e.key === 'Tab' || e.keyCode === 9)) return;

            if (!cookiebar.focused) {
                cookiebar.focused = true;
                cookiebar.firstFocus?.classList.remove('cc-hide-focus');
            }

            if (document.activeElement === cookiebar.lastFocus && !e.shiftKey) {
                e.preventDefault();
                cookiebar.firstFocus?.focus();
            }

            if (document.activeElement === cookiebar.firstFocus && e.shiftKey) {
                e.preventDefault();
                cookiebar.lastFocus?.focus();
            }

            if (
                document.activeElement === cookiebar.toggleOpener &&
                !isFocusable(cookiebar.lastFocus) &&
                cookiebar.toggleOpener.ariaExpanded === 'false' &&
                !e.shiftKey
            ) {
                e.preventDefault();
                cookiebar.firstFocus?.focus();
            }
        };

        const inert = function (state) {
            if (cookiebar.settings.blocking) {
                cookiebar.dom?.parentElement
                    .querySelectorAll(':scope >:not(script):not(.contao-cookiebar)')
                    ?.forEach((el) => {
                        state ? el.setAttribute('inert', '') : el.removeAttribute('inert');
                    });
            }

            if (!cookiebar.settings.focusTrap) return;

            if (state) {
                document.addEventListener('keydown', focusTrap);
                cookiebar.dom.querySelector('.cc-inner').onanimationend = () => {
                    cookiebar.focused = false;
                    cookiebar.firstFocus?.classList.add('cc-hide-focus');
                    cookiebar.firstFocus?.focus({ preventScroll: true });
                };
            } else {
                document.removeEventListener('keydown', focusTrap);
            }
        };

        // Check for children that are added whilst the page builds (race-condition)
        const registerInertObserver = function () {
            new MutationObserver((list) => {
                for (const mutation of list) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach((node) => {
                            if (
                                cookiebar.show &&
                                node.nodeType === Node.ELEMENT_NODE &&
                                !node.classList.contains('.contao-cookiebar') &&
                                !node.hasAttribute('inert')
                            ) {
                                node.setAttribute('inert', '');
                            }
                        });
                    }
                }
            }).observe(cookiebar.dom, {
                childList: true,
                subtree: false,
            });
        };

        const checkVisibility = function () {
            if (cookiebar.show) {
                cookiebar.dom.classList.remove(cookiebar.settings.classes.onSave);
                cookiebar.dom.classList.add(cookiebar.settings.classes.onShow);
                inert(true);
            } else {
                cookiebar.dom.classList.remove(cookiebar.settings.classes.onShow);
                inert(false);
            }
        };

        const toggleCookies = function () {
            let state = this.checked;
            let inputs = this.parentElement.querySelectorAll('input[name="cookie[]"]');

            if (inputs) {
                inputs.forEach(function (input, index) {
                    if (!input.disabled) {
                        input.checked = state;
                    }
                });
            }

            this.classList.remove(cookiebar.settings.classes.onGroupSplitSelection);
        };

        const toggleGroup = function () {
            let state = !this.classList.contains(cookiebar.settings.classes.onGroupToggle);

            this.setAttribute('aria-expanded', state ? 'true' : 'false');

            let groups = this.parentElement.querySelectorAll(':scope > .toggle-group');

            if (groups) {
                groups.forEach(function (group, index) {
                    group.style.display = state ? 'block' : 'none';
                });
            }

            this.classList.toggle(cookiebar.settings.classes.onGroupToggle);
        };

        /**
         * Public methods
         *
         */
        p.get = function () {
            return cookiebar;
        };

        p.getStorage = function () {
            return getStorage(cookiebar);
        };

        /**
         * @deprecated issetCookie() has been deprecated, use cookieExists() instead
         */
        p.issetCookie = function (varCookie) {
            console.warn('cookiebar.issetCookie() is deprecated. Use cookiebar.cookieExists() instead.');
            return p.cookieExists(varCookie);
        };

        p.cookieExists = function (varCookie) {
            return cookieExists(varCookie, cookiebar);
        };

        p.unblock = function (element, cookieId, url) {
            if (element.tagName.toLowerCase() === 'iframe') {
                element.src = url;
            } else if (element.tagName.toLowerCase()) {
                window.location.href = url;
            }

            push(cookieId);
            unblockIframe(cookieId);
        };

        p.addModule = function (cookieId, callback, objContent) {
            return addModule(cookieId, callback, objContent, cookiebar);
        };

        p.onResourceLoaded = function (cookieId, callback) {
            onResourceLoaded(cookieId, callback, cookiebar);
        };

        p.show = function (restore) {
            cookiebar.show = true;
            checkVisibility();

            if (!!restore) {
                syncPreferences(cookiebar);
            }
        };

        p.hide = function () {
            cookiebar.show = false;
            checkVisibility();
        };

        init();

        return p;
    };
})();

window.ContaoCookiebar = ContaoCookiebar;
