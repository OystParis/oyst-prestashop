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

$(document).ready(function() {
    // Hide prestashop refund buttons and display custom cancel & refund buttons
    var standard_refund_button = $('#desc-order-standard_refund');
    var partial_refund_button = $('#desc-order-partial_refund');
    var address_shipping = $('#addressShipping form');

    standard_refund_button.hide();
    partial_refund_button.hide();
    address_shipping.hide();

    if (method_payment != null && method_payment == 'FP') {
        if (order_can_be_cancelled) {
            standard_refund_button.after(cancel_button_html);
            partial_refund_button.hide();
        } else {
            /*if (order_can_be_totally_refunded) {
                standard_refund_button.after(refund_button_html);
            }*/
            if (order_max_refund > 0) {
                partial_refund_button.show().html(refund_button_html);
            }
        }
    }
    if (method_payment != null && method_payment == 'OC') {
        if (order_can_be_totally_refunded && order_max_refund > 0) {
            partial_refund_button.show().html(refund_oc_button_html);
        }
    }

    $('#desc-order-freepay-cancel').click(function() {
        if (confirm(label_confirm_cancel)) {
            $('#desc-order-freepay-cancel').attr('disabled', 'disabled');
            $.ajax({
                method: 'POST',
                url: window.location.href,
                data: { subaction: 'freepay-refund' }
            }).done(function( msg ) {
                msg = JSON.parse(msg);
                if (msg.result == 'success') {
                    window.location.href = window.location.href;
                } else {
                    alert(label_error + ' ' + msg.details.message);
                    $('#desc-order-freepay-cancel').removeAttr('disabled');
                }
            });
        }
        return false;
    });

    $('#desc-order-freepay-refund').click(function() {
        if (confirm(label_confirm_refund)) {
            $('#desc-order-freepay-refund').attr('disabled', 'disabled');
            partial_refund_button.attr('disabled', 'disabled');
            $.ajax({
                method: 'POST',
                url: window.location.href,
                data: { subaction: 'freepay-refund' }
            }).done(function( msg ) {
                msg = JSON.parse(msg);
                if (msg.result == 'success') {
                    window.location.href = window.location.href;
                } else {
                    alert('Une erreur s\'est produite lors du remboursement : ' + msg.details.message);
                    $('#desc-order-freepay-refund').removeAttr('disabled');
                    partial_refund_button.removeAttr('disabled');
                }
            });
        }
        return false;
    });
});
