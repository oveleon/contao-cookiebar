import { serialize } from '../lib/serialize';

export function log(cookiebar, storage) {
    if (!cookiebar.settings.consentLog) {
        return;
    }

    let request = new XMLHttpRequest();

    let parameter = {
        referrer: window.location.pathname,
        configId: cookiebar.settings.configId,
        pageId: cookiebar.settings.pageId,
        cookies: storage.cookies,
    };

    request.open('GET', '/cookiebar/log?' + serialize(parameter), true);
    request.send();
}
