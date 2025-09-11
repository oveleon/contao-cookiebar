/**
 * AddModule method of the Cookiebar
 *
 * @param {int} cookieId
 * @param {addModuleCallback} callback
 * @param {*} objContent
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @returns {boolean}
 *
 * @deprecated Deprecated since Cookiebar 2.3, to be removed in Version 3.0
 *             No replacement has been added yet.
 */
export function add(cookieId, callback, objContent, cookiebar) {
    _registerModule(cookieId, callback, cookiebar);

    if (cookiebar.storage.isset(cookieId)) {
        call(cookieId, cookiebar);
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
                    cookiebar.push(cookieId);
                    call(cookieId, cookiebar);
                });

                html.append(btn);
            }

            container.appendChild(html);
        }
    }
}

/**
 * @param {int} cookieId
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 */
export function call(cookieId, cookiebar) {
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

    cookiebar.updateUserInterface();
}

/**
 * @param {int} cookieId
 * @param {addModuleCallback} callback
 * @param {import('../index.js').ContaoCookiebar} cookiebar
 * @private
 */
function _registerModule(cookieId, callback, cookiebar) {
    if (cookiebar.modules.hasOwnProperty('_' + cookieId)) {
        cookiebar.modules['_' + cookieId].push(callback);
    } else {
        cookiebar.modules['_' + cookieId] = [callback];
    }
}
