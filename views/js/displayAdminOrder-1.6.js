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
var cancel_button_html = '<a id="desc-order-freepay-cancel" class="btn btn-default" href="#"> <i class="icon-exchange"></i> ' + label_cancel + '</a>&nbsp;&nbsp;&nbsp;';
var refund_button_html = '<a id="desc-order-freepay-refund" class="btn btn-default" href="#"> <i class="icon-exchange"></i> ' + label_refund + '</a>&nbsp;&nbsp;&nbsp;';
var partial_refund_button_html = '<a id="desc-order-freepay-partial-refund" class="btn btn-default" href="#"> <i class="icon-exchange"></i> ' + label_partial_refund + '</a>';

$(document).ready(function() {
    // Display FreePay transaction ID
    var panel_heading = $('.panel-heading-action');
    panel_heading.before('<span class="badge">Transaction FreePay n°' + oyst_transaction_id + '</span>');

});
