<?php
/**
 *  @author    NS8.com <support@ns8.com>
 *  @copyright 2018 NS8.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once _PS_MODULE_DIR_.'ns8csp/lib/utils.php';

class AdminNS8CSPSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = (!isset($this->context->cookie) || !is_object($this->context->cookie)) ?
            (int)Configuration::get('PS_LANG_DEFAULT') : (int)$this->context->cookie->id_lang;
        parent::__construct();
    }

    public function getContainerUrl($path)
    {
        //  the default website location
        $baseUrl = "https://presta-protect.ns8.com";

        //  see if it has been overridden
        if (Configuration::hasKey('NS8_CSP_WEBSITE')) {
            $baseUrl = Configuration::get('NS8_CSP_WEBSITE');
        }

        return $baseUrl.$path.'?accessToken='.Configuration::get('NS8_CSP_TOKEN').'&shopid='.$this->context->shop->id;
    }

    public function display()
    {
        parent::display();
    }

    public function renderList()
    {
        $this->context->smarty->assign(array(
            'url' => $this->getContainerUrl('/settings'),
            'ns8BaseUrl' => $this->websiteBaseUrl()
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'ns8csp/views/templates/admin/container.tpl');
    }
}
