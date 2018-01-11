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
use Oyst\Service\Http\CurrentRequest;
use Product;
use Validate;
use Oyst;
use Context;
use Currency;
use Configuration as ConfigurationP;
use Oyst\Factory\AbstractExportProductServiceFactory;
use Tools;

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
        $context = null
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
            null
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

        if (!$request->hasRequest('oneClick')) {
            $data['error'] = 'Missing parameters';
        } elseif (!$request->hasRequest('productId')) {
            $data['error'] = 'Missing product';
        } elseif (!$request->hasRequest('productAttributeId')) {
            $data['error'] = 'Missing combination, even none selected';
        } elseif (!$request->hasRequest('quantity')) {
            $data['error'] = 'Missing quantity';
        }

        if (!isset($data['error'])) {
            $product = new Product($request->getRequestItem('productId'));
            if (!Validate::isLoadedObject($product)) {
                $data['error'] = 'Product can\'t be found';
            }

            if ($request->hasRequest('productAttributeId')) {
                $combinationId = (int) $request->getRequestItem('productAttributeId');
                if ($combinationId > 0) {
                    $combination = new Combination($request->getRequestItem('productAttributeId'));
                    if (!Validate::isLoadedObject($combination)) {
                        $data['error'] = 'Combination could not be found';
                    }
                }
            }

            $quantity = (int)$request->getRequestItem('quantity');
            if ($quantity <= 0) {
                $data['error'] = 'Bad quantity';
            }

            Context::getContext()->currency = new Currency(ConfigurationP::get('PS_CURRENCY_DEFAULT'));
            $exportProductService = AbstractExportProductServiceFactory::get(new Oyst(), Context::getContext());
            $productLess[] = $exportProductService->transformProductLess(
                (int)$request->getRequestItem('productId'),
                (int) $request->getRequestItem('productAttributeId'),
                $quantity
            );
        }

        if (isset($data['error'])) {
            $this->logger->critical(sprintf('Error occurred during oneClick process: %s', $data['error']));
        }

        // Generated context
        $oystContext = array(
            'id' => (string)$this->generatedId(),
            'store_id' => (int)Context::getContext()->shop->id
        );

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
            }

            $delay = (int)ConfigurationP::get('FC_OYST_DELAY');

            if (is_numeric($delay) && $delay > 0) {
                $oneClickOrdersParams->setDelay($delay);
            } else {
                $oneClickOrdersParams->setDelay(15);
            }
            $oneClickOrdersParams->setManageQuantity(true);
            $oneClickOrdersParams->setShouldReinitBuffer(false);

            $this->logger->info(
                sprintf(
                    'New notification oneClickOrdersParams [%s]',
                    json_encode($oneClickOrdersParams->toArray())
                )
            );

            $oneClickNotifications = new OneClickNotifications();
            $oneClickNotifications->setShouldAskShipments(true);
            $oneClickNotifications->setEvents(array('order.shipments.get'));
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
                    json_encode($productLess)
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
                $oystContext
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
