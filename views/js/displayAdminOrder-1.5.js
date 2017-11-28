/*
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
 */

// Refund & Cancel buttons
var cancel_button_html = '<a id="desc-order-freepay-cancel" class="toolbar_btn" href="#"> <span class="process-icon-partial_refund process-icon-partialRefund"></span> <div>' + label_cancel + '</div></a>';
var refund_button_html = '<span class="process-icon-partial_refund process-icon-partialRefund"></span> <div>' + label_refund + '</div>';

$(document).ready(function() {
    // Display FreePay transaction ID
    var page_title = $('span .breadcrumb');
    page_title.text(page_title.text() + ' - Transaction FreePay n°' + oyst_transaction_id);
});
