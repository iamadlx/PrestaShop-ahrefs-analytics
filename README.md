<div align="center">

<img src="ahrefsanalytics/logo.png" alt="Ahrefs Web Analytics for PrestaShop" width="96" height="96">

# Ahrefs Web Analytics for PrestaShop

**Free, cookie-less visitor analytics on your store in 2 minutes. No theme edits, no cookie banner needed.**

[![PrestaShop 1.7 to 8.x](https://img.shields.io/badge/PrestaShop-1.7%20%E2%86%92%208.x-df0067)](https://www.prestashop.com)
[![Latest release](https://img.shields.io/github/v/release/iamadlx/PrestaShop-ahrefs-analytics?label=release)](https://github.com/iamadlx/PrestaShop-ahrefs-analytics/releases/latest)
[![Downloads](https://img.shields.io/github/downloads/iamadlx/PrestaShop-ahrefs-analytics/total)](https://github.com/iamadlx/PrestaShop-ahrefs-analytics/releases)
[![License: MIT](https://img.shields.io/badge/license-MIT-green)](LICENSE)

[Download the latest zip](https://github.com/iamadlx/PrestaShop-ahrefs-analytics/releases/latest) · [Install](#install-in-2-minutes) · [FAQ](#faq)

</div>

---

## Why this module

[Ahrefs Web Analytics](https://ahrefs.com/web-analytics) is a free, lightweight analytics tool from the
Ahrefs SEO suite: visitors, pages, sources, countries and devices, without cookies. Installing it on
PrestaShop usually means editing the theme, and the edit disappears at the next theme update.

This module adds the official Ahrefs script in the `<head>` of every page through a standard
PrestaShop hook. Paste your key, switch it on, done.

## What it does

- **Adds the official Ahrefs tracking script** on every front page, via the `displayHeader` hook.
- **No theme edits.** Theme updates cannot remove it.
- **Key checked on save.** An invalid key shows an error instead of silently tracking nothing. You can
  also paste the whole snippet from Ahrefs: the module extracts the key for you.
- **One switch** to pause tracking without losing your key. To remove the key, empty the field and save.
- **Translatable.** Back office in English, with French included; any other language can be translated from **International > Translations**.
- **Lightweight.** No database table, no cron, nothing loaded in the back office outside its own page.

## Install in 2 minutes

1. Download `ahrefsanalytics-x.y.z.zip` from the [latest release](https://github.com/iamadlx/PrestaShop-ahrefs-analytics/releases/latest).
2. Back office: **Modules > Module Manager > Upload a module**, drop the zip.
3. Create a free [Ahrefs Webmaster Tools](https://ahrefs.com/webmaster-tools) account if you do not have one, open **Web Analytics** and copy your key (the `data-key` value, or the whole snippet).
4. In PrestaShop, click **Configure** on the module, paste the key, switch tracking on, save.
5. Clear the cache: **Advanced Parameters > Performance > Clear cache**.
6. Back in Ahrefs, click **Verify installation**.

### Check that it works

View the source of any page (Ctrl+U or Cmd+Option+U) and search for `analytics.ahrefs.com`. Or:

```bash
curl -s https://your-store.com/ | grep -i 'analytics.ahrefs.com'
```

## Compatibility

| PrestaShop | Status |
|---|---|
| 1.7 | supported |
| 8.0 to 8.2 | supported, running in production on 8.2.8 |
| 9.x | not declared yet: the module refuses to install until it has been tested |

Works with any theme that calls the standard `displayHeader` hook (Classic, Hummingbird and the large
majority of commercial themes).

## FAQ

**Do I need a cookie banner for Ahrefs Web Analytics?**
Ahrefs states that Web Analytics does not use cookies and does not require a consent banner by
default. You remain responsible for your own privacy policy: mention the tool there.

**Can I use it next to Google Analytics, Plausible or Matomo?**
Yes. The module only adds the Ahrefs script and does not touch other trackers.

**Does it slow the store down?**
The Ahrefs script is small and loaded asynchronously. The module itself adds no database query.

**Multistore?**
Installation runs in the "all shops" context; the key and switch follow the usual PrestaShop
configuration context.

## Uninstall

Module Manager, then Uninstall. The module removes its configuration values and leaves nothing behind.

## Contributing

Issues and pull requests are welcome. Please describe your PrestaShop version and theme.

## Hosting recommendation (optional)

Looking for a host with **1-click PrestaShop installation**, easy updates and automatic backups?
[Infomaniak](https://www.infomaniak.com/en/create-a-website/cms/prestashop-hosting?utm_term=5f980010e7533) offers a dedicated PrestaShop hosting plan (affiliate link).

## License

[MIT](LICENSE). Copyright (c) 2026 ADLX. Ahrefs is a trademark of Ahrefs Pte. Ltd.; this module is
an independent integration, not affiliated with Ahrefs.

Built and maintained by [ADLX Studio](https://adlx.studio), from Belgium.
More PrestaShop modules on [github.com/iamadlx](https://github.com/iamadlx).
