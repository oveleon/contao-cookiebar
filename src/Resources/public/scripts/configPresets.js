var Cookiebar = {
    presets: {
        token: {
            googleAnalytics: ['_ga','_gat','_gid'],
            etracker: ['et_allow_cookies']
        },
        scripts: {
            googleAnalytics: {
                anonymize_ip: true,
                cookie_domain: 'blog.example.com',
                cookie_expires: 28 * 24 * 60 * 60,
                cookie_prefix: 'example',
                cookie_update: false,
                cookie_flags: "SameSite=None;Secure"
            },

            googleConsentMode: "gtag('consent', 'update', {\n" +
                               "  'Insert consent mode here': 'granted',\n" +
                               "});",

            tagManager_gcm: "window.dataLayer = window.dataLayer || [];\n" +
                            "function gtag(){dataLayer.push(arguments);}\n\n" +
                            "// Define only global settings here.\n" +
                            "// The \"granted\" values should be set in the cookie itself.\n" +
                            "gtag('consent', 'default', {\n" +
                            "   'ad_storage': 'denied',\n" +
                            "   'analytics_storage': 'denied',\n" +
                            "   'wait_for_update': 500\n" +
                            "});\n\n" +
                            "//gtag('set', 'ads_data_redaction', true);\n" +
                            "//gtag('set', 'url_passthrough', true);\n\n" +
                            "gtag('js', new Date());\n" +
                            "gtag('config', 'Insert container id here');",

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
            googleAnalytics:   "https://developers.google.com/analytics/devguides/collection/ga4/cookies-user-id",
            googleConsentMode: "https://developers.google.com/gtagjs/devguide/consent",
            tagManager:        "https://support.google.com/tagmanager/answer/6102821",
            tagManager_gcm:    "https://developers.google.com/gtagjs/devguide/consent#implementation_example",
            matomo:            "https://developer.matomo.org/guides/tracking-javascript-guide",
            etracker:          "https://www.etracker.com/docs/integration-setup/tracking-code-sdks/tracking-code-integration/parameter-setzen/"
        }
    },
    issetConfig: function(s, e){
        if(!Cookiebar.presets.scripts[s]){
            e.style.display = 'none';
            return false;
        }
    },
    issetToken: function(s, e){
        if(!Cookiebar.presets.token[s]){
            e.style.display = 'none';
            return false;
        }
    },
    issetDocs: function(s, e){
        if(!Cookiebar.presets.docs[s]){
            e.style.display = 'none';
            return false;
        }
    },
    getConfig: function(s){
        if(typeof Cookiebar.presets.scripts[s] === "object"){
            return JSON.stringify(Cookiebar.presets.scripts[s], null, "\t");
        }

        return Cookiebar.presets.scripts[s];
    },
    getToken: function(s, m){
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
                for (let i=0; i<t.length; i++) {
                    while ((match = regex.exec(t[i])) !== null) {
                        if (match.index === regex.lastIndex) {
                            regex.lastIndex++;
                        }
                        match.forEach((match, groupIndex) => {
                            v = document.getElementById(match.substr(1, match.length-2)).value.trim();
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
    getDocs: function(s){
        return Cookiebar.presets.docs[s];
    }
};
