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

namespace Oyst\Service;

use Combination;
use Exception;
use Oyst\Classes\OystUser;
use Oyst\Classes\OneClickOrderParams;
use Oyst\Classes\OneClickNotifications;
use Oyst\Classes\OneClickCustomization;
use Oyst\Service\Http\CurrentRequest;
use Product;
use Validate;
use Oyst;
use Context;
use Currency;
use Configuration as ConfigurationP;
use Oyst\Factory\AbstractExportProductServiceFactory;
use Tools;
use StockAvailable;
use Module;

/**
 * Class Oyst\Service\OneClickService
 */
class OneClickService extends AbstractOystService
{
    const ONE_CLICK_VERSION = 2;

    /**
     * @param Product $product
     * @param $quantity
     * @param Combination|null $combination
     * @param OystUser|null $user
     * @return array
     * @throws Exception
     */
    public function authorizeNewOrder(
        $productLess = null,
        OneClickNotifications $notifications = null,
        OystUser $user = null,
        OneClickOrderParams $orderParams = null,
        $context = null,
        OneClickCustomization $customization = null
    ) {
        if (!is_array($productLess)) {
            $products[] = $productLess;
        } else {
            $products = $productLess;
        }
        $response = $this->requester->call('authorizeOrderV2', array(
            $products,
            $notifications,
            $user,
            $orderParams,
            $context,
            $customization
        ));

        $apiClient = $this->requester->getApiClient();
        if ($apiClient->getLastHttpCode() == 200) {
            $result = array(
                'url' => $response['url'],
                'state' => true,
            );
        } else {
            $result = array(
                'error' => $apiClient->getLastError(),
                'state' => false,
            );
        }

        return $result;
    }

