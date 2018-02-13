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
    $('.urlCustomization select').each(function() {
        handleSelectOptions($(this));
    }).change(function() {
        handleSelectOptions($(this));
    });

    $('select[name^="OYST_API_ENV"]').each(function () {
        refreshEnvironmentDisplay($(this));
    }).on('change', function () {
        refreshEnvironmentDisplay($(this));
    });
    $('select[name="OYST_API_ENV_ONECLICK"]').on('change', function () {
        $('#shipment-collection').empty();
        $('#add-shipment').attr('disabled', 'disabled');
        $('#add-shipment-help').show();
    });
    $('ul#oyst-config-menu a').click(function () {
        var clickedTab = $(this).attr('href');
        $('#current_tab_value').val(clickedTab);

        handleExportCatalogButton(clickedTab);
    });

    handleExportCatalogButton(currentTab);

    $('#payment_notifications').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": notification_bo_url+"&action=getNotifications"
    });
});

function handleExportCatalogButton(clickedTab) {
    if (clickedTab == '#tab-content-FreePay') {
        $('#module_export_catalog_btn').hide();
    } else {
        $('#module_export_catalog_btn').show();
    }
}

function handleSelectOptions(select) {
    var parentDiv = select.parent();

    $('option:selected', select).each(function() {
        if ($(this).hasClass('customUrl')) {
            $('.customUrlText', parentDiv).show();
            $('input.customUrlText', parentDiv).removeAttr('disabled');
        } else {
            $('.customUrlText', parentDiv).hide();
            $('input.customUrlText', parentDiv).attr('disabled', 'disabled');
        }
    });
}

function refreshEnvironmentDisplay(select) {
    var tabContainer = select.closest('.tab-pane');
    var containerClass = '.' + select.val();
    $('.env', tabContainer).not(containerClass).hide();
    $(containerClass, tabContainer).show();
}
