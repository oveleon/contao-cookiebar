var Cookiebar = {
    configs: {
        googleAnalytics: {
            anonymize_ip: true,
            cookie_domain: 'blog.example.com',
            cookie_expires: 28 * 24 * 60 * 60,
            cookie_prefix: 'example',
            cookie_update: false,
            cookie_flags: "SameSite=None;Secure"
        }
    },
    isset: function(s, e){
        if(!Cookiebar.configs[s]){
            e.style.display = 'none';
            return false;
        }
    },
    get: function(s){
        return JSON.stringify(Cookiebar.configs[s], null, "\t");
    }
};