    /**
     * @param CurrentRequest $request
     * @return array
     */
    public function requestAuthorizeNewOrderProcess(CurrentRequest $request)
    {

        $data = array(
            'state' => false,
        );

        $product = null;
        $combination = null;
        $quantity = 0;
        $products = null;
        $controller = $request->getRequestItem('controller');
        // Deprecated ??
        Context::getContext()->currency = new Currency(ConfigurationP::get('PS_CURRENCY_DEFAULT'));
        $exportProductService = AbstractExportProductServiceFactory::get(new Oyst(), Context::getContext());
        $load = (int)$request->getRequestItem('preload');
        $labelCta = $request->getRequestItem('labelCta');

        if ($controller == 'order') {
            $products = Context::getContext()->cart->getProducts();

            if (!$products) {
                $data['error'] = 'Missing products';
            }
        } else {
            $idProduct = (int)$request->getRequestItem('productId');
            $idCombination = (int)$request->getRequestItem('productAttributeId');

            if (!$request->hasRequest('productId')) {
                $data['error'] = 'Missing product';
            } elseif (!$request->hasRequest('productAttributeId')) {
                $data['error'] = 'Missing combination, even none selected';
            } elseif (!$request->hasRequest('quantity')) {
                $data['error'] = 'Missing quantity';
            }
        }

        if (!$request->hasRequest('oneClick')) {
            $data['error'] = 'Missing parameters';
        }

        if (!isset($data['error'])) {
            if ($products && $controller == 'order') {
                foreach ($products as $product) {
                    $productLess[] = $exportProductService->transformProductLess(
                        $product['id_product'],
                        $product['id_product_attribute'],
                        $product['cart_quantity']
                    );

                    if ($load == 0 && ConfigurationP::get('FC_OYST_SHOULD_AS_STOCK')) {
                        if ($product['advanced_stock_management'] == 0) {
                            $qty_available = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']);
                            $new_qty = $qty_available - $quantity;
                            StockAvailable::setQuantity($product['id_product'], $product['id_product_attribute'], $new_qty);
                        }
                    }
                }
            } else {
                $product = new Product($idProduct);
                if (!Validate::isLoadedObject($product)) {
                    $data['error'] = 'Product can\'t be found';
                }

                if ($request->hasRequest('productAttributeId')) {
                    if ($idCombination > 0) {
                        $combination = new Combination($idCombination);
                        if (!Validate::isLoadedObject($combination)) {
                            $data['error'] = 'Combination could not be found';
                        }
                    }
                }

                $quantity = (int)$request->getRequestItem('quantity');
                if ($quantity <= 0) {
                    $data['error'] = 'Bad quantity';
                }

                // Check preload, and update quantity
                $load = (int)$request->getRequestItem('preload');
                if ($load == 0 && ConfigurationP::get('FC_OYST_SHOULD_AS_STOCK')) {
                    if ($product->advanced_stock_management == 0) {
                        StockAvailable::updateQuantity($idProduct, $idCombination, -(int)$quantity);

                        $productLess[] = $exportProductService->transformProductLess(
                            $idProduct,
                            $idCombination,
                            $quantity
                        );
                    }
                }
            }
        }

        if (isset($data['error'])) {
            $this->logger->critical(sprintf('Error occurred during oneClick process: %s', $data['error']));
        }

        // Generated context
        $oystContext = array(
            'id' => (string)$this->generatedId(),
            'store_id' => (int)Context::getContext()->shop->id
        );

        if ($controller == 'order') {
            $oystContext['id_cart'] = (int)Context::getContext()->cart->id;
        }

        if (!isset($data['error'])) {
            $oystUser = null;
            $customer = $this->context->customer;
            if ($customer->isLogged()) {
                $oystUser = (new OystUser())
                    ->setFirstName($customer->firstname)
                    ->setLastName($customer->lastname)
                    ->setLanguage($this->context->language->iso_code)
                    ->setEmail($customer->email);
                $oystContext['id_user'] = $customer->id;
            }

            $oneClickOrdersParams = new OneClickOrderParams();
            foreach ($productLess as $product) {
                if (!$product->materialized) {
                    $oneClickOrdersParams->setIsMaterialized($product->materialized);
                } else {
                    $oneClickOrdersParams->setIsMaterialized(true);
                }
                $result_products[] = $product->toArray();
            }

            $delay = (int)ConfigurationP::get('FC_OYST_DELAY');

            if (is_numeric($delay) && $delay > 0) {
                $oneClickOrdersParams->setDelay($delay);
            } else {
                $oneClickOrdersParams->setDelay(15);
            }

            $oneClickOrdersParams->setShouldReinitBuffer(false);

            if ($controller == 'order') {
                $oneClickOrdersParams->setIsCheckoutCart(true);
                $oneClickOrdersParams->setManageQuantity(ConfigurationP::get('FC_OYST_MANAGE_QUANTITY_CART'));
            } else {
                $oneClickOrdersParams->setIsCheckoutCart(false);
                $oneClickOrdersParams->setManageQuantity(ConfigurationP::get('FC_OYST_MANAGE_QUANTITY'));
            }

            $this->logger->info(
                sprintf(
                    'New notification oneClickOrdersParams [%s]',
                    json_encode($oneClickOrdersParams->toArray())
                )
            );

            if ($labelCta && $labelCta != '' && $controller == 'order') {
                $glue = '&';
                if (ConfigurationP::get('PS_REWRITING_SETTINGS') == 1) {
                    $glue = '?';
                }
                $url = Context::getContext()->link->getModuleLink('oyst', 'oneclickreturn').$glue.'id_cart='.Context::getContext()->cart->id.'&key='.ConfigurationP::get('FC_OYST_HASH_KEY');
                // $url = Context::getContext()->link->getPageLink('order-confirmation').$glue.'id_cart='.Context::getContext()->cart->id.'&id_module='.Module::getModuleIdByName('oyst').'&key='.$customer->secure_key;

                $oneClickCustomization = new OneClickCustomization();
                $oneClickCustomization->setCta($labelCta, $url);
            } else {
                $oneClickCustomization = null;
            }

            $oneClickNotifications = new OneClickNotifications();
            $oneClickNotifications->setShouldAskShipments(true);
            $oneClickNotifications->setShouldAskStock(ConfigurationP::get('FC_OYST_SHOULD_AS_STOCK'));
            if (ConfigurationP::get('FC_OYST_SHOULD_AS_STOCK')) {
                $oneClickNotifications->addEvent('order.stock.released');
                $oneClickNotifications->addEvent('order.stock.book');
            }
            $oneClickNotifications->setUrl($this->oyst->getNotifyUrl());

            $this->logger->info(
                sprintf(
                    'New notification oneClickNotifications [%s]',
                    json_encode($oneClickNotifications->toArray())
                )
            );

            $this->logger->info(
                sprintf(
                    'New notification products [%s]',
                    json_encode($result_products)
                )
            );

            $this->logger->info(
                sprintf(
                    'New notification context [%s]',
                    json_encode($oystContext)
                )
            );
            $result = $this->authorizeNewOrder(
                $productLess,
                $oneClickNotifications,
                $oystUser,
                $oneClickOrdersParams,
                $oystContext,
                $oneClickCustomization
            );
            $data = array_merge($data, $result);
        }

        return $data;
    }

    public function generatedId()
    {
        $hash = ConfigurationP::get('FC_OYST_HASH_KEY');
        $datetime = new \DateTime();
        $datetime = $datetime->format('YmdHis');
        return uniqid().$hash.'-'.$datetime;
    }
}
