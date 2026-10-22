# Changelog

## 1.1.2 (2026-10-22)
- Back Office follows the standard PrestaShop layout: the setup help, the "Open Ahrefs Webmaster Tools" button and the current status now sit inside the settings panel, with a single icon.
- The "Clear stored key" switch is removed: empty the key field and save to erase the key. A non empty key is still validated, and an invalid one is not saved.
- Warning when tracking is on but no key is saved.
- Plain wording for merchants, technical terms removed from the page and the module description.
- `upgrade/upgrade-1.1.2.php` removes the old switch setting if a shop ever stored it. Configuration keys are unchanged.

## 1.1.1 (2026-10-21)
- Back Office strings now use the standard PrestaShop translation system (`$this->l()`): English by default, translatable from International > Translations. French shipped in `translations/fr.php`.
- The Ahrefs key is validated on save; an invalid key shows an error and is not stored. Pasting the whole Ahrefs snippet also works (the data-key is extracted).
- Uninstall now asks for confirmation.
- Compatibility declared up to PrestaShop 8.99.99 (was: any installed version).
- Module attributes set before `parent::__construct()`, as in the official example.
- Multistore: installation runs in "all shops" context.
- Added the missing `index.php` in `translations/`.

## 1.1.0
- Cleaner info box styling (no duplicated icons)
- Back Office UI in English & French (built-in)
- Module name simplified (removed "(Head)")
- Shows saved key in clear (as requested)
- Toggle state no longer resets when key field is empty
- Injects script via `displayHeader` only (head-only)

## 1.0.0
- Initial release
