<?php
/**
 *  @author    NS8.com <support@ns8.com>
 *  @copyright 2018 NS8.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once _PS_MODULE_DIR_.'ns8csp/lib/utils.php';

class AdminNS8CSPController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->module = 'ns8csp';
        $this->lang = (!isset($this->context->cookie) || !is_object($this->context->cookie)) ?
            (int)Configuration::get('PS_LANG_DEFAULT') : (int)$this->context->cookie->id_lang;
        parent::__construct();
    }

    public function ajaxProcessCancelOrder()
    {
        $objOrder = new Order((int)Tools::getValue('id'));
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $history->id_order_state = 6;
        $history->add();
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

        $url = '/status';

        if (isset($path)) {
            if (!strpos($path, '?')) {
                $url = $baseUrl.$path.'?accessToken='.Configuration::get('NS8_CSP_TOKEN');
            } else {
                $url = $baseUrl.$path.'&accessToken='.Configuration::get('NS8_CSP_TOKEN');
            }
        }

        $url = $url.'&shopid='.$this->context->shop->id.'&context='
            .Shop::getContext().'&groupid='.Shop::getContextShopGroupID();

        return $url;
    }

    public function renderList()
    {
        //  set to whatever is passed in
        if (Tools::getValue('setApi')) {
            Configuration::updateValue(
                'NS8_CSP_API_BASE_URL',
                Tools::getValue('setApi'),
                null,
                0,
                0
            );
            $this->context->controller->informations[] = 'API set to '.Tools::getValue('setApi');
        }

        if (Tools::getValue('setWebsite')) {
            Configuration::updateValue(
                'NS8_CSP_WEBSITE',
                Tools::getValue('setWebsite'),
                null,
                0,
                0
            );
            $this->context->controller->informations[] = 'Website set to '.Tools::getValue('setWebsite');
        }

        //  default url
        $path = '/status';
        $devId = 'a';

        //  first look for action to take
        if (Tools::getValue('action')) {
            $action = Tools::getValue('action');

            //  check for specific dev id to go to a or b
            if (Tools::getValue('devId')) {
                $devId = Tools::getValue('devId');
            }

            switch ($action) {
                case 'stageMode':
                    // stage mode uses a live api
                    Configuration::deleteByName('NS8_CSP_API_BASE_URL');
                    Configuration::updateValue('NS8_CSP_WEBSITE', 'https://stage-presta-protect.ns8.com', null, 0, 0);
                    $this->context->controller->informations[] = 'Stage mode set';
                    break;
                case 'uiDebugMode':
                    // uiDebug mode uses a live api
                    Configuration::deleteByName('NS8_CSP_API_BASE_URL');
                    Configuration::updateValue('NS8_CSP_WEBSITE', 'https://dev-presta-protect.ngrok.io', null, 0, 0);
                    $this->context->controller->informations[] = 'UI Debug mode set';
                    break;
                case 'debugMode':
                    // debug mode uses a local api with an A / B
                    Configuration::updateValue(
                        'NS8_CSP_API_BASE_URL',
                        'https://dev-'.$devId.'-api.ngrok.io/v1',
                        null,
                        0,
                        0
                    );
                    Configuration::updateValue('NS8_CSP_WEBSITE', 'https://dev-presta-protect.ngrok.io', null, 0, 0);
                    $this->context->controller->informations[] = 'Debug mode set';
                    break;
                case 'productionMode':
                    Configuration::deleteByName('NS8_CSP_API_BASE_URL');
                    Configuration::deleteByName('NS8_CSP_WEBSITE');
                    $this->context->controller->informations[] = 'Production mode set';
                    break;
                case 'clearAuth':
                    Configuration::deleteByName('NS8_CSP_TOKEN');
                    Configuration::deleteByName('NS8_CSP_PROJECT_ID');
                    $this->context->controller->informations[] = 'Authorization cleared';
                    break;
            }
        }

        //  check for specific path to go to
        if (Tools::getValue('path')) {
            $path = Tools::getValue('path');
        }

        $this->context->smarty->assign(array(
            'moduleAdminLink' => $this->context->link->getAdminLink('AdminNS8CSP', true),
            'url' => $this->getContainerUrl($path),
            'orderLink' => $this->context->link->getAdminLink('AdminOrders'),
            'ns8BaseUrl' => $this->websiteBaseUrl(),
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'ns8csp/views/templates/admin/container.tpl');
    }
}
