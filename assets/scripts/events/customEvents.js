export function cookiebarInitEvent(cookiebar) {
    return new CustomEvent('cookiebar_init', {
        detail: {
            visibility: cookiebar.show,
            cookiebar,
        },
    });
}

export function cookiebarSaveEvent(cookiebar) {
    return new CustomEvent('cookiebar_save', {
        detail: {
            cookiebar,
        },
    });
}
