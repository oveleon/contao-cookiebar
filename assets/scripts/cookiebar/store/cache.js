export function cache(token, type, cookiebar) {
    // Create new cache bag
    if (!cookiebar.cache[type]) {
        cookiebar.cache[type] = [];
    }

    if (cookiebar.cache[type].indexOf(token) !== -1) {
        return true;
    }

    cookiebar.cache[type].push(token);
    return false;
}

export function getToken(cookie, index) {
    return cookie.id + '' + index;
}
