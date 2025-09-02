import { getStorage } from '../store/store';

export function syncPreferences(cookiebar, force) {
    if (!cookiebar.show && force !== true) {
        return;
    }

    let objStorage = getStorage(cookiebar);
    let cookies = [];

    if (objStorage.cookies && objStorage.cookies.length) {
        cookies = objStorage.cookies;
    } else if (objStorage.version === -1) {
        for (let cookieId in cookiebar.settings.cookies) {
            const cid = parseInt(cookieId.replace('_', ''));

            if (cookiebar.settings.cookies[cookieId].checked) {
                cookies.push(cid);
            }
        }
    }

    if (cookies.length) {
        cookies.forEach(function (cookieId, index) {
            let input = cookiebar.dom.querySelector('[id="c' + cookieId + '"]');

            if (!!input) {
                input.checked = true;
            }
        });
    }

    let arrGroupInputs = cookiebar.dom.querySelectorAll('input[name="group[]"]');

    if (!!arrGroupInputs) {
        arrGroupInputs.forEach(function (groupInput) {
            if (groupInput.disabled) {
                return;
            }

            groupInput.checked = false;
            groupInput.classList.remove(cookiebar.settings.classes.onGroupSplitSelection);

            let inputs = groupInput.parentElement.querySelectorAll('input[name="cookie[]"]');
            let arrGroup = [];

            if (!!inputs) {
                inputs.forEach(function (input) {
                    if (!input.disabled) {
                        arrGroup.push(!!input.checked);
                    }
                });

                if (arrGroup.indexOf(false) === -1) {
                    groupInput.checked = true;
                } else if (arrGroup.indexOf(true) !== -1 && arrGroup.indexOf(false) !== -1) {
                    groupInput.classList.add(cookiebar.settings.classes.onGroupSplitSelection);
                }
            }
        });
    }
}
