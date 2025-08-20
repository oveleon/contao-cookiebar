var Cookiebar = {
    presets: {
        token: {
            googleAnalytics: ['_ga', '_gat', '_gid'],
            etracker: ['et_allow_cookies']
        },
        cookie: {
            scripts: {
                googleAnalytics: {
                    cookie_domain: 'blog.example.com',
                    cookie_expires: 28 * 24 * 60 * 60,
                    cookie_prefix: 'example',
                    cookie_update: false,
                    cookie_flags: "SameSite=None;Secure"
                },

                googleConsentMode: "window.dataLayer = window.dataLayer || [];\n" +
                    "function gtag(){dataLayer.push(arguments);}\n" +
                    "gtag('js', new Date());\n" +
                    "gtag('config', 'Insert container id here');\n" +
                    "gtag('consent', 'update', {\n" +
                    "  'ad_storage': 'granted',\n" +
                    "  'ad_user_data': 'granted',\n" +
                    "  'ad_personalization': 'granted',\n" +
                    "  'analytics_storage': 'granted',\n" +
                    "  'functionality_storage': 'granted',\n" +
                    "  'personalization_storage': 'granted',\n" +
                    "  'security_storage': 'granted',\n" +
                    "});",

                matomo: "_paq.push(['disableCookies']);\n" +
                    "_paq.push(['trackPageView']);\n" +
                    "_paq.push(['enableLinkTracking']);",

                matomoTagManager: "// Static Data Layer Example\n" +
                    "// var_mtm = _mtm || [];\n" +
                    "// _mtm.push({\n" +
                    "//  'pageValue': '0.5',\n" +
                    "//  'pageTitle': document.title\n" +
                    "// });",

                etracker: "// var et_pagename = '';\n" +
                    "// var et_areas = '';\n" +
                    "// var et_tval = 0;\n" +
                    "// var et_tsale = 0;\n" +
                    "// var et_tonr = '';\n" +
                    "// var et_basket = '';"
            },
            docs: {
                googleAnalytics: "https://developers.google.com/analytics/devguides/collection/ga4/cookies-user-id",
                googleConsentMode: "https://developers.google.com/gtagjs/devguide/consent",
                tagManager: "https://support.google.com/tagmanager/answer/6102821",
                matomo: "https://developer.matomo.org/guides/tracking-javascript-guide",
                etracker: "https://www.etracker.com/docs/integration-setup/tracking-code-sdks/tracking-code-integration/parameter-setzen/"
            }
        },
        config: {
            scripts: {
                googleConsentMode: "window.dataLayer = window.dataLayer || [];\n" +
                    "function gtag(){dataLayer.push(arguments);}\n\n" +
                    "// Define only global settings here.\n" +
                    "// The \"granted\" values should be set in the cookie type itself.\n" +
                    "gtag('consent', 'default', {\n" +
                    "   'ad_storage': 'denied',\n" +
                    "   'ad_user_data': 'denied',\n" +
                    "   'ad_personalization': 'denied',\n" +
                    "   'analytics_storage': 'denied',\n" +
                    "   'functionality_storage': 'denied',\n" +
                    "   'personalization_storage': 'denied',\n" +
                    "   'security_storage': 'denied',\n" +
                    "   'wait_for_update': 500\n" +
                    "});",

                tagManager: "window.dataLayer = window.dataLayer || [];\n" +
                    "function gtag(){dataLayer.push(arguments);}\n\n" +
                    "// Define only global settings here.\n" +
                    "// The \"granted\" values should be set in the cookie type itself.\n" +
                    "gtag('consent', 'default', {\n" +
                    "   'ad_storage': 'denied',\n" +
                    "   'ad_user_data': 'denied',\n" +
                    "   'ad_personalization': 'denied',\n" +
                    "   'analytics_storage': 'denied',\n" +
                    "   'functionality_storage': 'denied',\n" +
                    "   'personalization_storage': 'denied',\n" +
                    "   'security_storage': 'denied',\n" +
                    "   'wait_for_update': 500\n" +
                    "});\n\n" +
                    "//gtag('set', 'ads_data_redaction', true);\n" +
                    "//gtag('set', 'url_passthrough', true);\n\n" +
                    "gtag('js', new Date());\n" +
                    "gtag('config', 'Insert container id here');"
            },
            docs: {
                googleConsentMode: "https://developers.google.com/gtagjs/devguide/consent",
                tagManager: "https://developers.google.com/gtagjs/devguide/consent#implementation_example"
            }
        }
    },
    issetToken: function (s, e) {
        if (!Cookiebar.presets.token[s]) {
            e.style.display = 'none';
            return false;
        }
    },
    issetCookieScript: function (s, e) {
        if (!Cookiebar.presets.cookie.scripts[s]) {
            e.style.display = 'none';
            return false;
        }
    },
    issetCookieDocs: function (s, e) {
        if (!Cookiebar.presets.cookie.docs[s]) {
            e.style.display = 'none';
            return false;
        }
    },
    issetConfigScript: function (s, e) {
        if (!Cookiebar.presets.config.scripts[s]) {
            e.style.display = 'none';
            return false;
        }
    },
    issetConfigDocs: function (s, e) {
        if (!Cookiebar.presets.config.docs[s]) {
            e.style.display = 'none';
            return false;
        }
    },
    getToken: function (s, m) {
        var t = Cookiebar.presets.token[s].slice();
        switch (s) {
            case 'googleAnalytics':
                const vendorId = document.getElementById('ctrl_vendorId')?.value;
                if (!vendorId.trim()) {
                    alert(m);
                    return false;
                }
                const vendorParts = vendorId.split('-');
                t[1] = t[1] + '_gtag_' + vendorParts.join('_');
                if ('G' === vendorParts[0]) {
                    t.push('_ga_' + vendorParts[1])
                }
                break;
            default:
                const regex = /\[\w*\]/g;
                for (let i = 0; i < t.length; i++) {
                    while ((match = regex.exec(t[i])) !== null) {
                        if (match.index === regex.lastIndex) {
                            regex.lastIndex++;
                        }
                        match.forEach((match, groupIndex) => {
                            v = document.getElementById(match.substr(1, match.length - 2)).value.trim();
                            if (v) {
                                t = [];
                                alert(m);
                                return false;
                            }
                            t[i] = t[i].replace(match, v);
                        });
                    }
                }
                break;
        }
        return t.join(',');
    },
    getCookieScript: function (s) {
        if (typeof Cookiebar.presets.cookie.scripts[s] === "object") {
            return JSON.stringify(Cookiebar.presets.cookie.scripts[s], null, "\t");
        }

        return Cookiebar.presets.cookie.scripts[s];
    },
    getCookieDocs: function (s) {
        return Cookiebar.presets.cookie.docs[s];
    },
    getConfigScript: function (s) {
        if (typeof Cookiebar.presets.config.scripts[s] === "object") {
            return JSON.stringify(Cookiebar.presets.cookie.scripts[s], null, "\t");
        }

        return Cookiebar.presets.config.scripts[s];
    },
    getConfigDocs: function (s) {
        return Cookiebar.presets.config.docs[s];
    }
};
