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

// use Oyst\Classes\OneClickShipmentCalculation;
// use Oyst\Classes\OneClickShipmentCatalogLess;
use Oyst\Classes\OneClickStock;
use Configuration as PSConfiguration;
use StockAvailable;
use Product;
use Exeption;

class StockService extends AbstractOystService
{
    /**
     * @param $data
     * @return array
     * @throws Exeption
     */
    public function manageQty($data)
    {
        try {
            if (!isset($data['products'])) {
                $this->logger->emergency(
                    'Products not found: ['.$this->serializer->serialize($data).']'
                );
                return false;
            }

            foreach ($data['products'] as $product) {
                // $qty = (isset($product['quantity']) ? $product['quantity'] : 1);
                $qty = $product['quantity'];
                $idProduct = $product['reference'];
                $idCombination = null;

                if (false  !== strpos($idProduct, ';')) {
                    $p = explode(';', $idProduct);
                    $idProduct = $p[0];
                    $idCombination = $p[1];
                }

                $product = new Product($idProduct);

                if ($product->advanced_stock_management == 0) {
                    StockAvailable::updateQuantity($idProduct, $idCombination, $qty);
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logger->emergency($e->getMessage());
            die(Tools::jsonEncode(array('result' => 'ko', 'error' => $e->getMessage())));
        }
    }

    /**
     * @param $data
     * @return array
     * @throws Exeption
     */
    public function stockBook($data)
    {
        try {
            $qty = $data['quantity'];
            $idProduct = $data['product_reference'];
            $idCombination = null;

            if (false  !== strpos($idProduct, ';')) {
                $p = explode(';', $idProduct);
                $idProduct = $p[0];
                $idCombination = $p[1];
            }

            $product = new Product($idProduct);
            if ($product->advanced_stock_management == 0) {
                $qty_available = StockAvailable::getQuantityAvailableByProduct($idProduct, $idCombination);
                $new_qty = $qty_available - $qty;
                if (StockAvailable::outOfStock($idProduct) === 1 || $new_qty >= 0) {
                    StockAvailable::updateQuantity($idProduct, $idCombination, -$qty);
                    $oneClickStock = new OneClickStock(1, $data['product_reference']);
                } else {
                    $oneClickStock = new OneClickStock(0, $data['product_reference']);
                }
            }

            return $oneClickStock->toArray();

        } catch (Exception $e) {
            $this->logger->emergency($e->getMessage());
            die(Tools::jsonEncode(array('result' => 'ko', 'error' => $e->getMessage())));
        }
    }
}
