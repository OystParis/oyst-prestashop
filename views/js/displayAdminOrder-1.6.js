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
var cancel_button_html = '<a id="desc-order-freepay-cancel" class="btn btn-default" href="#"> <i class="icon-exchange"></i> ' + label_cancel + '</a>';
var refund_button_html = '<i class="icon-exchange"></i> ' + label_refund;

$(document).ready(function() {
    // Display FreePay transaction ID
    var panel_heading = $('.panel-heading-action');
    panel_heading.before('<span class="badge">Transaction Oyst n°' + oyst_transaction_id + '</span>');

    $('#desc-order-partial_refund').click(function() {
        $('input[name^="partialRefundProductQuantity"]').focusout(function(event) {
            var quantity = parseFloat($('input.partialRefundProductQuantity', $(this).closest('tr')).val());
            var inputQuantity = parseFloat($(this).val());

            if (inputQuantity < 0 || inputQuantity > quantity) {
                //use setTimeout method to fix a Firefox bug which select text after dismissing the alert
                setTimeout(function() { alert(label_wrong_quantity); }, 0);
                $(this).val(0);
            }
        });
        $('input[name^="partialRefundProduct"]').focusout(function(event) {
            var amount = parseFloat($('input.partialRefundProductAmount', $(this).closest('tr')).val());
            var inputAmount = parseFloat($(this).val());

            if (inputAmount < 0 || inputAmount > amount) {
                //use setTimeout method to fix a Firefox bug which select text after dismissing the alert
                setTimeout(function() { alert(label_wrong_amount); }, 0);
                $(this).val(0);
            }
        });
    });
});
