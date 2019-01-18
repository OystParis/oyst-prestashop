<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

require_once dirname(__FILE__).'/autoload.php';

/**
 * Class Oyst
 */
class Oyst extends PaymentModule
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = 'oyst';
        $this->version = '2.0.0-RC29';
        $this->tab = 'payments_gateways';

        parent::__construct();

        $this->author = 'Oyst';
        $this->displayName = $this->l('Oyst - 1Click');
        $this->description = $this->l('Oyst is an online shopping solution allowing users to buy in 1-click on any website, from any page (not only any more through the traditional "cart" page).');
        $this->module_key = '728233ba4101873905adb6b9ec29f28f';
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        // Set Oyst version as define
        if (!defined('_PS_OYST_VERSION_')) {
            define('_PS_OYST_VERSION_', $this->version);
        }

        // If PS 1.6 or greater, we enable bootstrap
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            $this->bootstrap = true;
        }
    }

    public function uninstall()
    {
        $oystDb = new \Oyst\Classes\InstallManager(Db::getInstance(), $this);
        return parent::uninstall() && $oystDb->uninstall();
    }

    public function install()
    {
        $result = parent::install();

        $result &= $this->registerHook('footer');
        $result &= $this->registerHook('displayPaymentReturn');
        $result &= $this->registerHook('adminProductsExtra');
        $result &= $this->registerHook('actionEmailSendBefore');
        $result &= $this->registerHook('actionOrderHistoryAddAfter');
        $result &= $this->registerHook('moduleRoutes');

        // Clear cache
        Cache::clean('Module::getModuleIdByName_oyst');

        $oystDb = new \Oyst\Classes\InstallManager(Db::getInstance(), $this);
        $result &= $oystDb->install();
        return $result;
    }

    public function loadSQLFile($sql_file)
    {
        // Get install SQL file content
        $sql_content = Tools::file_get_contents($sql_file);

        // Replace prefix and store SQL command in array
        $sql_content = str_replace('@PREFIX@', _DB_PREFIX_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);

        // Execute each SQL statement
        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }

        // Return result
        return $result;
    }

    /**
     * Configuration method
     * @return string $html
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitOystConfiguration')) {
            if ($this->saveConfigForm()) {
                $this->context->smarty->assign('result', 'ok');
            }
        }

        $oyst_api_key = \Oyst\Classes\OystAPIKey::getAPIKey();

        $module_dir = _MODULE_DIR_.$this->name.'/';

        $this->context->smarty->assign([
            'module_dir' => $module_dir,
            'oyst_api_key' => $oyst_api_key,
            'oyst_merchant_id' => Configuration::get('OYST_MERCHANT_ID'),
            'oyst_script_tag' => base64_decode(Configuration::get('OYST_SCRIPT_TAG')),
            'oyst_public_endpoints' => Configuration::get('OYST_PUBLIC_ENDPOINTS'),
        ]);

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
        	$this->context->controller->addCSS($module_dir.'views/css/config-1.5.css');
        	$template_name = 'getMerchantConfigure.tpl';
		} else {
        	$template_name = 'getMerchantConfigure.bootstrap.tpl';
		}

        return $this->context->smarty->fetch(__DIR__.'/views/templates/hook/'.$template_name);
    }

    public function saveConfigForm()
    {
        $res = Configuration::updateValue('OYST_MERCHANT_ID', Tools::getValue('oyst_merchant_id'));
        $res &= Configuration::updateValue('OYST_SCRIPT_TAG', base64_encode(Tools::getValue('oyst_script_tag')));
        $res &= Configuration::updateValue('OYST_PUBLIC_ENDPOINTS', Tools::getValue('oyst_public_endpoints'));
        return $res;
    }

    /**
     * Logging methods
     */

    public function logNotification($name, $debug)
    {
        $data = "<!---- Start notification ".$name." -->\n";
        $data .= "Response:\n".var_export(Tools::file_get_contents('php://input'), true)."/n";
        $data .= "Debug:\n".var_export($debug, true)."/n";
        $data .= "<!---- End notification -->\n";
        $this->log($data);
    }

    public function log($data)
    {
        if (_PS_OYST_DEBUG_ != 1) {
            return '';
        }
        if (is_array($data)) {
            $data_json = json_encode($data);
            $data = var_export($data, true);
        }
        file_put_contents(
            dirname(__FILE__).'/logs/log-notification.txt',
            '['.date('Y-m-d H:i:s').'] '.$data_json."\n",
            FILE_APPEND
        );
        file_put_contents(
            dirname(__FILE__).'/logs/log-notification.txt',
            '['.date('Y-m-d H:i:s').'] '.$data."\n",
            FILE_APPEND
        );
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    public function hookFooter($params)
    {
        if (Configuration::hasKey('OYST_SCRIPT_TAG') && Configuration::hasKey('OYST_MERCHANT_ID')) {
            $script_tag = str_replace('[MERCHANT_ID_PLACEHOLDER]', Configuration::get('OYST_MERCHANT_ID'), base64_decode(Configuration::get('OYST_SCRIPT_TAG')));
            $this->context->smarty->assign(array(
                'page_name_oyst' => $this->getPageName(),
                'base_url' => Tools::getShopDomainSsl(true),
                'cart_url' => $this->context->link->getPageLink('cart', Configuration::get('PS_SSL_ENABLED')),
                'redirect_url' => $this->context->link->getModuleLink('oyst', 'oneclickreturn', array(), Configuration::get('PS_SSL_ENABLED')),
                'script_tag' => $script_tag,
                'form_selector' => $this->getFormSelector()
            ));
            return $this->display(__FILE__, './views/templates/hook/displayFooter.tpl');
        }
        return '';
    }

    private function getPageName()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            // Are we in a payment module
            $module_name = '';
            if (Validate::isModuleName(Tools::getValue('module'))) {
                $module_name = Tools::getValue('module');
            }

            if (!empty($this->context->controller->php_self)) {
                $page_name = $this->context->controller->php_self;
            } elseif (Tools::getValue('fc') == 'module' && $module_name != '' && (Module::getInstanceByName($module_name) instanceof PaymentModule)) {
                $page_name = 'module-payment-submit';
            }
            // @retrocompatibility Are we in a module ?
            elseif (preg_match('#^'.preg_quote($this->context->shop->physical_uri, '#').'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m)) {
                $page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
            } else {
                $page_name = Dispatcher::getInstance()->getController();
                $page_name = (preg_match('/^[0-9]/', $page_name) ? 'page_'.$page_name : $page_name);
            }
        } else {
            $page_name = $this->context->controller->getPageName();
        }
        return $page_name;
    }

    private function getFormSelector()
    {
        $form_selector = '#add-to-cart-or-refresh';

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $form_selector = '#buy_block';
        }

        return $form_selector;
    }

    public function hookActionEmailSendBefore($params)
    {
        if (isset($this->context->oyst_skip_mail) && isset($this->context->oyst_current_notification) && !empty($this->context->cart->id)) {
            // Save email informations into notification table
            if ($this->context->oyst_current_notification->saveOrderEmailData($params)) {
                //Return false to cancel email sending
                return false;
            }
        }
        return true;
    }

    public function hookActionOrderHistoryAddAfter($params)
    {
        if (!empty($params['order_history']) && $params['order_history']->id_order_state == Configuration::get('OYST_ORDER_STATUS_PAYMENT_TO_CAPTURE')) {
            // Call endpoint of connector to call capture
            if (Configuration::hasKey('OYST_PUBLIC_ENDPOINTS')) {
                $public_endpoints = json_decode(Configuration::get('OYST_PUBLIC_ENDPOINTS'), true);
                foreach ($public_endpoints as $public_endpoint) {
                    if ($public_endpoint['type'] == 'capture') {
                        $authorization = "Authorization: Bearer ".$public_endpoint['api_key'];
                        $fields = json_encode([
                            'orderIds' => [\Oyst\Classes\Notification::getOystIdByOrderId($params['order_history']->id_order)]
                        ]);
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization));
                        curl_setopt($ch, CURLOPT_URL, $public_endpoint['url']);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $response_json = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($response_json, true);

                        if (!empty($response['orders'])) {
                            foreach ($response['orders'] as $order) {
                                try {
                                    $order_obj = new Order($order['internal_id']);
                                    if (Validate::isLoadedObject($order_obj)) {
										$prestashop_status_name = \Oyst\Services\OystStatusService::getInstance()->getPrestashopStatusFromOystStatus('oyst_payment_captured');
                                        if ($order_obj->getCurrentState() != Configuration::get($prestashop_status_name)) {
                                            $notification = \Oyst\Classes\Notification::getNotificationByOystId($order['oyst_id']);

                                            //Set id_cart to order for cart avoid
                                            $order_obj->id_cart = $notification->cart_id;
                                            $order_obj->update();
                                            // If status oyst_payment_captured => send order email to customer
                                            $history = new OrderHistory();
                                            $history->id_order = $notification->order_id;
                                            $history->changeIdOrderState(Configuration::get($prestashop_status_name), $order_obj, true);
                                            $history->addWithemail();
                                            $notification->sendOrderEmail();
                                        }
                                    } else {
                                        //Can't load object
                                    }
                                } catch(Exception $e) {
                                    //array('error' => 'fail on status change : '.$e->getMessage()));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function hookModuleRoutes()
    {
        return array(
            'oyst_rule' => array(
                'controller' => 'dispatcher',
                'rule' => 'oyst-oneclick/{request}',
                'keywords' => array(
                    'request' => array('regexp' => '.*', 'param' => 'request'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'oyst'
                ),
            ),
        );
    }

    public function hookDisplayPaymentReturn()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {

            // Load data
            $id_cart = (int)Tools::getValue('id_cart');
            $id_order = Order::getOrderByCartId($id_cart);
            $order = new Order($id_order);
            $transaction_id = Db::getInstance()->getValue('
            SELECT `payment_id` FROM `'._DB_PREFIX_.'oyst_payment_notification`
            WHERE `id_cart` = '.(int)$id_cart);

            // Security check
            if ($order->secure_key != Tools::getValue('key')) {
                die('Secure key is invalid');
            }

            // Assign data
            $assign = array(
                'order_reference' => $order->reference,
                'transaction_id' => $transaction_id,
            );

            $this->context->smarty->assign($this->name, $assign);

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $template_name = 'displayPaymentReturn.tpl';
            } else {
                $template_name = 'displayPaymentReturn.bootstrap.tpl';
            }
            return $this->display(__FILE__, $template_name, $this->getCacheId());
        }
    }
}
