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
    $('#toggleConfig').click(function() {
        $('.advancedOptions').toggle();
        $('i', this).toggleClass('icon-eye').toggleClass('icon-eye-close');
        $('span', this).toggle();
    });
    $('.urlCustomization select').each(function() {
        handleSelectOptions($(this));
    }).change(function() {
        handleSelectOptions($(this));
    });
});

function handleSelectOptions(select) {
    var parentDiv = select.parent();

    $('option:selected', select).each(function() {
        if ($(this).hasClass('customUrl')) {
            $('input.customUrlText', parentDiv).show().removeAttr('disabled');
        } else {
            $('input.customUrlText', parentDiv).hide().attr('disabled', 'disabled');
        }
    });
}
