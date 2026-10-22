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
 *   accepted: the key is extracted from its data-key attribute. An empty
 *   field erases the stored key (no separate "clear" switch since 1.1.2).
 * - Back office follows the standard PrestaShop look: one HelperForm panel,
 *   help box inside it, plain wording for non technical merchants.
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
        $this->version = '1.1.2';
        $this->author = 'ADLX';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '8.99.99'];

        parent::__construct();

        $this->displayName = $this->l('Ahrefs Web Analytics');
        $this->description = $this->l('Adds Ahrefs Web Analytics to every page of your shop, without editing your theme.');
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
            $enabled = (int) Tools::getValue(self::CFG_ENABLED) === 1;
            $newKey = trim((string) Tools::getValue(self::CFG_KEY));

            // An empty field erases the key; a non empty one must be valid.
            $validKey = ($newKey !== '') ? $this->normalizeKey($newKey) : '';

            if ($validKey === false) {
                // Nothing is saved: a wrong key must not silently replace a working one.
                $output .= $this->displayError($this->l('This does not look like an Ahrefs key. Paste the installation code given by Ahrefs, or only the key it contains (about 22 letters and digits).'));
            } else {
                Configuration::updateValue(self::CFG_ENABLED, $enabled ? 1 : 0);
                Configuration::updateValue(self::CFG_KEY, $validKey);

                $output .= $this->displayConfirmation($this->l('Settings saved.'));
                if ($enabled && $validKey === '') {
                    $output .= $this->displayWarning($this->l('Tracking is switched on, but no key is saved: Ahrefs receives nothing until you paste your key.'));
                }
            }
        }

        return $output . $this->renderForm();
    }

    /**
     * Help box shown inside the settings panel (HelperForm "description",
     * rendered as alert alert-info by the core template).
     * l() already returns HTML-escaped strings, safe to concatenate.
     */
    protected function renderHelp()
    {
        $enabled = (int) Configuration::get(self::CFG_ENABLED) === 1;
        $key = (string) Configuration::get(self::CFG_KEY);
        $hasKey = ($key !== '');

        if ($enabled && $hasKey) {
            $status = '<span class="label label-success">' . $this->l('Active: Ahrefs receives your visits') . '</span>';
        } elseif ($enabled) {
            $status = '<span class="label label-warning">' . $this->l('Switched on, but no key saved') . '</span>';
        } else {
            $status = '<span class="label label-default">' . $this->l('Off') . '</span>';
        }

        return '<p><strong>' . $this->l('How to set up Ahrefs Web Analytics') . '</strong></p>'
            . '<ol>'
            . '<li>' . $this->l('Create a free Ahrefs Webmaster Tools account, if you do not have one yet.') . '</li>'
            . '<li>' . $this->l('In Ahrefs, open Web Analytics, add your shop and copy the installation code.') . '</li>'
            . '<li>' . $this->l('Paste it in the field below, switch tracking on and click Save.') . '</li>'
            . '<li>' . $this->l('Back in Ahrefs, click Verify installation.') . '</li>'
            . '</ol>'
            . '<p><a class="btn btn-default" href="https://ahrefs.com/webmaster-tools" target="_blank" rel="noopener noreferrer">'
            . '<i class="icon-external-link"></i> ' . $this->l('Open Ahrefs Webmaster Tools') . '</a></p>'
            . '<p style="margin-bottom:0;"><strong>' . $this->l('Current status:') . '</strong> ' . $status . '</p>';
    }

    protected function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Ahrefs Web Analytics settings'),
                    'icon' => 'icon-cogs',
                ],
                'description' => $this->renderHelp(),
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
                        'desc' => $this->l('When switched off, nothing is added to your pages.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Ahrefs key'),
                        'name' => self::CFG_KEY,
                        'required' => false,
                        'desc' => $this->l('Paste the installation code from Ahrefs, or only the key it contains. To remove the key, empty this field and click Save.'),
                        'hint' => $this->l('Example format: OIsSv/REMT+oZHSA8x4AqA'),
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
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->bootstrap = true;

        $helper->fields_value[self::CFG_ENABLED] = (int) Configuration::get(self::CFG_ENABLED);
        $helper->fields_value[self::CFG_KEY] = (string) Configuration::get(self::CFG_KEY);

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
