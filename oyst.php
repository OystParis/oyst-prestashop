<?php

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
        $this->version = '2.2.1-RC1';
        $this->tab = 'payments_gateways';

        parent::__construct();

        $this->author = 'Oyst';
        $this->displayName = $this->l('Oyst - 1Click');
        $this->description = $this->l('Oyst permet d\'acheter en un clic depuis n\'importe quelle page de votre site. Offrez à vos clients une expérience d\'achat en un clic');
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
        return parent::uninstall() && \Oyst\Classes\InstallManager::getInstance()->uninstall();
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
        $result &= $this->registerHook('displayBackOfficeHeader');

        if ($result) {
			// Clear cache
			Cache::clean('Module::getModuleIdByName_oyst');

			$result &= \Oyst\Classes\InstallManager::getInstance()->install();
		}
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

        $module_dir = _MODULE_DIR_.$this->name.'/';

		//If multishop and global shop => error
		if (Shop::getContext() != Shop::CONTEXT_GROUP && Shop::getContext() != Shop::CONTEXT_ALL) {
			$oyst_api_key = \Oyst\Classes\OystAPIKey::getInstance()->getAPIKey();

			$order_states = OrderState::getOrderStates($this->context->language->id);

			$this->context->smarty->assign([
				'module_dir' => $module_dir,
				'oyst_api_key' => $oyst_api_key,
				'oyst_merchant_id' => Configuration::get('OYST_MERCHANT_ID'),
				'oyst_script_tag' => base64_decode(Configuration::get('OYST_SCRIPT_TAG')),
				'oyst_public_endpoints' => Configuration::get('OYST_PUBLIC_ENDPOINTS'),
				'oyst_hide_errors' => Configuration::get('OYST_HIDE_ERRORS'),
				'order_states' => $order_states,
				'oyst_order_creation_status' => Configuration::get('OYST_ORDER_CREATION_STATUS'),
			]);
		} else {
			$this->context->smarty->assign('multishop_error', true);
		}

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
        $res = Configuration::updateValue('OYST_MERCHANT_ID', Tools::getValue('oyst_merchant_id'), false, Shop::getContextShopGroupID(), Shop::getContextShopID());
        $res &= Configuration::updateValue('OYST_SCRIPT_TAG', base64_encode(Tools::getValue('oyst_script_tag')), false, Shop::getContextShopGroupID(), Shop::getContextShopID());
        $res &= Configuration::updateValue('OYST_PUBLIC_ENDPOINTS', Tools::getValue('oyst_public_endpoints'), false, Shop::getContextShopGroupID(), Shop::getContextShopID());
        $res &= Configuration::updateValue('OYST_HIDE_ERRORS', Tools::getValue('oyst_hide_errors'), false, Shop::getContextShopGroupID(), Shop::getContextShopID());
        $res &= Configuration::updateValue('OYST_ORDER_CREATION_STATUS', Tools::getValue('oyst_order_creation_status'), false, Shop::getContextShopGroupID(), Shop::getContextShopID());
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
    	$script_tag = Configuration::get('OYST_SCRIPT_TAG');
    	$merchant_id = Configuration::get('OYST_MERCHANT_ID');
        if (!empty($script_tag) && !empty($merchant_id)) {

            if (in_array($this->getPageName(), ['order-confirmation'])) {
                $this->context->smarty->assign('tracking_parameters', \Oyst\Services\TrackingService::getInstance()->getTrackingParameters(true));
            }
            $script_tag = str_replace('[MERCHANT_ID_PLACEHOLDER]', $merchant_id, base64_decode($script_tag));
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
        if (!empty($params['order_history'])) {
            if ($params['order_history']->id_order_state == Configuration::get('OYST_ORDER_STATUS_PAYMENT_TO_CAPTURE')) {
                $order = new Order($params['order_history']->id_order);
                $amount = $order->getTotalPaid();
                $fields = [
                    'orderAmounts' => [
                        \Oyst\Classes\Notification::getOystIdByOrderId($params['order_history']->id_order) => $amount
                    ]
                ];

                $endpoint_result = \Oyst\Services\EndpointService::getInstance()->callEndpoint('capture', $fields);

                if (!empty($endpoint_result['orders'])) {
                    foreach ($endpoint_result['orders'] as $order) {
                        try {
                            $order_obj = new Order($order['internal_id']);
                            if (Validate::isLoadedObject($order_obj)) {
                                $prestashop_status_id = \Oyst\Services\OystStatusService::getInstance()->getPrestashopStatusIdFromOystStatus('oyst_payment_captured');
                                if ($order_obj->getCurrentState() != $prestashop_status_id) {
                                    $notification = \Oyst\Classes\Notification::getNotificationByOystId($order['oyst_id']);

                                    //Set id_cart to order for cart avoid
                                    $order_obj->id_cart = $notification->cart_id;
                                    $order_obj->update();
                                    // If status oyst_payment_captured => send order email to customer
                                    $history = new OrderHistory();
                                    $history->id_order = $notification->order_id;
                                    $history->changeIdOrderState(Configuration::get($prestashop_status_id), $order_obj, true);
                                    $history->addWithemail();
                                    $notification->sendOrderEmail();
                                }
                            } else {
                                //Can't load object
                            }
                        } catch (Exception $e) {
                            //array('error' => 'fail on status change : '.$e->getMessage()));
                        }
                    }
                }
            } elseif ($params['order_history']->id_order_state == Configuration::get('PS_OS_REFUND')) {
                // If switch to status refund => Total refund
                \Oyst\Services\OrderService::getInstance()->refund($params['order_history']->id_order);
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

    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::isSubmit('partialRefundProduct') && ($refunds = Tools::getValue('partialRefundProduct')) && is_array($refunds)) {
            $amount = 0;
            $order_detail_list = array();
            $full_quantity_list = array();
            //Calcul refund amount
            foreach ($refunds as $id_order_detail => $amount_detail) {
                $quantity = Tools::getValue('partialRefundProductQuantity');
                if (!$quantity[$id_order_detail]) {
                    continue;
                }

                $full_quantity_list[$id_order_detail] = (int)$quantity[$id_order_detail];

                $order_detail_list[$id_order_detail] = array(
                    'quantity' => (int)$quantity[$id_order_detail],
                    'id_order_detail' => (int)$id_order_detail
                );

                $order_detail = new OrderDetail((int)$id_order_detail);
                if (empty($amount_detail)) {
                    $order_detail_list[$id_order_detail]['unit_price'] = (!Tools::getValue('TaxMethod') ? $order_detail->unit_price_tax_excl : $order_detail->unit_price_tax_incl);
                    $order_detail_list[$id_order_detail]['amount'] = $order_detail->unit_price_tax_incl * $order_detail_list[$id_order_detail]['quantity'];
                } else {
                    $order_detail_list[$id_order_detail]['amount'] = (float)str_replace(',', '.', $amount_detail);
                    $order_detail_list[$id_order_detail]['unit_price'] = $order_detail_list[$id_order_detail]['amount'] / $order_detail_list[$id_order_detail]['quantity'];
                }
                $amount += $order_detail_list[$id_order_detail]['amount'];
            }
            $shipping_cost_amount = (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) ? (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) : false;

            // If something to refund
            if ($amount != 0 || $shipping_cost_amount != 0) {
                \Oyst\Services\OrderService::getInstance()->refund(Tools::getValue('id_order'), $amount + $shipping_cost_amount);
                $order = new Order(Tools::getValue('id_order'));
                $prestashop_partial_refund_status_id = \Oyst\Services\OystStatusService::getInstance()->getPrestashopStatusIdFromOystStatus('oyst_partial_refund');
                if (!empty($prestashop_partial_refund_status_id)) {
                    $order->setCurrentState($prestashop_partial_refund_status_id);
                } else {
                    //TODO throw exception because status not found
                }
            }
        }
    }

    public function hookDisplayPaymentReturn()
    {
        $template_name = 'displayPaymentReturn.bootstrap.tpl';

        $id_cart = (int)Tools::getValue('id_cart');
        $id_order = Order::getOrderByCartId($id_cart);
        $order = new Order($id_order);

        // Security check
        if ($order->secure_key != Tools::getValue('key')) {
            die('Secure key is invalid');
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {

            // Assign data
            $assign = array(
                'order_reference' => $order->reference,
            );

            $this->context->smarty->assign($this->name, $assign);

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $template_name = 'displayPaymentReturn.tpl';
            }
        }
        return $this->display(__FILE__, $template_name);
    }
}
