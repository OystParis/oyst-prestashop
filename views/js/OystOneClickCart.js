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
function OystOneClickCart(url, controller)
{

    this.url = url;
    this.controller = controller;
    this.idBtnAddToCart = '.standard-checkout';
    this.smartBtn = true;
    this.borderBtn = true;
    this.themeBtn = 'normal';
    this.colorBtn = '#E91E63';
    this.widthBtn = '230px';
    this.heightBtn = '60px';
    this.marginTopBtn = '0px';
    this.marginLeftBtn = '0px';
    this.marginRightBtn = '0px';
    this.labelCta = 'Return shop.';
    this.preload = 1;

    this.setPreload = function (preload) {
        this.preload = preload;
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
                this.widthBtn = $(this.idBtnAddToCart).width()+'px';
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
                this.heightBtn = $(this.idBtnAddToCart).outerHeight()+'px';
            }
        } else if (heightBtn) {
            this.heightBtn = heightBtn;
        } else {
            this.heightBtn = "60px";
        }
    };

    this.setSmartBtn = function (smartBtn) {
        this.smartBtn = smartBtn;
    };

    this.setBorderBtn = function (borderBtn) {
        this.borderBtn = borderBtn;
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

    this.setIdBtnAddToCart = function (idBtnAddToCart) {
        this.idBtnAddToCart = idBtnAddToCart;
    }

    this.setLabelCta = function (labelCta) {
        this.labelCta = labelCta;
    };

    /**
     * Initialize requirements
     */
    this.prepareButton = function () {
        $(this.idBtnAddToCart).after($('<div>', {
            id: 'oneClickContainer'
        }));

        $('#oneClickContainer').css({
            'margin-top': this.marginTopBtn,
            'margin-left': this.marginLeftBtn,
            'margin-right': this.marginRightBtn
        }).append($('<div>', {
            id: 'oyst-1click-button'
        }).attr(
            'data-theme',
            this.themeBtn
        ).attr(
            'data-color',
            this.colorBtn
        ).attr(
            'data-width',
            this.widthBtn
        ).attr(
            'data-height',
            this.heightBtn
        ).attr(
            'data-rounded',
            this.borderBtn ? 'true' : 'false'
        ).attr(
            'data-smart',
            this.smartBtn ? 'true' : 'false'
        ));
    };

    /**
     * Send request to start oneClick process
     */
    this.requestOneCLick = function (oystCallBack) {
        var params = {};

        params.controller = this.controller;
        if (this.preload) {
            params.preload = this.preload;
            this.setPreload(0);
        } else {
            params.preload = this.preload;
        }

        params.labelCta = this.labelCta;
        params.oneClick = true;
        params.token = '{SuggestToAddSecurityToken}';

        $.post(this.url, params, function (json) {
            if (json.state) {
                oystCallBack(null, json.url);
            } else {
                // display properly the error to try again
                alert('Error occurred, please try later or contact us');
            }
        });
    }
};
