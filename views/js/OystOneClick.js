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

"use strict";

/**
 * Manage oneClick process
 */
function OystOneClick(url, productId) {

    this.url = url;
    this.productId = productId;
    this.combinations = [];
    this.button = '#oneClickContainer';

    this.setExportedCombinations = function(combinations) {
        this.combinations = combinations;
    }

    /**
     * Return json with the product information to avoid any redundant code.
     * @returns {{isExported: boolean, product: {productId, productAttributeId: *, quantity: (*|jQuery)}}}
     */
    this.isProductExported = function () {
        var product = this.getSelectedProduct();
        // if productAttributeIf is equal to 0, it means its a unique product
        var isExported = product.productAttributeId in this.combinations;

        return {
            "isExported": isExported,
            "product": product
        };
    };

    /**
     * Check is the product is available
     * @returns {boolean}
     */
    this.isProductAvailable = function() {
        var productExported = this.isProductExported();

        if (productExported.isExported) {
            var product = productExported.product;

            return 0 < this.combinations[product.productAttributeId].quantity;
        }

        return false;
    };

    /**
     * Watch any change about the variations of the product
     */
    this.watcherCombination = function() {
        if (this.isProductAvailable()) {
            $(this.button).show();
        } else {
            $(this.button).hide();
        }

        var object = this;
        window.setTimeout(function () {
            object.watcherCombination();
        }, 100);
    };

    /**
     * Prepare any possible events
     */
    this.prepareEvents = function () {
        // Value is changed by PrestaShop code, we need to check using a timer
        this.watcherCombination();
    };

    /**
     * Initialize requirements
     */
    this.prepareButton = function() {
        // Avoid any event issue due to potential remove / create from loaded oyst script
        $('#add_to_cart, .add_to_cart').before($('<div>', {
            id: 'oneClickContainer'
        }));

        $('#oneClickContainer').append($('<div>', {
            id: 'oyst-1click-button'
        }));

        this.prepareEvents();
    };

    /**
     * On Click, retrieve the right product / combination information
     * @returns {{productId, productAttributeId: *, quantity: (*|jQuery)}}
     */
    this.getSelectedProduct = function() {

        var productAttributeId = null;

        if ($('#idCombination').val() != undefined) {
            productAttributeId = parseInt($('#idCombination').val()) || 0;
        }

        return {
            productId: this.productId,
            productAttributeId: productAttributeId,
            quantity: $('input[name="qty"]').val(),
        }
    };

    /**
     * Send request to start oneClick process
     */
    this.requestOneCLick = function(oystCallBack) {
        var params = this.getSelectedProduct();
        params.oneClick = true;
        params.token = '{SuggestToAddSecurityToken}';

        $.post(this.url, params, function(json) {
            if (json.state) {
                oystCallBack(null, json.url);
            } else {
                // display properly the error to try again
                alert('Error occurred, please try later or contact us');
            }
        });
    }
};
