var Cookiebar = {
    presets: {
        token: {
            googleAnalytics: ['_ga','_gat','_gid']
        },
        scripts: {
            googleAnalytics: {
                anonymize_ip: true,
                cookie_domain: 'blog.example.com',
                cookie_expires: 28 * 24 * 60 * 60,
                cookie_prefix: 'example',
                cookie_update: false,
                cookie_flags: "SameSite=None;Secure"
            }
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
    getConfig: function(s){
        return JSON.stringify(Cookiebar.presets.scripts[s], null, "\t");
    },
    getToken: function(s, m){
        var t = Cookiebar.presets.token[s].slice();
        switch (s) {
            case 'googleAnalytics':
                if (!document.getElementById('ctrl_vendorId').value.trim()) {
                    alert(m);
                    return false;
                }
                t[1] = t[1] + '_gtag_' + document.getElementById('ctrl_vendorId').value.split('-').join('_');
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
    }
};
