/**
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @returns {CustomEvent<{visibility, ContaoCookiebar}>}
 */
export function cookiebarInitEvent(cookiebar) {
    return new CustomEvent('cookiebar_init', {
        detail: {
            visibility: cookiebar.show,
            cookiebar,
        },
    });
}

/**
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @returns {CustomEvent<{ContaoCookiebar}>}
 */
export function cookiebarSaveEvent(cookiebar) {
    return new CustomEvent('cookiebar_save', {
        detail: {
            cookiebar,
        },
    });
}
