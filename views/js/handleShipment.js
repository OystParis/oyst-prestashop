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
    var shipmentModel = $('#shipment-model').html();
    // We delete the shipment model form to not submit it
    $('#shipment-model').remove();

    var shipmentCount = $('.shipment-item').length;

    $('#add-shipment').click(function(event) {
        event.preventDefault();

        var shipmentNewItem = shipmentModel.replace(/__shipment_id__/g, shipmentCount);

        $('#shipment-collection').append(shipmentNewItem);
        shipmentCount++;

        addDeleteEventOnButton();

        // if this is the first shipment
        if ($('.shipment-item').length == 1) {
            $('.shipment-primary').attr('checked', 'checked');
            $('.shipment-primary').attr('readonly', 'readonly');
        } else {
            $('.shipment-primary').removeAttr('readonly');
        }
    });

    addDeleteEventOnButton();
});

function addDeleteEventOnButton()
{
    $('.delete-shipment').click(function(event) {
        event.preventDefault();

        if(confirm('Are you sure you want to delete this shipment?')) {
            $(this).closest('div.shipment-item').remove();

            // if there is only one shipment left
            if ($('.shipment-item').length == 1) {
                $('.shipment-primary').attr('checked', 'checked');
                $('.shipment-primary').attr('readonly', 'readonly');
            }
        }
    });

    $('.shipment-primary').click(function() {
        $('.shipment-primary').removeAttr('checked');
        $(this).attr('checked', 'checked');
    });
}
