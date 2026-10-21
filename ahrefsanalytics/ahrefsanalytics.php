<?php
/**
 * Ahrefs Web Analytics
 *
 * Injects the Ahrefs Web Analytics script in the page head through the
 * displayHeader hook, so the theme is never edited and survives updates.
 *
 * Design notes
 * ------------
 * - English source strings, translated through the standard PrestaShop
 *   system ($this->l() + translations/<iso>.php), so any language can be
 *   added from International > Translations.
 * - The data-key is validated on save. Pasting the whole Ahrefs snippet is
 *   accepted: the key is extracted from its data-key attribute.
 * - Single file on purpose, no namespace, no autoloader.
 *
 * Author : ADLX (https://github.com/iamadlx)
 * License: MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AhrefsAnalytics extends Module
{
    const CFG_KEY = 'AHREFS_ANALYTICS_KEY';
    const CFG_ENABLED = 'AHREFS_ANALYTICS_ENABLED';

    /**
     * Ahrefs data-keys seen in real snippets are 22 base64 characters
     * (16 random bytes, no padding), e.g. "OIsSv/REMT+oZHSA8x4AqA".
     * The check stays a little wider (base64 or base64url, 16 to 64 chars,
     * optional padding) so a future format change does not lock merchants out,
     * while still rejecting spaces, quotes, tags or a truncated paste.
     */
    const KEY_PATTERN = '/^[A-Za-z0-9+\/_-]{16,64}={0,2}$/';

    public function __construct()
    {
        $this->name = 'ahrefsanalytics';
        $this->tab = 'analytics_stats';
        $this->version = '1.1.1';
        $this->author = 'ADLX';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '8.99.99'];

        parent::__construct();

        $this->displayName = $this->l('Ahrefs Web Analytics');
        $this->description = $this->l('Injects the Ahrefs Web Analytics script in the page head via displayHeader (theme-update safe).');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module? Its settings, including the saved key, will be deleted.');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
            && $this->registerHook('displayHeader')
            && Configuration::updateValue(self::CFG_ENABLED, 0)
            && Configuration::updateValue(self::CFG_KEY, '');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName(self::CFG_ENABLED)
            && Configuration::deleteByName(self::CFG_KEY);
    }

    /**
     * Returns the key to store, or false when the input is not a valid key.
     * Accepts either the bare key or the full Ahrefs snippet.
     */
    protected function normalizeKey($raw)
    {
        $raw = trim((string) $raw);
        if (preg_match('/data-key\s*=\s*["\']([^"\']*)["\']/i', $raw, $m)) {
            $raw = trim($m[1]);
        }
        $raw = trim($raw, " \t\n\r\0\x0B\"'");

        return preg_match(self::KEY_PATTERN, $raw) ? $raw : false;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitAhrefsAnalytics')) {
            $enabled = (int) Tools::getValue(self::CFG_ENABLED);
            $clearKey = (int) Tools::getValue('AHREFS_ANALYTICS_CLEAR_KEY');
            $newKey = trim((string) Tools::getValue(self::CFG_KEY));

            $validKey = ($newKey !== '') ? $this->normalizeKey($newKey) : '';

            if ($clearKey !== 1 && $validKey === false) {
                // Nothing is saved: a wrong key must not silently replace a working one.
                $output .= $this->displayError($this->l('This does not look like an Ahrefs data-key. Copy only the value of data-key from the Ahrefs installation snippet (about 22 characters, letters, digits, + and /), or paste the whole snippet.'));
            } else {
                // Enabled state is always persisted, even if the key field is empty
                Configuration::updateValue(self::CFG_ENABLED, $enabled ? 1 : 0);

                if ($clearKey === 1) {
                    Configuration::updateValue(self::CFG_KEY, '');
                } elseif ($validKey !== '') {
                    // Only replace the stored key if the user typed something
                    Configuration::updateValue(self::CFG_KEY, $validKey);
                }

                $output .= $this->displayConfirmation($this->l('Settings saved.'));
            }
        }

        $output .= $this->renderInfoPanel();
        $output .= '<div class="bootstrap">' . $this->renderForm() . '</div>';

        return $output;
    }

    protected function renderInfoPanel()
    {
        $enabled = (int) Configuration::get(self::CFG_ENABLED);
        $key = (string) Configuration::get(self::CFG_KEY);
        $hasKey = ($key !== '');

        $statusLabel = $enabled ? $this->l('Enabled') : $this->l('Disabled');
        $statusClass = $enabled ? 'label-success' : 'label-default';

        // The data-key is public (it is printed in every page), so it is shown in clear
        $shownKey = $hasKey ? Tools::htmlentitiesUTF8($key) : $this->l('No key saved yet');

        $ahrefsUrl = 'https://ahrefs.com/webmaster-tools';

        // l() already returns HTML-escaped strings, safe to concatenate
        $html = '
        <div class="bootstrap" style="margin-top:10px;">
          <div class="alert alert-info" role="alert" style="margin-bottom:15px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
              <img src="' . $this->_path . 'logo.png" alt="Ahrefs" style="width:20px; height:20px;" />
              <strong style="font-size:16px;">' . $this->l('How to set up Ahrefs Web Analytics') . '</strong>
            </div>

            <ol style="margin:0 0 10px 18px;">
              <li>' . $this->l('Create a free Ahrefs Webmaster Tools account (if you don\'t have one).') . '</li>
              <li>' . $this->l('Open Web Analytics settings and copy your data-key value.') . '</li>
              <li>' . $this->l('Paste the key below, enable tracking, and save.') . '</li>
              <li>' . $this->l('Then click "Verify installation" in Ahrefs.') . '</li>
            </ol>

            <p style="margin:0 0 10px 0;">
              <a class="btn btn-default" href="' . $ahrefsUrl . '" target="_blank" rel="noopener noreferrer">
                ' . $this->l('Open Ahrefs Webmaster Tools') . '
              </a>
            </p>

            <p style="margin:0;">
              <strong>' . $this->l('Current status:') . '</strong>
              <span class="label ' . $statusClass . '">' . $statusLabel . '</span>
              &nbsp;&nbsp;|&nbsp;&nbsp;
              <strong>' . $this->l('Saved key:') . '</strong>
              <code>' . $shownKey . '</code>
            </p>
            <p style="margin:6px 0 0 0; font-size:12px; opacity:0.85;">
              ' . $this->l('Note: this module injects the script inside the <head> tag only (displayHeader).') . '
            </p>
          </div>
        </div>';

        return $html;
    }

    protected function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Ahrefs Web Analytics settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable tracking'),
                        'name' => self::CFG_ENABLED,
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'enabled_on', 'value' => 1, 'label' => $this->l('Enabled')],
                            ['id' => 'enabled_off', 'value' => 0, 'label' => $this->l('Disabled')],
                        ],
                        'desc' => $this->l('Turn Ahrefs tracking on or off.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Ahrefs Analytics key'),
                        'name' => self::CFG_KEY,
                        'required' => false,
                        'desc' => $this->l('Paste your Ahrefs data-key value (from the installation snippet). Pasting the whole snippet also works.'),
                        'hint' => $this->l('Example format: OIsSv/REMT+oZHSA8x4AqA'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Clear stored key'),
                        'name' => 'AHREFS_ANALYTICS_CLEAR_KEY',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'clear_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'clear_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                        'desc' => $this->l('If enabled, the currently saved key will be deleted when you save.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAhrefsAnalytics';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->bootstrap = true;

        $helper->fields_value[self::CFG_ENABLED] = (int) Configuration::get(self::CFG_ENABLED);
        $helper->fields_value[self::CFG_KEY] = (string) Configuration::get(self::CFG_KEY);
        $helper->fields_value['AHREFS_ANALYTICS_CLEAR_KEY'] = 0;

        return $helper->generateForm([$fields_form]);
    }

    public function hookDisplayHeader($params)
    {
        if (!(int) Configuration::get(self::CFG_ENABLED)) {
            return '';
        }

        $key = trim((string) Configuration::get(self::CFG_KEY));
        if ($key === '') {
            return '';
        }

        $keyEsc = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');

        return '<script src="https://analytics.ahrefs.com/analytics.js" data-key="' . $keyEsc . '" async></script>';
    }
}
