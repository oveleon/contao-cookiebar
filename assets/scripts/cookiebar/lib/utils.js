/**
 * @param {HTMLScriptElement} html
 * @returns {HTMLScriptElement}
 */
export function createScript(html) {
    let script = document.createElement('script');
    script.type = 'text/javascript';
    script.nonce = document.querySelector('script[nonce]')?.nonce ?? null;
    script.innerHTML = html;

    return script;
}

/**
 * @param {string} url
 * @returns {*}
 */
export function getHostname(url) {
    let matches = url.match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
    return matches && matches[1];
}

/**
 * @returns {number}
 */
export function getTime() {
    return Math.floor(+new Date() / 1000);
}

/**
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @returns {boolean}
 */
export function isPageAllowed(cookiebar) {
    return !(
        cookiebar.settings.currentPageId &&
        cookiebar.settings.excludedPageIds &&
        cookiebar.settings.excludedPageIds.indexOf(cookiebar.settings.currentPageId) !== -1
    );
}

/**
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @returns {boolean}
 */
export function isTrackingAllowed(cookiebar) {
    if (!cookiebar.settings.doNotTrack) {
        return true;
    }

    if (window.doNotTrack || navigator.doNotTrack || navigator.msDoNotTrack) {
        return !(
            window.doNotTrack == '1' ||
            navigator.doNotTrack == 'yes' ||
            navigator.doNotTrack == '1' ||
            navigator.msDoNotTrack == '1'
        );
    }

    return true;
}

/**
 * @param {number} time
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @returns {boolean}
 */
export function isExpired(time, cookiebar) {
    let st = parseInt(time);
    let lt = parseInt(cookiebar.settings.lifetime);

    if (isNaN(st) || st === -1 || lt === 0) {
        return false;
    }

    return st + lt < getTime();
}

/**
 * @param {string} message
 */
export function consoleLog(message) {
    console.info('%cContao Cookiebar:', 'background: #fff09b; color: #222; padding: 3px', '\n' + message);
}

/**
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 */
export function sortCookiesByLoadingOrder(cookiebar) {
    const arrPrioritySorted = Object.entries(cookiebar.settings.cookies ?? {}).sort(
        ([, a], [, b]) => b.priority - a.priority,
    );
    // ES6 Object.fromEntries implementation with prefix on keys to keep priority order
    cookiebar.settings.cookies = Array.from(arrPrioritySorted).reduce(
        (acc, [k, v]) => Object.assign(acc, { [`_${k}`]: v }),
        {},
    );
}

/**
 * @param {int|string} cookieId
 * @param {onResourceLoadedCallback} callback
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @returns {boolean}
 */
export function onResourceLoaded(cookieId, callback, cookiebar) {
    if (!cookiebar.settings.cookies.hasOwnProperty(cookieId)) {
        console.warn(`Cookie ID ${cookieId} does not exists.`);
        return false;
    }

    if (!cookiebar.settings.cookies[cookieId].resources.length) {
        console.warn(`The cookie ID ${cookieId} does not contain any resources.`);
        return false;
    }

    // Get resource by cookie id
    const resource = cookiebar.settings.cookies[cookieId].resources[0].src;

    // Check if resource already loaded
    if (cookiebar.loadedResources.indexOf(resource) !== -1) {
        callback();
    } else {
        cookiebar.resourcesEvents.push({
            src: resource,
            callback: callback,
        });
    }
}

/**
 * @param {HTMLScriptElement} strContent
 * @param {int} pos
 */
export function insertAtPosition(strContent, pos) {
    switch (pos) {
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
}

/**
 * @param {HTMLElement} element
 * @returns {boolean}
 */
export function isFocusable(element) {
    while (element) {
        const style = window.getComputedStyle(element);

        if (style.display === 'none') {
            return false;
        }

        element = element.parentElement;
    }

    return true;
}
