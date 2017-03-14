<?php

class ProductRepository
{
    /**
     * @var Db
     */
    private $db;

    /**
     * ProductRepository constructor.
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function getProductsNotExported()
    {
        $query = "
            SELECT 
              p.id_product,
              pa.id_product_attribute
            FROM ps_product p
            LEFT JOIN ps_product_attribute pa ON pa.id_product = p.id_product
            WHERE CONCAT(p.id_product, '-', pa.id_product_attribute) NOT IN (
              SELECT CONCAT(oec.productId, '-', oec.productAttributeId)
              FROM ps_oyst_exported_catalog oec
            )
            LIMIT 512
        ";

        // Use replace to keep IDE working with database source on dev side.
        $products = $this->db->executeS(str_replace('ps_', _DB_PREFIX_, $query));
        return $products;
    }

    /**
     * @param $products
     * @param $importId
     */
    public function recordSentProducts($products, $importId)
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = [
                'productId' => $product['id_product'],
                'productAttributeId' => $product['id_product_attribute'],
                'importId' => $importId,
            ];
        }
        $this->db->insert('oyst_exported_catalog', $productIds);
    }
}
