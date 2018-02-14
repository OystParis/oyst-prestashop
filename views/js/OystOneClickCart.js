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
 * Manage oneClickCart process
 */
function OystOneClickCart(url, controller) {

    this.url = url;
    this.controller = controller;
    // this.combinations = [];
    this.stockManagement = 1;
    // this.allowOosp = 0;
    // this.productQuantity = 0;
    this.button = '#oneClickContainer';
    this.themeBtn = 'normal';
    this.colorBtn = '#E91E63';
    this.widthBtn = '230px';
    this.heightBtn = '60px';
    // this.positionBtn = 'before';
    this.preload = 1;
    this.shouldAsStock = 0;
    this.errorText = 'There isn\'t enough product in stock.';

    this.setPreload = function(preload) {
        this.preload = preload;
    }

    this.setShouldAskStock = function(shouldAsStock) {
        this.shouldAsStock = shouldAsStock;
    }

    this.setStockManagement = function(stockManagement) {
        this.stockManagement = stockManagement;
    }

    this.setThemeBtn = function(themeBtn) {
        this.themeBtn = themeBtn;
    }

    this.setColorBtn = function(colorBtn) {
        this.colorBtn = colorBtn;
    }

    this.setWidthBtn = function(widthBtn) {
        this.widthBtn = $('.standard-checkout').width()+'px';
    }

    this.setHeightBtn = function(heightBtn) {
        this.heightBtn = $('.standard-checkout').outerHeight()+'px';
    }

    this.setErrorText = function(errorText) {
        this.errorText = errorText;
    }

    /**
     * Return json with the product information to avoid any redundant code.
     * @returns {{isExported: boolean, product: {productId, productAttributeId: *, quantity: (*|jQuery)}}}
     */
    // this.isProductExported = function () {
    //     var product = this.getSelectedProduct();
    //     // if productAttributeIf is equal to 0, it means its a unique product
    //     var isExported = product.productAttributeId in this.combinations;
    //
    //     return {
    //         "isExported": isExported,
    //         "product": product
    //     };
    // };

    /**
     * Check is the product is available
     * @returns {boolean}
     */
    // this.isProductAvailable = function() {
    //     var productExported = this.isProductExported();
    //     var product = productExported.product;
    //
    //     if (this.stockManagement && !this.allowOosp){
    //         if (product.productAttributeId == 0)
    //             return 0 < this.productQuantity;
    //         else
    //             return 0 < this.combinations[product.productAttributeId].quantity;
    //     } else {
    //         return true
    //     }
    //
    //     return false;
    // };

    /**
     * Watch any change about the variations of the product
     */
    // this.watcherCombination = function() {
    //     if (this.isProductAvailable()) {
    //         $(this.button).show();
    //     } else {
    //         $(this.button).hide();
    //     }
    //
    //     var object = this;
    //     window.setTimeout(function () {
    //         object.watcherCombination();
    //     }, 100);
    // };

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
        // if (this.positionBtn == 'after') {
        //     $('#add_to_cart, .add_to_cart').after($('<div>', {
        //         id: 'oneClickContainer'
        //     }));
        // } else {
        $('.standard-checkout').after($('<div>', {
            id: 'oneClickContainer'
        }));
        // }


        $('#oneClickContainer').append($('<div>', {
            id: 'oyst-1click-button'
        }).attr(
            'data-theme', this.themeBtn
        ).attr(
            'data-color', this.colorBtn
        ).attr(
            'data-width', this.widthBtn
        ).attr(
            'data-height', this.heightBtn
        ).attr(
            'data-rounded', false
        ).attr(
            'data-smart', false
        ));

        // this.prepareEvents();
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

        var quantity = $('input[name="qty"]').val();
        if (typeof quantity === "undefined")
            quantity = 1;

        if (this.shouldAsStock) {
            if ($('#quantityAvailable').length && parseInt($('#quantityAvailable').html()) < quantity) {
                if (!!$.prototype.fancybox) {
                    $.fancybox.open([
                      {
                        type: 'inline',
                        autoScale: true,
                        minHeight: 30,
                        content: '<p class="fancybox-error">' + this.errorText + '</p>'
                      }
                    ], {
                      padding: 0
                    });
                    return;
                } else {
                    alert(this.errorText);
                    return;
                }
            }
        }

        return {
            productId: this.productId,
            productAttributeId: productAttributeId,
            quantity: quantity,
        }
    };

    /**
     * Send request to start oneClick process
     */
    this.requestOneCLick = function(oystCallBack) {
        var params = {};

        params.controller = this.controller;
        if (this.preload) {
          params.preload = this.preload;
          this.setPreload(0);
        } else {
          params.preload = this.preload;
        }

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