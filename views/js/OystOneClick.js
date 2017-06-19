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

/**
 * Manage oneClick process
 */
class OystOneClick {

    /**
     * Constructor
     * @param url
     */
    constructor(url, productId) {
        this.url = url;
        this.productId = productId;
        this.combinations = [];
        this.button = '#oneClickContainer';
    }

    setExportedCombinations(combinations) {
        this.combinations = combinations;
    }

    isProductCombinationExported() {
        let product = this.getSelectedProduct();
        let isExported = this.combinations.indexOf(product.productAttributeId);

        return isExported >= 0;
    }

    watcherCombination() {
        if (!this.isProductCombinationExported() || !quantityAvailable) {
            $(this.button).hide();
        } else if (quantityAvailable) {
            $(this.button).show();
        }

        let object = this;
        window.setTimeout(function () {
            object.watcherCombination();
        }, 100);
    }

    prepareEvents() {
        if (this.combinations.length) {
            // Value is changed by PrestaShop code, we need to check using a timer
            this.watcherCombination();
        }
    }

    /**
     * Initialize requirements
     */
    prepareButton() {
        // Avoid any event issue due to potential remove / create from loaded oyst script
        $('#add_to_cart').before($('<div>', {
            id: 'oneClickContainer'
        }));

        $('#oneClickContainer').append($('<div>', {
            id: 'oyst-1click-button'
        }));

        this.prepareEvents();

        if (!quantityAvailable) {
            $(this.button).hide();
        }
    }

    /**
     * On Click, retrieve the right product / combination information
     * @returns {{productId, productAttributeId: *, quantity: (*|jQuery)}}
     */
    getSelectedProduct() {

        let productAttributeId = null;

        if ($('#idCombination').val() != undefined) {
            productAttributeId = $('#idCombination').val();
        }

        return {
            productId: this.productId,
            productAttributeId: productAttributeId,
            quantity: $('input[name="qty"]').val(),
        }
    }

    /**
     * Send request to start oneClick process
     */
    requestOneCLick(oystCallBack) {

        let params = Object.assign({}, this.getSelectedProduct(), {
            oneClick: true,
            token: '{SuggestToAddSecurityToken}'
        });

        $.post(this.url, params, function(json) {
            if (json.state) {
                oystCallBack(null, json.url);
            } else {
                // display properly the error to try again
                alert('Error occurred, please try later or contact us');
            }
        });
    }
}
