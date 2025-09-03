export function getStorage(cookiebar) {
    let objStorage = localStorage.getItem(_generateToken(cookiebar));

    if (null === objStorage) {
        objStorage = {
            configId: cookiebar.settings.configId,
            pageId: cookiebar.settings.pageId,
            version: -1,
            saved: -1,
            cookies: [],
        };

        localStorage.setItem(_generateToken(cookiebar), JSON.stringify(objStorage));
    } else {
        objStorage = JSON.parse(objStorage);
    }

    return objStorage;
}

export function setStorage(objStorage, cookiebar) {
    localStorage.setItem(_generateToken(cookiebar), JSON.stringify(objStorage));
}

export function cookieExists(varCookie, cookiebar) {
    let cookieId;
    let arrCookies = getStorage(cookiebar);

    if (!arrCookies.cookies) {
        return false;
    }

    if (typeof varCookie == 'number') {
        return arrCookies.cookies.indexOf(varCookie) !== -1;
    }

    for (cookieId in cookiebar.settings.cookies) {
        if (
            null !== cookiebar.settings.cookies[cookieId].token &&
            cookiebar.settings.cookies[cookieId].token.indexOf(varCookie) !== -1
        ) {
            return arrCookies.cookies.indexOf(cookiebar.settings.cookies[cookieId].id) !== -1;
        }
    }

    return arrCookies.cookies.indexOf(varCookie.toString()) !== -1;
}

function _generateToken(cookiebar) {
    return cookiebar.settings.token + '_' + cookiebar.settings.configId;
}
