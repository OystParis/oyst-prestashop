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
    }

    /**
     * Initialize requirements
     */
    prepareButton() {
        $('#add_to_cart').before($('<div>', {
            id: 'oyst-1click-button'
        }));
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
