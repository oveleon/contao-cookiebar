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
            version: null,
            cookies: null,
            doNotTrack: false,
            showAlways: false,
            classes: {
                'onSave': 'cc-saved',
                'onShow': 'cc-active',
                'onGroupToggle': 'cc-active'
            }
        };

        const init = function () {
            // Defaults
            cookiebar.settings = extend(true, defaults, settings);
            cookiebar.dom = document.querySelector(cookiebar.settings.selector);
            cookiebar.storage = getCookie();

            cookiebar.scriptCache = [];
            cookiebar.resourceCache = [];
            cookiebar.show = false;

            // Set visibility
            if(
                (parseInt(cookiebar.storage.version) !== parseInt(cookiebar.settings.version) ||
                parseInt(cookiebar.storage.configId) !== parseInt(cookiebar.settings.configId) ||
                cookiebar.settings.showAlways) &&
                !cookiebar.settings.doNotTrack ||
                cookiebar.settings.showAlways){
                cookiebar.show = true;
            }

            // Inputs
            cookiebar.inputs = [];
            cookiebar.dom.querySelectorAll('input[name="cookie[]"]').forEach(function(input, index){
                if(!input.disabled){
                    cookiebar.inputs.push( input );
                }
            });

            // Register events
            registerEvents();

            // Check visibility
            checkVisibility();

            // Load scripts
            if(cookiebar.settings.cookies){
                setScripts(cookiebar.settings.cookies);
            }
        };

        const save = function(e){
            let parameter = {
                referrer: window.location.pathname,
                configId: cookiebar.settings.configId,
                pageId:   cookiebar.settings.pageId,
                version:  cookiebar.settings.version,
                cookies:  []
            };

            let btn = e.currentTarget;
            let mode = 0;

            if(btn.hasAttribute('data-accept-all')){
                mode = 1;
            }else if(btn.hasAttribute('data-deny-all')){
                mode = 2;
            }

            cookiebar.inputs.forEach(function(input, index){
                if(mode === 2){
                    input.checked = false;
                }else if(mode === 1 || input.checked) {
                    parameter.cookies.push(input.value);
                    input.checked = true;
                }
            });

            let request = new XMLHttpRequest();

            request.open('GET', '/cookiebar/save?' + serialize(parameter), true);
            request.onload = function() {
                if (request.status >= 200 && request.status < 400) {
                    setScripts(JSON.parse(request.responseText));
                }else{
                    console.error("Cookies could not be saved.");
                }
            };

            request.send();

            cookiebar.dom.classList.add(cookiebar.settings.classes.onSave);
        };

        const push = function(id){
            let parameter = {
                referrer: window.location.pathname,
                configId: cookiebar.settings.configId,
                pageId:   cookiebar.settings.pageId,
                version:  cookiebar.settings.version
            };

            let request = new XMLHttpRequest();

            request.open('GET', '/cookiebar/push/' + id + '?' + serialize(parameter), true);
            request.onload = function() {
                if (request.status < 200 && request.status >= 400) {
                    console.error("Cookie could not be saved.");
                }
            };

            request.send();
        };

        const setScripts = function(request){
            request.forEach(function(cookie){
                if(null !== cookie.resources){
                    cookie.resources.forEach(function(resource){
                        // 1. load script if cookie confirmed
                        // 2. load script if cookie not confirmed
                        // 3. load script always
                        if(
                            (resource.mode === 1 && cookie.confirmed) ||
                            (resource.mode === 2 && !cookie.confirmed) ||
                            (resource.mode === 3)
                        ){
                            if(cacheCookie(cookie, 'resource')){
                                return;
                            }

                            addResource(resource);
                        }
                    });
                }

                if(null !== cookie.scripts){
                    cookie.scripts.forEach(function(script){
                        // 1. load script if cookie confirmed
                        // 2. load script if cookie not confirmed
                        if(
                            (script.confirmed === cookie.confirmed) ||
                            (!script.confirmed === !cookie.confirmed)
                        ){
                            if(cacheCookie(cookie, 'script')){
                                return;
                            }

                            addScript(script);
                        }
                    });
                }
            });
        };

        const addScript = function(script){
            script.script = createScript(script.script);

            switch(script.position){
                case 1:
                    // below content body
                    document.body.append(script.script);
                    break;
                case 2:
                    // above content body
                    document.body.prepend(script.script);
                    break;
                case 3:
                    // head
                    document.head.append(script.script);
                    break;
            }
        };

        const addResource = function(resource){
            var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = resource.src;

            if(null !== resource.flags && resource.flags.length){
                resource.flags.forEach(function(flag){
                    script[flag] = true;
                });
            }

            document.head.append(script);
        };

        const cacheCookie = function(cookie, type){
            switch(type){
                case 'resource':
                    if(cookiebar.resourceCache.indexOf(cookie.id) !== -1){
                        return true;
                    }

                    cookiebar.resourceCache.push(cookie.id);
                    break;
                case 'script':
                    if(cookiebar.scriptCache.indexOf(cookie.id) !== -1){
                        return true;
                    }

                    cookiebar.scriptCache.push(cookie.id);
                    break;
            }

            return false;
        };

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

        const prefillCookies = function(){
            let arrCookies = getCookie();

            if(!arrCookies.cookies){
                return;
            }

            arrCookies.cookies.forEach(function(cookieId, index){
                let input = cookiebar.dom.querySelector('[id="c' + cookieId + '"]');

                if(!!input) {
                    input.checked = true;
                }
            });
        };

        const checkVisibility = function(){
            if(cookiebar.show) {
                cookiebar.dom.classList.remove(cookiebar.settings.classes.onSave);
                cookiebar.dom.classList.add(cookiebar.settings.classes.onShow);
            }
            else cookiebar.dom.classList.remove(cookiebar.settings.classes.onShow);
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
        };

        const toggleGroup = function(){
            let state = !this.classList.contains(cookiebar.settings.classes.onGroupToggle);
            let groups = this.parentElement.querySelectorAll('.toggle-group');

            if(groups){
                groups.forEach(function(group, index){
                    group.style.display = state ? 'block' : 'none';
                });
            }

            this.classList.toggle(cookiebar.settings.classes.onGroupToggle);
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

        const createScript = function(html) {
            var script = document.createElement('script');
                script.type = 'text/javascript';
                script.innerHTML = html;

            return script;
        };

        const getCookie = function() {
            var name = cookiebar.settings.token + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for(var i = 0; i <ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return JSON.parse(c.substring(name.length, c.length));
                }
            }
            return "";
        };

        /** Public methods */

        p.get = function(){
            return cookiebar;
        };

        p.getCookie = function(){
            return getCookie();
        };

        p.issetCookie = function(id){
            let arrCookies = getCookie();

            if(!arrCookies.cookies){
                return false;
            }

            return arrCookies.cookies.indexOf(id.toString()) !== -1;
        };

        p.unblock = function(element, id, url){
            if(element.tagName.toLowerCase() === 'iframe'){
                element.src = url;
            }else if(element.tagName.toLowerCase()){
                window.location.href = url;
            }

            push(id);
        };

        p.show = function(prefill){
            cookiebar.show = true;
            checkVisibility();

            if(!!prefill){
                prefillCookies();
            }
        };

        p.hide = function(){
            cookiebar.show = false;
            checkVisibility();
        };

        init();

        return p;
    };

    return Constructor;
})();
