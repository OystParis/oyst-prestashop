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
function OystOneClick(url, productId, controller) {

    this.url = url;
    this.controller = controller;
    this.productId = productId;
    this.combinations = [];
    this.stockManagement = 1;
    this.allowOosp = 0;
    this.productQuantity = 0;
    this.button = '#oneClickContainer';
    this.smartBtn = true;
    this.borderBtn = true;
    this.themeBtn = 'normal';
    this.colorBtn = '#E91E63';
    this.widthBtn = '';
    this.heightBtn = '';
    this.marginTopBtn = '0px';
    this.marginLeftBtn = '0px';
    this.marginRightBtn = '0px';
    this.positionBtn = 'before';
    this.idBtnAddToCart = '#add_to_cart';
    this.idBtnSmartBtn = '#add_to_cart button';
    this.preload = 1;
    this.shouldAsStock = 0;
    this.errorText = 'There isn\'t enough product in stock.';

    this.setExportedCombinations = function (combinations) {
        this.combinations = combinations;
    };

    this.setPreload = function (preload) {
        this.preload = preload;
    };

    this.setShouldAskStock = function (shouldAsStock) {
        this.shouldAsStock = shouldAsStock;
    };

    this.setStockManagement = function (stockManagement) {
        this.stockManagement = stockManagement;
    };

    this.setAllowOosp = function (allowOosp) {
        this.allowOosp = allowOosp;
    };

    this.setProductQuantity = function (productQuantity) {
        this.productQuantity = productQuantity;
    };

    this.setSmartBtn = function (smartBtn) {
        this.smartBtn = smartBtn;
    };

    this.setBorderBtn = function (borderBtn) {
        this.borderBtn = borderBtn;
    };

    this.setThemeBtn = function (themeBtn) {
        this.themeBtn = themeBtn;
    };

    this.setColorBtn = function (colorBtn) {
        this.colorBtn = colorBtn;
    };

    this.setWidthBtn = function (widthBtn) {
        if (this.smartBtn) {
            if (widthBtn) {
                this.widthBtn = widthBtn;
            } else {
                this.widthBtn = $(this.idBtnSmartBtn).width()+'px';
            }
        } else if (widthBtn) {
            this.widthBtn = widthBtn;
        } else {
            this.widthBtn = "230px";
        }
    };

    this.setHeightBtn = function (heightBtn) {
        if (this.smartBtn) {
            if (heightBtn) {
                this.heightBtn = heightBtn;
            } else {
                this.heightBtn = $(this.idBtnSmartBtn).height()+'px';
            }
        } else if (heightBtn) {
            this.heightBtn = heightBtn;
        } else {
            this.heightBtn = "60px";
        }
    };

    this.setMarginTopBtn = function (marginTopBtn) {
        this.marginTopBtn = marginTopBtn;
    };

    this.setMarginLeftBtn = function (marginLeftBtn) {
        this.marginLeftBtn = marginLeftBtn;
    };

    this.setMarginRightBtn = function (marginRightBtn) {
        this.marginRightBtn = marginRightBtn;
    };

    this.setPositionBtn = function (positionBtn) {
        this.positionBtn = positionBtn;
    };

    this.setIdBtnAddToCart = function (idBtnAddToCart) {
        this.idBtnAddToCart = idBtnAddToCart;
    };

    this.setIdSmartBtn = function (idBtnSmartBtn) {
        this.idBtnSmartBtn = idBtnSmartBtn;
    };

    this.setErrorText = function (errorText) {
        this.errorText = errorText;
    };

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
    this.isProductAvailable = function () {
        var productExported = this.isProductExported();
        var product = productExported.product;

        if (this.stockManagement && !this.allowOosp) {
            if (product.productAttributeId == 0) {
                return 0 < this.productQuantity;
            } else {
                return 0 < this.combinations[product.productAttributeId].quantity;
            }
        } else {
            return true
        }

        return false;
    };

    /**
     * Watch any change about the variations of the product
     */
    this.watcherCombination = function () {
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
    this.prepareButton = function () {
        var selectors = this.idBtnAddToCart.split(',');
        var oystOneClick = this;

        $.each(selectors, function (index, selector) {
            if ($(selector).length) {
                // Avoid any event issue due to potential remove / create from loaded oyst script
                if (oystOneClick.positionBtn == 'after') {
                    $(selector).after($('<div>', {
                        id: 'oneClickContainer'
                    }));
                } else {
                    $(selector).before($('<div>', {
                        id: 'oneClickContainer'
                    }));
                }
                return false;
            }
        });

        $('#oneClickContainer').css({
            'margin-top': this.marginTopBtn,
            'margin-left': this.marginLeftBtn,
            'margin-right': this.marginRightBtn
        }).append($('<div>', {
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
            'data-rounded', this.borderBtn ? 'true' : 'false'
        ).attr(
            'data-smart', this.smartBtn ? 'true' : 'false'
        ));

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

        var quantity = $('input[name="qty"]').val();
        if (typeof quantity === "undefined")
            quantity = 1;

        // if (this.shouldAsStock) {
        //     if ($('#quantityAvailable').length && parseInt($('#quantityAvailable').html()) < quantity) {
        //         if (!!$.prototype.fancybox) {
        //             $.fancybox.open([
        //               {
        //                 type: 'inline',
        //                 autoScale: true,
        //                 minHeight: 30,
        //                 content: '<p class="fancybox-error">' + this.errorText + '</p>'
        //               }
        //             ], {
        //               padding: 0
        //             });
        //             return;
        //         } else {
        //             alert(this.errorText);
        //             return;
        //         }
        //     }
        // }

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
        var params = this.getSelectedProduct();

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
