<?php
/**
 *  @author    NS8.com <support@ns8.com>
 *  @copyright 2018 NS8.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once _PS_MODULE_DIR_.'ns8csp/lib/utils.php';

class NS8CSPContainerModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->context = Context::getContext();
    }

    public function init()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::init();
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->path = __PS_BASE_URI__.'modules/ns8csp/';
        $this->context->controller->addJS($this->path.'views/js/page.js');
        $this->context->controller->addJS($this->path.'views/js/toastr.min.js');

        $this->context->controller->addCSS($this->path.'views/css/container.css');
        $this->context->controller->addCSS($this->path.'views/css/toastr.min.css');
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

        $url = $baseUrl.'/';

        if (isset($path)) {
            $url = $baseUrl.$path;
        }

        return $url;
    }

    public function initContent()
    {
        parent::initContent();

        $path = '/';

        //  check for specific path to go to
        if (Tools::getValue('path')) {
            $path = Tools::getValue('path');
        }

        $this->context->smarty->assign(array(
            'url' => $this->getContainerUrl($path),
            'ns8BaseUrl' => $this->websiteBaseUrl(),
            'ps_version' => _PS_VERSION_
        ));

        if (_PS_VERSION_ < '1.7') {
            $this->setTemplate('container1.6.tpl');
        } else {
            $this->setTemplate('module:ns8csp/views/templates/front/container.tpl');
        }
    }
}
