{**
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
 * @license GNU GENERAL PUBLIC LICENSE
 *}

<script>
    var oyst_transaction_id = {$oyst.transaction_id|escape:'html':'UTF-8'};
    var order_can_be_cancelled = {$oyst.order_can_be_cancelled|escape:'html':'UTF-8'};
    var order_can_be_refunded = {$oyst.order_can_be_refunded|escape:'html':'UTF-8'};
    var label_cancel = '{$oyst.label_cancel|escape:'html':'UTF-8'}';
    var label_refund = '{$oyst.label_refund|escape:'html':'UTF-8'}';
    var label_partial_refund = '{$oyst.label_partial_refund|escape:'html':'UTF-8'}';
</script>
<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/displayAdminOrder-1.6.js"></script>
<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/displayAdminOrder.js"></script>
