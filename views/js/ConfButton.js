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
 * Configuration button oneClick
 */
$(document).ready(function () {
    if (displayBtnCart) {
        var oyst = new OystOneClickCart(oneClickUrl, controller);
        oyst.setLabelCta(oyst_label_cta);
    } else {
        var oyst = new OystOneClick(oneClickUrl, product.id, controller);
        oyst.setExportedCombinations(synchronizedCombination);
        oyst.setAllowOosp(allowOosp);
        oyst.setProductQuantity(productQuantity);
        oyst.setStockManagement(stockManagement);
        oyst.setShouldAskStock(shouldAsStock);
        oyst.setErrorText(oyst_error);
    }
    oyst.setIdBtnAddToCart(idBtnAddToCart);
    oyst.setIdSmartBtn(idSmartBtn);
    oyst.setSmartBtn(smartBtn);
    oyst.setBorderBtn(borderBtn);
    oyst.setThemeBtn(themeBtn);
    oyst.setColorBtn(colorBtn);
    oyst.setWidthBtn(widthBtn);
    oyst.setHeightBtn(heightBtn);
    oyst.setMarginTopBtn(marginTopBtn);
    oyst.setMarginLeftBtn(marginLeftBtn);
    oyst.setMarginRightBtn(marginRightBtn);
    oyst.setPositionBtn(positionBtn);
    oyst.prepareButton();

    window.addEventListener('message', function (event) {
        if (event.data.type == 'ORDER_REQUEST') {
            oyst.setPreload(0);
        }
    });

    window.__OYST__ = window.__OYST__ || {};
    window.__OYST__.getOneClickURL = function (callback) {
        oyst.requestOneCLick(callback);
    };
});
