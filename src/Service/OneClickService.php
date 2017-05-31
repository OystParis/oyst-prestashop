<?php

namespace Oyst\Service;

use Combination;
use Exception;
use Oyst\Classes\OystUser;
use Oyst\Api\OystOneClickApi;
use Oyst\Service\Http\CurrentRequest;
use Product;
use Validate;

/**
 * Class Oyst\Service\OneClickService
 */
class OneClickService extends AbstractOystService
{
    /**
     * @param Product $product
     * @param $quantity
     * @param Combination|null $combination
     * @param OystUser|null $user
     * @return array
     * @throws Exception
     */
    public function authorizeNewOrder(Product $product, $quantity, Combination $combination = null, OystUser $user = null)
    {
        $productReference = $this->oyst->getProductReference($product, $combination);

        $response = $this->requester->call('authorizeOrder', array(
            $productReference,
            $quantity,
            null,
            $user
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
        }

        if (isset($data['error'])) {
            $this->logger->critical(sprintf('Error occurred during oneClick process: %s', $data['error']));
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
            }

            $result = $this->authorizeNewOrder($product, $quantity, $combination, $oystUser);
            $data = array_merge($data, $result);
        }

        return $data;
    }
}
