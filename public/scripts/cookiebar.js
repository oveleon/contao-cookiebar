/**
 * Contao Cookiebar
 */
let ContaoCookiebar = (function () {

    'use strict';

    let Constructor = function (settings) {
        let p = {}, cookiebar = {}, defaults = {
            selector: '.contao-cookiebar',
            token: 'ccb_contao_token',
            configId: null,
            pageId: null,
            hideOnInit: false,
            blocking: false,
            version: null,
            lifetime: 63072000,
            consentLog: false,
            cookies: null,
            configs: null,
            doNotTrack: false,
            currentPageId: 0,
            excludedPageIds: null,
            disableTracking: false,
            texts: {
                acceptAndDisplay: 'Accept'
            },
            classes: {
                onSave: 'cc-saved',
                onShow: 'cc-active',
                onGroupToggle: 'cc-active',
                onGroupSplitSelection: 'cc-group-half'
            },
        };

        const init = function () {
            // Defaults
            cookiebar.settings = extend(true, defaults, settings);
            cookiebar.dom = document.querySelector(cookiebar.settings.selector);
            cookiebar.cache = {};
            cookiebar.modules = {};
            cookiebar.loadedResources = [];
            cookiebar.resourcesEvents = [];
            cookiebar.show = false;

            let storage = getStorage();

            // Set visibility
            if(
                !cookiebar.settings.hideOnInit &&
                (parseInt(storage.version) !== parseInt(cookiebar.settings.version) ||
                 parseInt(storage.configId) !== parseInt(cookiebar.settings.configId) ||
                 isExpired(storage.saved)) &&
                isTrackingAllowed() &&
                isPageAllowed()
            ){
                cookiebar.show = true;
            }

            // Inputs
            cookiebar.inputs = [];
            cookiebar.dom.querySelectorAll('input[name="cookie[]"]').forEach(function(input, index){
                if(!input.disabled){
                    cookiebar.inputs.push( input );
                }
            });

            // Trigger logger info
            if(cookiebar.settings.disableTracking)
            {
                logger('The execution of scripts is prevented. Please log out of the backend to test scripts, or disable the setting within the cookiebar config.');
            }

            // Register events
            registerEvents();

            // Register trigger events
            registerTriggerEvents();

            // Initialize focus trap
            initFocusTrap();

            if (cookiebar.settings.blocking) {
                // Register inert observer
                registerInertObserver();
            }

            // Sort cookies
            sortCookiesByLoadingOrder();

            // Validate cookies from storage
            validateCookies(storage.cookies);

            // Check visibility
            checkVisibility();

            // Load global config
            setConfigs();

            // Load scripts
            setScripts();

            // Restore temporary status
            restoreCookieStatus();

            // Custom event (init)
            let event = new CustomEvent("cookiebar_init", {
                detail: {
                    visibility: cookiebar.show,
                    cookiebar: cookiebar
                }
            });

            window.dispatchEvent(event);
        };

        const save = function(e){
            let arrCookies = [];
            let btn = e.currentTarget;
            let mode = 0;

            if(btn.hasAttribute('data-accept-all')){
                mode = 1;
            }else if(btn.hasAttribute('data-deny-all')){
                mode = 2;
            }

            inert(false)

            cookiebar.inputs.forEach(function(input){
                if(mode === 2){
                    input.checked = false;
                }else if(mode === 1 || input.checked) {
                    arrCookies.push(parseInt(input.value));
                    input.checked = true;
                }
            });

            // Overwrite current storage
            setStorage({
                configId: cookiebar.settings.configId,
                pageId: cookiebar.settings.pageId,
                version: cookiebar.settings.version,
                saved: getTime(),
                cookies: arrCookies
            });

            // Validate new set of cookies
            validateCookies(arrCookies, true);

            // Set configs
            setConfigs();

            // Set scripts
            setScripts();

            // Add new log entry
            log();

            // Show iframes and call modules
            if(arrCookies.length){
                arrCookies.forEach(function(cookieId){
                    // Iframes
                    if(cookiebar.settings.cookies.hasOwnProperty('_'+cookieId) && cookiebar.settings.cookies['_'+cookieId].type === 'iframe'){
                        unblockIframe(cookieId);
                    }

                    // Modules
                    if(cookiebar.modules.hasOwnProperty('_'+cookieId)){
                        callModule(cookieId);
                    }
                });
            }

            // Add CSS class
            cookiebar.dom.classList.add(cookiebar.settings.classes.onSave);

            // Custom event (save)
            let event = new CustomEvent("cookiebar_save", {
                detail: {
                    cookiebar: cookiebar
                }
            });

            window.dispatchEvent(event);

            restoreCookieStatus(true);
        };

        const push = function(cookieId){
            let storage = getStorage();

            if(!storage.cookies.includes(cookieId))
            {
                // Update storage
                storage.cookies.push(parseInt(cookieId));
                setStorage(storage);

                // Set new status
                cookiebar.settings.cookies['_'+cookieId].confirmed = true;

                // Add new log entry
                log();
            }
        };

        const sortCookiesByLoadingOrder = function() {
            const arrPrioritySorted = Object.entries(cookiebar.settings.cookies ?? {}).sort(([,a],[,b]) => b.priority - a.priority);
            // ES6 Object.fromEntries implementation with prefix on keys to keep priority order
            cookiebar.settings.cookies = Array.from(arrPrioritySorted).reduce((acc, [k, v]) => Object.assign(acc, {[`_${k}`]: v}), {});
        }

        const validateCookies = function(arrCookies, deleteCookies){
            let arrDelete = [];

            for(let cookieId in cookiebar.settings.cookies){
                const intCookieId = parseInt(cookieId.replace('_',''));

                if(!cookiebar.settings.cookies.hasOwnProperty(cookieId)){
                    continue;
                }

                let previousState = !!cookiebar.settings.cookies[cookieId].confirmed;
                let currentState = arrCookies.includes(intCookieId);
                let deleteCookie = previousState !== currentState && !currentState;

                cookiebar.settings.cookies[cookieId].confirmed = currentState;

                if(true === deleteCookies && deleteCookie){
                    let token = cookiebar.settings.cookies[cookieId].token;

                    if(null !== token){
                        token.forEach(function(token){
                            arrDelete.push(token);
                        });
                    }
                }
            }

            if(true === deleteCookies && arrDelete.length){
                let request = new XMLHttpRequest();
                    request.open('POST', '/cookiebar/delete', true);
                    request.send(serialize({tokens: arrDelete}));
            }
        };

        const setConfigs = function(){
            let configId;
            for(configId in cookiebar.settings.configs){

                if(!cookiebar.settings.configs.hasOwnProperty(configId)){
                    continue;
                }

                let config = cookiebar.settings.configs[ configId ];
                let confirmed = checkCookieConfirmation(config.cookies);

                if(null !== config.resources){
                    config.resources.forEach(function(resource, index){
                        // 1. load script only if one of the cookies was confirmed
                        // 2. load script only if one of the cookies was not confirmed
                        // 3. load script always
                        if(
                            (resource.mode === 1 && confirmed) ||
                            (resource.mode === 2 && !confirmed) ||
                            (resource.mode === 3)
                        ){
                            if(cache(getChacheToken(config, index), 'config_resource')){
                                return;
                            }

                            addResource(resource);
                        }
                    });
                }

                if(null !== config.scripts){
                    config.scripts.forEach(function(script, index){
                        // 1. load script only if one of the cookies was confirmed
                        // 2. load script only if one of the cookies was not confirmed
                        // 3. load script always
                        if(
                            (script.mode === 1 === confirmed) ||
                            (script.mode === 2 === !confirmed) ||
                            (script.mode === 3)
                        ){
                            if(cache(getChacheToken(config, index), 'config_script')){
                                return;
                            }

                            addScript(script);
                        }
                    });
                }
            }
        };

        const setScripts = function(){
            let cookieId;
            for(cookieId in cookiebar.settings.cookies){

                if(!cookiebar.settings.cookies.hasOwnProperty(cookieId)){
                    continue;
                }

                let cookie = cookiebar.settings.cookies[ cookieId ];

                if(null !== cookie.resources){
                    cookie.resources.forEach(function(resource, index){
                        // 1. load script if cookie confirmed
                        // 2. load script if cookie not confirmed
                        // 3. load script always
                        if(
                            (resource.mode === 1 && cookie.confirmed) ||
                            (resource.mode === 2 && !cookie.confirmed) ||
                            (resource.mode === 3)
                        ){
                            if(cache(getChacheToken(cookie, index), 'resource')){
                                return;
                            }

                            addResource(resource);
                        }
                    });
                }

                if(null !== cookie.scripts){
                    cookie.scripts.forEach(function(script, index){
                        // 1. load script if cookie confirmed
                        // 2. load script if cookie not confirmed
                        // 3. load script always
                        if(
                            (script.mode === 1 === cookie.confirmed) ||
                            (script.mode === 2 === !cookie.confirmed) ||
                            (script.mode === 3)
                        ){
                            if(cache(getChacheToken(cookie, index), 'script')){
                                return;
                            }

                            addScript(script);
                        }
                    });
                }
            }
        };

        const addScript = function(script){
            if(cookiebar.settings.disableTracking)
            {
                logger('Script execution was stopped.');
                return;
            }

            // Create script tag
            script.script = createScript(script.script);

            // Insert at defined position
            insertAtPosition(script.script, script.position);
        };

        const addResource = function(resource){
            if(cookiebar.settings.disableTracking)
            {
                logger('Adding a resource was stopped.');
                return;
            }

            // Skip resources that are already available
            try{
                let scripts = document.querySelectorAll('script[src]');
                let host = getHostname(resource.src);
                for (let i = scripts.length; i--;) {
                    if (scripts[i].src.indexOf(host) !== -1 && host !== window.location.host){
                        return false;
                    }
                }
            }catch (e) {}

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

            if(null !== resource.flags && resource.flags.length){
                resource.flags.forEach(function(flag){
                    if(typeof flag === 'object'){
                        script.setAttribute(flag[0], flag[1]);
                    }else{
                        script[flag] = true;
                    }
                });
            }

            document.head.append(script);
        };

        const insertAtPosition = function(strContent, pos){
            switch(pos){
                case 1:
                    // below content body
                    document.body.append(strContent);
                    break;
                case 2:
                    // above content body
                    document.body.prepend(strContent);
                    break;
                case 3:
                    // head
                    document.head.append(strContent);
                    break;
            }
        };

        const checkCookieConfirmation = function(cookies){
            let confirmed = false;
            let cookieId;

            for(cookieId in cookies){

                if(!cookiebar.settings.cookies.hasOwnProperty(cookieId)){
                    continue;
                }

                let cookie = cookiebar.settings.cookies[ cookieId ];

                if(cookie.confirmed){
                    confirmed = true;
                    break;
                }
            }

            return confirmed;
        };

        const cache = function(token, type){
            // Create new cache bag
            if(!cookiebar.cache[type]){
                cookiebar.cache[ type ] = [];
            }

            if(cookiebar.cache[ type ].indexOf(token) !== -1){
                return true;
            }

            cookiebar.cache[ type ].push(token);
            return false;
        };

        const getChacheToken = function(cookie, index){
            return cookie.id  + '' + index;
        }

        const logger = function(message){
            console.info('%cContao Cookiebar:', 'background: #fff09b; color: #222; padding: 3px', '\n' + message)
        }

        const registerTriggerEvents = function(){
            document.querySelectorAll('a.ccb-trigger, strong.ccb-trigger').forEach(function(btn){
                applyTriggerEvent(btn)
            });

            // See #152
            new MutationObserver(function(mutationsList) {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(element) {
                            if (element.matches && element.matches('a.ccb-trigger, strong.ccb-trigger')) {
                                applyTriggerEvent(element)
                            }
                        })
                    }
                }
            }).observe(document, {
                attributes: false,
                childList: true,
                subtree: true
            });
        }

        const applyTriggerEvent = function (el) {
            el.addEventListener('click', function(e){
                e.preventDefault();
                p.show(el.classList.contains('ccb-prefill'));
            });
        }

        const registerEvents = function(){
            let btnToggleCookies = cookiebar.dom.querySelectorAll('[data-toggle-cookies]');
            let btnToggleGroups  = cookiebar.dom.querySelectorAll('[data-toggle-group]');
            let btnAction        = cookiebar.dom.querySelectorAll('[data-save],[data-accept-all],[data-deny-all]');

            if(btnAction.length){
                btnAction.forEach(function(btn){
                    btn.addEventListener('click', save);
                });
            }

            if(btnToggleCookies.length){
                btnToggleCookies.forEach(function(btn){
                    btn.addEventListener('click', toggleCookies);
                });
            }

            if(btnToggleGroups.length){
                btnToggleGroups.forEach(function(btn){
                    btn.addEventListener('click', toggleGroup);
                });
            }
        };

        const registerModule = function(cookieId, callback){
            if(cookiebar.modules.hasOwnProperty('_'+cookieId)){
                cookiebar.modules['_'+cookieId].push(callback);
            }else{
                cookiebar.modules['_'+cookieId] = [callback];
            }
        };

        const callModule = function(cookieId){
            let modules = document.querySelectorAll('.cc-module[data-ccb-id="' + cookieId + '"]');

            if(!!modules){
                modules.forEach(function(module){
                    module.parentNode.removeChild(module);
                });
            }

            cookiebar.modules['_'+cookieId].forEach(function(callback){
                callback();
            });

            delete cookiebar.modules['_'+cookieId];

            restoreCookieStatus();
        };

        const processResourceEvents = function(){
            if(!cookiebar.resourcesEvents.length) {
                return false;
            }

            cookiebar.resourcesEvents.forEach(function(event, index) {
                if(cookiebar.loadedResources.indexOf(event.src) === -1) {
                    return false;
                }

                event.callback();

                delete cookiebar.resourcesEvents[index];
            });
        };

        const unblockIframe = function(cookieId){
            let iframes = document.querySelectorAll('iframe[data-ccb-id="' + cookieId + '"]');

            if(iframes.length){
                iframes.forEach(function(iframe){
                    iframe.src = iframe.src;
                    iframe.removeAttribute('data-ccb-id');
                });
            }

            restoreCookieStatus();
        };

        const restoreCookieStatus = function(force){
            let objStorage = getStorage();
            let cookies = [];

            if(!cookiebar.show && force !== true){
                return;
            }

            if(objStorage.cookies && objStorage.cookies.length){
                cookies = objStorage.cookies;
            }else if(objStorage.version === -1){
                for(let cookieId in cookiebar.settings.cookies){
                    const cid = parseInt(cookieId.replace('_',''));

                    if(cookiebar.settings.cookies[cookieId].checked){
                        cookies.push(cid)
                    }
                }
            }

            if(cookies.length){
                cookies.forEach(function(cookieId, index){
                    let input = cookiebar.dom.querySelector('[id="c' + cookieId + '"]');

                    if(!!input) {
                        input.checked = true;
                    }
                });
            }

            let arrGroupInputs = cookiebar.dom.querySelectorAll('input[name="group[]"]');

            if(!!arrGroupInputs){
                arrGroupInputs.forEach(function(groupInput){
                    if(groupInput.disabled){
                        return;
                    }

                    groupInput.checked = false;
                    groupInput.classList.remove(cookiebar.settings.classes.onGroupSplitSelection);

                    let inputs = groupInput.parentElement.querySelectorAll('input[name="cookie[]"]');
                    let arrGroup = [];

                    if(!!inputs) {
                        inputs.forEach(function(input){
                            if(!input.disabled){
                                arrGroup.push(!!input.checked);
                            }
                        });

                        if(arrGroup.indexOf(false) === -1)
                        {
                            groupInput.checked = true;
                        }
                        else if(arrGroup.indexOf(true) !== -1 && arrGroup.indexOf(false) !== -1){
                            groupInput.classList.add(cookiebar.settings.classes.onGroupSplitSelection);
                        }
                    }
                });
            }
        };

        const isFocusable = function(element) {
            while (element) {
                const style = window.getComputedStyle(element);

                if (style.display === 'none') {
                    return false;
                }

                element = element.parentElement;
            }

            return true;
        };

        const initFocusTrap = function() {
            const focusable = cookiebar.dom.querySelectorAll('a[href]:not([disabled]), button:not([disabled]), input[type="checkbox"]:not([disabled])');

            cookiebar.toggleOpener = cookiebar.dom.querySelector('[data-ft-opener]');
            cookiebar.firstFocus = focusable[0];
            cookiebar.lastFocus = focusable[focusable.length - 1];
        }

        const focusTrap = function(e) {
            if (!(e.key === 'Tab' || e.keyCode === 9))
                return;

            if (document.activeElement === cookiebar.lastFocus && !e.shiftKey) {
                e.preventDefault();
                cookiebar.firstFocus?.focus()
            }

            if (document.activeElement === cookiebar.firstFocus && e.shiftKey) {
                e.preventDefault()
                cookiebar.lastFocus?.focus()
            }

            if (document.activeElement === cookiebar.toggleOpener && !isFocusable(cookiebar.lastFocus) && cookiebar.toggleOpener.ariaExpanded === 'false' && !e.shiftKey) {
                e.preventDefault();
                cookiebar.firstFocus?.focus()
            }
        }

        const inert = function(state) {
            if (cookiebar.settings.blocking) {
                cookiebar.dom?.parentElement.querySelectorAll(':scope >:not(script):not(.contao-cookiebar)')?.forEach(el => {
                    state ? el.setAttribute('inert', '') : el.removeAttribute('inert');
                })
            }

            // Focus the first element when opening the cookiebar
            cookiebar.firstFocus.focus()

            if (state)
                document.addEventListener('keydown', focusTrap);
            else
                document.removeEventListener('keydown', focusTrap)
        }

        // Check for children that are added whilst the page builds (race-condition)
        const registerInertObserver = function() {
            new MutationObserver(list => {
                for (const mutation of list) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(node => {
                            if (
                              cookiebar.show
                              && node.nodeType === Node.ELEMENT_NODE
                              && !node.classList.contains('.contao-cookiebar')
                              && !node.hasAttribute('inert')
                            ) {
                                node.setAttribute('inert', '');
                            }
                        });
                    }
                }
            }).observe(cookiebar.dom, {
                childList: true,
                subtree: false
            });
        }

        const checkVisibility = function(){
            if (cookiebar.show) {
                cookiebar.dom.classList.remove(cookiebar.settings.classes.onSave);
                cookiebar.dom.classList.add(cookiebar.settings.classes.onShow);
                inert(true)
            }
            else {
                cookiebar.dom.classList.remove(cookiebar.settings.classes.onShow);
                inert(false)
            }
        };

        const toggleCookies = function(){
            let state  = this.checked;
            let inputs = this.parentElement.querySelectorAll('input[name="cookie[]"]');

            if(inputs){
                inputs.forEach(function(input, index){
                    if(!input.disabled){
                        input.checked = state;
                    }
                });
            }

            this.classList.remove(cookiebar.settings.classes.onGroupSplitSelection);
        };

        const toggleGroup = function(){
            let state = !this.classList.contains(cookiebar.settings.classes.onGroupToggle);

            this.setAttribute('aria-expanded', state ? 'true' : 'false');

            try{
                let groups = this.parentElement.querySelectorAll(':scope > .toggle-group');

                if(groups){
                    groups.forEach(function(group, index){
                        group.style.display = state ? 'block' : 'none';
                    });
                }
            }catch(err){
                // IE11 Fallback
                let group = this.parentElement.querySelector('[data-toggle-group] ~ .toggle-group');
                group.style.display = state ? 'block' : 'none';
            }

            this.classList.toggle(cookiebar.settings.classes.onGroupToggle);
        };

        const log = function(){
            if(!cookiebar.settings.consentLog)
            {
                return;
            }

            let request = new XMLHttpRequest();

            let parameter = {
                referrer: window.location.pathname,
                configId: cookiebar.settings.configId,
                pageId:   cookiebar.settings.pageId,
                cookies:  getStorage().cookies
            };

            request.open('GET', '/cookiebar/log?' + serialize(parameter), true);
            request.send();
        };

        const polyfill = function(){
            // execute only for ie
            if(!window.document.documentMode)
            {
                return;
            }

            if (window.NodeList && !NodeList.prototype.forEach) {
                NodeList.prototype.forEach = Array.prototype.forEach;
            }

            (function (arr) {
                arr.forEach(function (item) {
                    if (item.hasOwnProperty('append')) {
                        return;
                    }
                    Object.defineProperty(item, 'append', {
                        configurable: true,
                        enumerable: true,
                        writable: true,
                        value: function append() {
                            var argArr = Array.prototype.slice.call(arguments),
                                docFrag = document.createDocumentFragment();

                            argArr.forEach(function (argItem) {
                                var isNode = argItem instanceof Node;
                                docFrag.appendChild(isNode ? argItem : document.createTextNode(String(argItem)));
                            });

                            this.appendChild(docFrag);
                        }
                    });
                });
            })([Element.prototype, Document.prototype, DocumentFragment.prototype]);

            (function (arr) {
                arr.forEach(function (item) {
                    if (item.hasOwnProperty('prepend')) {
                        return;
                    }
                    Object.defineProperty(item, 'prepend', {
                        configurable: true,
                        enumerable: true,
                        writable: true,
                        value: function prepend() {
                            var argArr = Array.prototype.slice.call(arguments),
                                docFrag = document.createDocumentFragment();

                            argArr.forEach(function (argItem) {
                                var isNode = argItem instanceof Node;
                                docFrag.appendChild(isNode ? argItem : document.createTextNode(String(argItem)));
                            });

                            this.insertBefore(docFrag, this.firstChild);
                        }
                    });
                });
            })([Element.prototype, Document.prototype, DocumentFragment.prototype]);

            (function () {
                if ( typeof window.CustomEvent === "function" ) return false;

                function CustomEvent ( event, params ) {
                    params = params || { bubbles: false, cancelable: false, detail: undefined };
                    var evt = document.createEvent('CustomEvent');
                    evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
                    return evt;
                }

                CustomEvent.prototype = window.Event.prototype;
                window.CustomEvent = CustomEvent;
            })();
        };

        /** Helper methods */

        const serialize = function (obj, prefix) {
            let str = [],
                p;
            for (p in obj) {
                if (obj.hasOwnProperty(p)) {
                    let k = prefix ? prefix + "[" + p + "]" : p,
                        v = obj[p];
                    str.push((v !== null && typeof v === "object") ?
                        serialize(v, k) :
                        encodeURIComponent(k) + "=" + encodeURIComponent(v));
                }
            }
            return str.join("&");
        };

        const extend = function () {
            let extended = {};
            let deep = false;
            let i = 0;
            let length = arguments.length;

            if ( Object.prototype.toString.call( arguments[0] ) === '[object Boolean]' ) {
                deep = arguments[0];
                i++;
            }

            let merge = function (obj) {
                for ( let prop in obj ) {
                    if ( Object.prototype.hasOwnProperty.call( obj, prop ) ) {
                        // If deep merge and property is an object, merge properties
                        if ( deep && Object.prototype.toString.call(obj[prop]) === '[object Object]' ) {
                            extended[prop] = extend( true, extended[prop], obj[prop] );
                        } else {
                            extended[prop] = obj[prop];
                        }
                    }
                }
            };

            for ( ; i < length; i++ ) {
                let obj = arguments[i];
                merge(obj);
            }

            return extended;
        };

        const generateToken = function(){
            return cookiebar.settings.token + '_' + cookiebar.settings.configId;
        };

        const createScript = function(html) {
            let script = document.createElement('script');
                script.type = 'text/javascript';
                script.nonce = document.querySelector('script[nonce]')?.nonce ?? null;
                script.innerHTML = html;

            return script;
        };

        const setStorage = function(objStorage){
            localStorage.setItem(generateToken(), JSON.stringify(objStorage));
        };

        const getStorage = function() {
            let objStorage = localStorage.getItem(generateToken());

            if(null === objStorage){
                objStorage = {
                    configId: cookiebar.settings.configId,
                    pageId: cookiebar.settings.pageId,
                    version: -1,
                    saved: -1,
                    cookies: []
                };

                localStorage.setItem(generateToken(), JSON.stringify(objStorage));
            }else{
                objStorage = JSON.parse(objStorage);
            }

            return objStorage;
        };

        const getHostname = function(url){
            let matches = url.match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
            return matches && matches[1];
        };

        const getTime = function(){
            return Math.floor(+new Date()/1000);
        };

        const isPageAllowed = function(){
            return !(cookiebar.settings.currentPageId && cookiebar.settings.excludedPageIds && cookiebar.settings.excludedPageIds.indexOf(cookiebar.settings.currentPageId) !== -1);
        };

        const isTrackingAllowed = function(){
            if(!cookiebar.settings.doNotTrack){
                return true;
            }

            if (window.doNotTrack || navigator.doNotTrack || navigator.msDoNotTrack) {
                return !(window.doNotTrack == "1" || navigator.doNotTrack == "yes" || navigator.doNotTrack == "1" || navigator.msDoNotTrack == "1");
            }

            return true;
        };

        const isExpired = function(time){
            let st = parseInt(time);
            let lt = parseInt(cookiebar.settings.lifetime);

            if(isNaN(st) || st === -1 || lt === 0){
                return false;
            }

            return st + lt < getTime();
        }

        /** Public methods */

        p.get = function(){
            return cookiebar;
        };

        p.getStorage = function(){
            return getStorage();
        };

        p.issetCookie = function(varCookie){
            let cookieId;
            let arrCookies = getStorage();

            if(!arrCookies.cookies){
                return false;
            }

            if(typeof varCookie == 'number'){
                return arrCookies.cookies.indexOf(varCookie) !== -1;
            }

            for(cookieId in cookiebar.settings.cookies){
                if(null !== cookiebar.settings.cookies[cookieId].token && cookiebar.settings.cookies[cookieId].token.indexOf(varCookie) !== -1){
                    return arrCookies.cookies.indexOf(cookiebar.settings.cookies[cookieId].id) !== -1;
                }
            }

            return arrCookies.cookies.indexOf(varCookie.toString()) !== -1;
        };

        p.unblock = function(element, cookieId, url){
            if(element.tagName.toLowerCase() === 'iframe'){
                element.src = url;
            }else if(element.tagName.toLowerCase()){
                window.location.href = url;
            }

            push(cookieId);
            unblockIframe(cookieId);
        };

        p.addModule = function(cookieId, callback, objContent){
            registerModule(cookieId, callback);

            if(p.issetCookie(cookieId)){
                callModule(cookieId);
                return false;
            }

            if(objContent && typeof objContent === 'object' && objContent.selector){
                let container = null;

                if(typeof objContent.selector === 'string'){
                    container = document.querySelector(objContent.selector);
                }else{
                    container = objContent.selector;
                }

                if(!!container){
                    let html = document.createElement("div");
                        html.setAttribute('data-ccb-id', cookieId);
                        html.classList.add('cc-module');

                        if (!!objContent.message) {
                            html.innerHTML = '<p>' + objContent.message + '</p>';
                        }

                    if(typeof objContent.button === 'object' && true === objContent.button.show){
                        var btn = document.createElement("button");
                            btn.innerHTML = objContent.button.text || cookiebar.settings.texts.acceptAndDisplay;
                            btn.type = objContent.button.type || 'button';

                        if(objContent.button.classes){
                            btn.className = objContent.button.classes;
                        }

                        btn.addEventListener('click', function(){
                            push(cookieId);
                            callModule(cookieId);
                        });

                        html.append(btn);
                    }

                    container.appendChild(html);
                }
            }
        };

        p.onResourceLoaded = function(cookieId, callback){
            if(!cookiebar.settings.cookies.hasOwnProperty(cookieId)) {
                logger.warn(`Cookie ID ${cookieId} does not exists.`)
                return false;
            }

            if(!cookiebar.settings.cookies[cookieId].resources.length) {
                logger.warn(`The cookie ID ${cookieId} does not contain any resources.`)
                return false;
            }

            // Get resource by cookie id
            const resource = cookiebar.settings.cookies[cookieId].resources[0].src;

            // Check if resource already loaded
            if(cookiebar.loadedResources.indexOf(resource) !== -1) {
                callback();
            }else{
                cookiebar.resourcesEvents.push({
                    src: resource,
                    callback: callback
                });
            }
        };

        p.show = function(restore){
            cookiebar.show = true;
            checkVisibility();

            if(!!restore){
                restoreCookieStatus();
            }
        };

        p.hide = function(){
            cookiebar.show = false;
            checkVisibility();
        };

        polyfill();
        init();

        return p;
    };

    return Constructor;
})();
