<?php
/**
 * @author    NS8.com <support@ns8.com>
 * @copyright 2018 NS8.com
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'ns8csp/lib/RESTClient.php';
include_once _PS_MODULE_DIR_ . 'ns8csp/lib/utils.php';

class NS8CSP extends Module
{
    protected $debugMode = 1;
    protected $api = 0;

    public function __construct()
    {
        $this->version = '1.1.111';
        $this->module_key = '82cdbd9e4b3ddbf9beb693d13c720e00';
        $this->name = 'ns8csp';
        //$this->controllers = array('container');
        $this->tab = 'analytics_stats';
        $this->author = 'NS8';
        $this->need_instance = 1;  // need this for admin tab
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('NS8 Protect™');
        $this->description = $this->l('Protect your shop from order fraud, advertising fraud, and poor performance.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall NS8 Protect? '
            . 'You will lose all the data related to this module.');

        $this->api = new RestClient(array(
            'base_url' => $this->apiBaseUrl()
        ));
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!$this->installTab() ||
            !parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('actionAdminControllerSetMedia') ||
            !$this->registerHook('displayBackOfficeHeader') ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('adminOrder') ||
            !$this->registerHook('actionOrderStatusPostUpdate') ||
            !$this->hostInstall()) {
            return false;
        }

        return true;
    }

    public function hookActionOrderStatusPostUpdate($params)
    {

        //  trap all errors - do not return an error in this routine so order processing is not affected
        try {
            $data = array(
                "accessToken" => Configuration::get('NS8_CSP_TOKEN'),
                "params" => $params,
                "shop" => $this->context->shop,
                "prestaVersion" => _PS_VERSION_,
                "moduleVersion" => $this->version
            );

            $result = $this->api->post("protect/presta/orders/status", $data);
            $response = json_decode($result->response);

            if (!isset($response) || !isset($response->code)) {
                $this->logError('hookActionOrderStatusPostUpdate', 'No response from API');
            } elseif ($response->code != 200) {
                $this->logError('hookActionOrderStatusPostUpdate', 'Error from API - code ' . $response->code
                    . ', ' . $response->message);
            }
        } catch (Exception $e) {
            $this->logError('hookActionOrderStatusPostUpdate', $e->getMessage());
        } finally {
            return true;
        }
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        if (_PS_VERSION_ < '1.7') {
            $this->context->controller->addCSS($this->_path . '/views/css/menuTabIcon.css');
        }
    }

    public function hookAdminOrder($params)
    {

        //  trap all errors - do not return an error in this routine so order processing is not affected
        try {
            $link = $this->context->link->getAdminLink('AdminNS8CSP', true) . '&path='
                . urlencode('/order-detail?order=' . $params['id_order']);

            $data = array(
                "accessToken" => Configuration::get('NS8_CSP_TOKEN'),
                "link" => $link,
                "prestaVersion" => _PS_VERSION_,
                "moduleVersion" => $this->version
            );

            //  get the order risk data
            $result = $this->api->get("protect/presta/orders/" . $params['id_order'] . "/risk", $data);
            $response = json_decode($result->response);

            if (!isset($response) || !isset($response->code)) {
                $this->logError('hookAdminOrder', 'No response from API');
            } elseif ($response->code != 200 && $response->code != 404) {
                $this->logError('hookAdminOrder', 'Error from API - code '
                    . $response->code . ', ' . $response->message);
            } elseif (isset($response->data)) {
                if (isset($response->data->errorHtml)) {
                    $this->context->controller->errors[] = $response->data->errorHtml;
                }

                if (isset($response->data->warningHtml)) {
                    $this->context->controller->warnings[] = $response->data->warningHtml;
                }

                if (isset($response->data->infoHtml)) {
                    $this->context->controller->informations[] = $response->data->infoHtml;
                }

                if (isset($response->data->confirmationHtml)) {
                    $this->context->controller->confirmations[] = $response->data->confirmationHtml;
                }
            }
        } catch (Exception $e) {
            $this->logError('hookAdminOrder', $e->getMessage());
        } finally {
            return true;
        }
    }

    public function hookActionValidateOrder($params)
    {

        //  trap all errors - do not return an error in this routine so order processing is not affected
        try {
            $order_payment = new OrderPayment();
            $payments = $order_payment->getByOrderReference($params['order']->reference);

            $billingAddress = new Address((int)$params['order']->id_address_invoice);
            $shippingAddress = new Address((int)$params['order']->id_address_delivery);

            if (isset($billingAddress)) {
                $billingAddress->country_code = Country::getIsoById($billingAddress->id_country);
            }

            if (isset($shippingAddress)) {
                $shippingAddress->country_code = Country::getIsoById($shippingAddress->id_country);
            }

            $userId = null;
            $ua = null;
            $language = null;
            $cookieName = '__na_u_' . Configuration::get('NS8_CSP_PROJECT_ID');

            if (isset($this->context->$cookieName)) {
                $userId = $this->context->cookie->$cookieName;
            }

            if (isset($_SERVER["HTTP_USER_AGENT"])) {
                $ua = $_SERVER["HTTP_USER_AGENT"];
            }

            if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
                $language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
            }

            $data = array(
                "userId" => $userId,
                "accessToken" => Configuration::get('NS8_CSP_TOKEN'),
                "ip" => Utils::remoteAddress(),
                "ua" => $ua,
                "language" => $language,
                "params" => $params,
                "billingAddress" => $billingAddress,
                "shippingAddress" => $shippingAddress,
                "payments" => $payments,
                "shop" => $this->context->shop,
                "prestaVersion" => _PS_VERSION_,
                "moduleVersion" => $this->version
            );

            $result = $this->api->post("protect/presta/orders", $data);
            $response = json_decode($result->response);

            if (!isset($response) || !isset($response->code)) {
                $this->logError('hookActionValidateOrder', 'No response from API');
            } elseif ($response->code != 200) {
                $this->logError('hookActionValidateOrder', 'Error from API - code ' . $response->code
                    . ', ' . $response->message);
            }
        } catch (Exception $e) {
            $this->logError('hookActionValidateOrder', $e->getMessage());
        } finally {
            return true;
        }
    }

    /**
     * Return the API's base url
     */
    public function apiBaseUrl()
    {
        //  the default api endpoint
        $baseUrl = "https://api.ns8.com/v1";

        //  see if it has been overriden
        if (Configuration::hasKey('NS8_CSP_API_BASE_URL')) {
            $baseUrl = Configuration::get('NS8_CSP_API_BASE_URL');
        }

        return $baseUrl;
    }

    public function websiteBaseUrl()
    {
        //  the default website location
        $baseUrl = "https://presta-protect.ns8.com";

        //  see if it has been overridden
        if (Configuration::hasKey('NS8_CSP_WEBSITE')) {
            $baseUrl = Configuration::get('NS8_CSP_WEBSITE');
        }

        return $baseUrl;
    }

    public function getContainerUrl($path)
    {
        //  the default website location
        $baseUrl = $this->websiteBaseUrl();
        return $baseUrl . $path
            . '?accessToken=' . Configuration::get('NS8_CSP_TOKEN')
            . '&shopid=' . $this->context->shop->id;
    }

    public function installTab()
    {

        $this->uninstallTabs();

        // Install Tabs
        $tab = new Tab();
        $tab->class_name = 'AdminNS8CSP';
        $tab->active = 1;
        $tab->name = array();
        $tab->position = 1;

        // Need a foreach for the language
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'NS8 Protect';
        }

        //  in 1.6, set to main menu (0) - in 1.7 set to AdminTools
        if (_PS_VERSION_ < '1.7') {
            $tab->id_parent = 0;
        } else {
            $tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
            $tab->icon = 'security';
        }

        $tab->module = $this->name;
        $tab->save();

        return true;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        $this->context->controller->addJS($this->_path . 'views/js/page.js');
        $this->context->controller->addJS($this->_path . 'views/js/toastr.min.js');

        $this->context->controller->addCSS($this->websiteBaseUrl() . '/css/admin.css');

        $this->context->controller->addCSS($this->_path . 'views/css/container.css');
        $this->context->controller->addCSS($this->_path . 'views/css/toastr.min.css');
    }

    //  install app on host
    public function hostInstall()
    {
        $params = null;

        try {
            $this->logInfo('hostInstall', 'Installing NS8 Protect app...');

            $accessToken = null;

            if (Configuration::hasKey('NS8_CSP_TOKEN')) {
                $accessToken = Configuration::get('NS8_CSP_TOKEN');
            }

            $tbVersion = null;

            if (defined('_TB_VERSION_')) {
                $tbVersion = _TB_VERSION_;
            }

            $params = array(
                "shops" => Shop::getShops(true),
                "email" => Configuration::get('PS_SHOP_EMAIL'),
                "timezone" => Configuration::get('PS_TIMEZONE'),
                "accessToken" => $accessToken,
                "prestaVersion" => _PS_VERSION_,
                "moduleVersion" => $this->version,
                "tbVersion" => $tbVersion
            );

            $result = $this->api->post("protect/presta/install", $params, null, 30);
            $response = json_decode($result->response);

            if (!isset($response) || !isset($response->code)) {
                $this->context->controller->errors[] = 'Unable to create account.  '
                    . 'Please uninstall and re-install the app.';
                return false;
            }

            if ($response->code != 200) {
                $this->logError('hostInstall', $response->message, $params);
                $this->context->controller->errors[] = $response->message;
                return false;
            }

            Configuration::updateValue('NS8_CSP_PROJECT_ID', $response->data->projectId);
            Configuration::updateValue('NS8_CSP_TOKEN', $response->data->accessToken);

            return true;
        } catch (Exception $e) {
            $this->logError('hostInstall', $e->getMessage(), $params);
            return false;
        }
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallTabs() || !$this->hostUninstall()) {
            return false;
        }

        return true;
    }

    public function uninstallTabs()
    {

        // Uninstall Tabs
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        return true;
    }

    //  install app on host
    public function hostUninstall()
    {
        $this->logInfo('hostUninstall', 'Uninstalling NS8 Protect app...');

        $params = array(
            "accessToken" => Configuration::get('NS8_CSP_TOKEN'),
            "shop" => $this->context->shop,
            "prestaVersion" => _PS_VERSION_,
            "moduleVersion" => $this->version
        );

        $this->api->post("protect/presta/uninstall", $params);

        return true;
    }

    //  add script to front office pages
    public function hookHeader()
    {

        $this->context->smarty->assign(array(
            "projectId" => Configuration::get('NS8_CSP_PROJECT_ID'),
            "shopId" => $this->context->shop->id
        ));

        return $this->display(__FILE__, 'views/templates/admin/script.tpl');
    }

    /**
     * back office module configuration page content
     */
    public function getContent()
    {

        $this->context->smarty->assign(array(
            'url' => $this->getContainerUrl('/welcome'),
            'ns8BaseUrl' => $this->websiteBaseUrl()
        ));

        return $this->display(__FILE__, 'views/templates/admin/container.tpl');
    }

    protected function logError($function, $log, $data = null)
    {
        $this->log($function, $log, $data, 1);
    }

    protected function logInfo($function, $log, $data = null)
    {
        $this->log($function, $log, $data, 3);
    }

    protected function log($function, $log, $data = null, $level = 1)
    {
        try {
            //  log to Presta
            PrestaShopLogger::addLog('ns8csp.' . $function . ': ' . $log, 1, null, null, null, true);

            //  log to the cloud
            $data = array(
                'level' => $level,
                'category' => 'presta ns8csp',
                'data' => array(
                    'platform' => 'presta',
                    'projectId' => Configuration::get('NS8_CSP_PROJECT_ID'),
                    'shop' => $this->context->shop,
                    'function' => $function,
                    'message' => $log,
                    'data' => $data,
                    "prestaVersion" => _PS_VERSION_,
                    "moduleVersion" => $this->version
                )
            );

            $this->api->post("ops/logs", $data, null, 2);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('ns8csp.log: ' . $e->getMessage(), 1, null, null, null, true);
        } finally {
            return true;
        }
    }
}
