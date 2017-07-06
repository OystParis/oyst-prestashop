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
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Product;
use Oyst\Transformer\ProductTransformer;
use Oyst\Classes\OystProduct;

/**
 * Class ProductService
 */
class PaymentService extends AbstractOystService
{
    /** @var OystCatalogAPI */
    private $catalogApi;

    /** @var ProductTransformer */
    private $productTransformer;

    /** @var ProductRepository */
    private $productRepository;

     /**
     * @param int $amount, string $currency
     * @return $this
     */
    
    public function partialRefund($guid, $amount, $status)
    {
         $this->requester->call('cancelOrRefund', array($guid, $amount));

        $succeed = false;
        if ($this->requester->getApiClient()->getLastHttpCode() != 200) {
            $this->logger->warning(sprintf('Oyst order %s has not been updated to %s', $guid, $status));
        } else {
            $succeed = true;
            $this->logger->info(sprintf('Oyst order %s has been updated to %s', $guid, $status));
        }

        return $succeed;       
    }
}
