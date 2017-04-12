<?php

namespace Oyst\Service;

use Combination;
use Exception;
use Oyst\Classes\OystUser;
use Oyst\Api\OystOneClickApi;
use Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Validate;

/**
 * Class Oyst\Service\OneClickService
 */
class OneClickService extends AbstractOystService
{
    /** @var  OystOneClickApi */
    private $oneClickApi;

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
        if (null == $this->oneClickApi) {
            throw new Exception('Did you forget to inject the oneClick api component ?');
        }

        $productReference = $this->oyst->getProductReference($product, $combination);
        // TODO: Handle variation and change this SKU
        $response = $this->oneClickApi->authorizeOrder($productReference, $quantity, null, $user);

        if ($this->oneClickApi->getLastHttpCode() == 200) {
            $result = array(
                'url' => $response['url'],
                'state' => true,
            );
        } else {
            $result = array(
                'error' => $this->oneClickApi->getLastError(),
                'state' => false,
            );
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function requestAuthorizeNewOrderProcess(Request $request)
    {
        $data = array(
            'state' => false,
        );

        $product = null;
        $combination = null;
        $quantity = 0;
        $response = new JsonResponse();

        if (!$request->request->has('oneClick')) {
            $data['error'] = 'Missing parameters';
        } elseif (!$request->request->has('productId')) {
            $data['error'] = 'Missing product';
        } elseif (!$request->request->has('productAttributeId')) {
            $data['error'] = 'Missing combination, even none selected';
        } elseif (!$request->request->has('quantity')) {
            $data['error'] = 'Missing quantity';
        }

        if (!isset($data['error'])) {

            $product = new Product($request->request->get('productId'));
            if (!Validate::isLoadedObject($product)) {
                $data['error'] = 'Product can\'t be found';
            }

            if ($request->request->has('productAttributeId')) {
                $combination = new Combination($request->request->get('productAttributeId'));
                if (!Validate::isLoadedObject($combination)) {
                    $data['error'] = 'Combination could not be found';
                }
            }

            $quantity = (int)$request->request->get('quantity');
            if ($quantity <= 0) {
                $data['error'] = 'Bad quantity';
            }
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

        return $response->setData($data);
    }

    /**
     * @param OystOneClickApi $oneClickApi
     * @return $this
     */
    public function setOneClickApi(OystOneClickApi $oneClickApi)
    {
        $this->oneClickApi = $oneClickApi;

        return $this;
    }
}
