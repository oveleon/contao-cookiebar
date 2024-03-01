- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module / Content-Element / Insert-tags](MOD_CE_MISC.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)
- [**Content Security Policy**](CONTENT_SECURITY_POLICY.md)

---

# Content Security Policy

As of `Contao 5.3` and `Contao Cookiebar 2.0`, the Contao Cookiebar also supports the Content-Security-Policy feature.

The cookiebar and scripts that are added by it will get the nonce, however any resources have to be set up by yourself.

## Disclaimer

Issues about your content security policy not working due to misconfiguration will be closed without notice!
If you are unsure and need help setting it up, you can consider requesting our paid service or finding help on the
contao community forums.

## What is Content-Security-Policy and how do I enable it?

The following links should help you understand the topic:

- https://content-security-policy.com/
- https://docs.contao.org/manual/en/site-structure/website-root/#content-security-policy
- https://docs.contao.org/dev/framework/csp/

## Documentations for Services

| Service                        | URL                                                                                                                                      |
|--------------------------------|------------------------------------------------------------------------------------------------------------------------------------------|
| Google Maps                    | https://developers.google.com/maps/documentation/javascript/content-security-policy                                                      |
| Google Analytics / Tag Manager | https://developers.google.com/tag-platform/security/guides/csp#google_analytics_4_google_analytics                                       |
| Google Ads conversions         | https://developers.google.com/tag-platform/security/guides/csp#google_ads_conversions                                                    |
| Meta Pixel / Facebook          | https://developers.facebook.com/docs/meta-pixel/advanced                                                                                 |
| Matomo                         | https://matomo.org/faq/general/faq_20904/                                                                                                |
| etracker                       | https://www.etracker.com/docs/integration-setup/tracking-code-sdks/tracking-code-integration/funktion-zweck/#integration-security-header | 
| iFrame                         | https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-src                                              |
