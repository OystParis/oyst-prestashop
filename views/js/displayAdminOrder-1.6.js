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

    // Display FreePay transaction ID
    var panel_heading = $('.panel-heading-action');
    panel_heading.before('<span class="badge">Transaction FreePay n°' + oyst_transaction_id + '</span>');

    // Hide partial and refund and display refund button
    var partial_refund_button = $('#desc-order-partial_refund');
    partial_refund_button.hide();
    partial_refund_button.after('<a id="desc-order-freepay-refund" class="btn btn-default" href="#"> <i class="icon-exchange"></i>Remboursement standard </a>');
    $('#desc-order-freepay-refund').click(function() {
        if (confirm('Êtes vous sûr de vouloir rembourser la commande dans son intégralité ?')) {
            $.ajax({
                method: "POST",
                url: window.location.href,
                data: { subaction: "freepay-refund" }
            }).done(function( msg ) {
                msg = JSON.parse(msg);
                if (msg.result == 'success') {
                    window.location.href = window.location.href;
                }
            });
        }
        return false;
    });
});
