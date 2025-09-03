import { updateUserInterface } from '../components/cookiebar';
import { cookieExists } from '../store/store';

export function add(cookieId, callback, objContent, cookiebar, dom) {
    _registerModule(cookiebar, cookieId, callback);

    if (cookieExists(cookieId, cookiebar)) {
        call(cookiebar, dom, cookieId);
        return false;
    }

    if (objContent && typeof objContent === 'object' && objContent.selector) {
        let container = null;

        if (typeof objContent.selector === 'string') {
            container = document.querySelector(objContent.selector);
        } else {
            container = objContent.selector;
        }

        if (!!container) {
            let html = document.createElement('div');
            html.setAttribute('data-ccb-id', cookieId);
            html.classList.add('cc-module');

            if (!!objContent.message) {
                html.innerHTML = '<p>' + objContent.message + '</p>';
            }

            if (typeof objContent.button === 'object' && true === objContent.button.show) {
                var btn = document.createElement('button');
                btn.innerHTML = objContent.button.text || cookiebar.settings.texts.acceptAndDisplay;
                btn.type = objContent.button.type || 'button';

                if (objContent.button.classes) {
                    btn.className = objContent.button.classes;
                }

                btn.addEventListener('click', function () {
                    // ToDo: find a better alternative
                    cookiebar.push(cookieId);
                    call(cookiebar, dom, cookieId);
                });

                html.append(btn);
            }

            container.appendChild(html);
        }
    }
}

export function call(cookiebar, dom, cookieId) {
    let modules = document.querySelectorAll('.cc-module[data-ccb-id="' + cookieId + '"]');

    if (!!modules) {
        modules.forEach(function (module) {
            module.parentNode.removeChild(module);
        });
    }

    cookiebar.modules['_' + cookieId].forEach(function (callback) {
        callback();
    });

    delete cookiebar.modules['_' + cookieId];

    updateUserInterface(cookiebar, dom);
}

function _registerModule(cookiebar, cookieId, callback) {
    if (cookiebar.modules.hasOwnProperty('_' + cookieId)) {
        cookiebar.modules['_' + cookieId].push(callback);
    } else {
        cookiebar.modules['_' + cookieId] = [callback];
    }
}
