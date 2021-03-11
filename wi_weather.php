<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Wi_weather extends Module
{
    protected $config_form = false;
    protected $providers = array(
        'openweathermap.org' => array(
            'current' => 'api.openweathermap.org/data/2.5/weather?q={city}&appid={key}&units=metric',
        ),
        'weatherapi.com' => array(
            'current' => 'api.weatherapi.com/v1/current.json?key={key}&q={city}&units=metric',
        ),
    );

    public function __construct()
    {
        $this->name = 'wi_weather';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Jesús Abades';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Webimpacto Weather');
        $this->description = $this->l('Shows a weather block in your site.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('WI_WEATHER_ENABLED', '0');
        Configuration::updateValue('WI_WEATHER_PROVIDER', '');
        Configuration::updateValue('WI_WEATHER_KEY', '');
        Configuration::updateValue('WI_WEATHER_CITY', '');
        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('displayNav') &&
        $this->registerHook('displayNav1');
    }

    public function uninstall()
    {
        Configuration::deleteByName('WI_WEATHER_ENABLED');
        Configuration::deleteByName('WI_WEATHER_PROVIDER');
        Configuration::deleteByName('WI_WEATHER_KEY');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitWi_weatherModule')) == true) {
            $this->postProcess();
        }
        switch (Tools::getValue('tab_sec')) {
            case 'help':
                $html = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/help.tpl');
                break;
            default:
                $html = $this->renderForm();
                break;
        }

        $params = array(
            'wi_welcome' => array(
                'module_dir' => $this->_path,
                'module_name' => $this->name,
                'base_url' => _MODULE_DIR_ . $this->name . '/',
                'iso_code' => $this->context->language->iso_code,
                'menu' => $this->getMenu(),
                'html' => $html,
                'errors' => empty($this->errors) ? array() : $this->errors,
            ),
        );

        $this->context->smarty->assign($params);

        $header = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/header.tpl');
        $body = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/body.tpl');
        $footer = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/footer.tpl');

        return $header . $body . $footer;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWi_weatherModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getMenu()
    {
        $tab = Tools::getValue('tab_sec');
        $tab_link = $this->context->link->getAdminLink('AdminModules', true)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&tab_sec=';
        return array(
            array(
                'label' => $this->l('Configure Weather API'),
                'link' => $tab_link . 'edit',
                'active' => ($tab == 'edit' || empty($tab) ? 1 : 0),
            ),
            array(
                'label' => $this->l('Help'),
                'link' => $tab_link . 'help',
                'active' => ($tab == 'help' ? 1 : 0),
            ),
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 6,
                        'type' => 'select',
                        'required' => true,
                        'label' => $this->l('Weather provider'),
                        'desc' => $this->l('Select your preferred Weather provider'),
                        'name' => 'WI_WEATHER_PROVIDER',
                        'options' => array(
                            'query' => $this->getProvidersAsOptions(),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->l('Provider API key'),
                        'desc' => $this->l('Enter your provider API key. Must be obtained from your provider account.'),
                        'name' => 'WI_WEATHER_KEY',
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'label' => $this->l('City for testing'),
                        'desc' => $this->l('Enter a city, only for test purpose.'),
                        'name' => 'WI_WEATHER_CITY',
                    ),
                    array(
                        'col' => 6,
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'WI_WEATHER_ENABLED',
                        'desc' => $this->l('Enable the weather block in your home.'),
                        'values' => array(
                            array('value' => 1, 'name' => $this->l('Yes')),
                            array('value' => 0, 'name' => $this->l('No')),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getProvidersAsOptions()
    {
        $providers = array(
            array(
                'id' => '',
                'name' => $this->l('Select your preferred provider'),
            ),
        );
        foreach ($this->providers as $provider => $config) {
            $providers[] = array(
                'id' => $provider,
                'name' => $provider,
            );
        }
        return $providers;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $formValues = array(
            'WI_WEATHER_ENABLED' => Configuration::get('WI_WEATHER_ENABLED', ''),
            'WI_WEATHER_PROVIDER' => Configuration::get('WI_WEATHER_PROVIDER', ''),
            'WI_WEATHER_KEY' => Configuration::get('WI_WEATHER_KEY', ''),
            'WI_WEATHER_CITY' => Configuration::get('WI_WEATHER_CITY', ''),
        );
        return $formValues;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::getValue('submitWi_weatherModule')) {
            $formValues = $this->getConfigFormValues();
            foreach (array_keys($formValues) as $key) {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    protected function getIp()
    {
        return Tools::getRemoteAddr();
    }

    protected function getCity($lang = null)
    {
        if (!empty(Configuration::get('WI_WEATHER_CITY'))) {
            return Configuration::get('WI_WEATHER_CITY');
        }
        $lang = empty($lang) ? 'en' : Tools::strtolower($lang);
        $ip = $this->getIp();
        $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);
        try {
            $record = $reader->city($ip);
        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            $record = null;
        }
        return empty($record->city->names['en'])
            ? null
            : $record->city->names['en'];
    }

    protected function getUrl()
    {        
        $provider = Configuration::get('WI_WEATHER_PROVIDER');
        $key = Configuration::get('WI_WEATHER_KEY');
        if (!empty($provider) && !empty($key)) {
            $city = urlencode($this->getCity());
            return Tools::getShopProtocol() . preg_replace(
                array('/{city}/', '/{key}/'),
                array($city, $key),
                $this->providers[$provider]['current']
            );
        }
        return null;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name || Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $language_code = $this->context->language->language_code;
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            Media::addJsDef(
                array(
                    'wi_weather_provider' => Configuration::get('WI_WEATHER_PROVIDER'),
                    'wi_weather_url' => $this->getUrl(),
                    'wi_weather_city' => $this->getCity($language_code)
                )
            );
        } else {
            $this->context->smarty->assign(
                array(
                    'wi_weather_provider' => Configuration::get('WI_WEATHER_PROVIDER'),
                    'wi_weather_url' => $this->getUrl(),
                    'wi_weather_city' => $this->getCity($language_code)
                )
            );

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->name
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'templates'
                . DIRECTORY_SEPARATOR . 'front'
                . DIRECTORY_SEPARATOR . 'javascript.tpl'
            );
        }
    }

    public function hookDisplayNav()
    {
        if (Configuration::get('WI_WEATHER_ENABLED') &&
            Configuration::get('WI_WEATHER_PROVIDER') &&
            Configuration::get('WI_WEATHER_KEY')
        ) {
            return $this->context->smarty->fetch($this->local_path . 'views/templates/hook/displayNav.tpl');
        }
    }

    public function hookDisplayNav1()
    {
        return $this->hookDisplayNav();
    }
}
