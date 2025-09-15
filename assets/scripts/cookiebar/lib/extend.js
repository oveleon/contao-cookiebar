/**
 * Allows extending deep nested object configurations
 *
 * @returns {{}}
 */
export function extend() {
    let y = {};
    let dp = 0;
    let i = 0;
    let l = arguments.length;
    if (Object.prototype.toString.call(arguments[0]) === '[object Boolean]') {
        dp = arguments[0];
        i++;
    }
    let merge = function (oj) {
        for (let p in oj) {
            if (Object.prototype.hasOwnProperty.call(oj, p)) {
                if (dp && Object.prototype.toString.call(oj[p]) === '[object Object]') {
                    y[p] = extend(1, y[p], oj[p]);
                } else {
                    y[p] = oj[p];
                }
            }
        }
    };
    for (; i < l; i++) {
        let l = arguments[i];
        merge(l);
    }
    return y;
}
