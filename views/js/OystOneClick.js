/**
 * Manage oneClick process
 */
class OystOneClick {

    /**
     * Constructor
     * @param url
     */
    constructor(url) {
        this.url = url;
    }

    /**
     * Initialize requirements
     */
    prepareButton() {
        $('#add_to_cart').append($('<div>', {
            id: 'oyst-1click-button'
        }));
    }

    /**
     * Set product and combinations information
     * @param productInfo
     * @returns {OystOneClick}
     */
    setProductInfo(productInfo) {
        this.product = productInfo.product;
        this.productCombinations = productInfo.combinations;

        return this;
    }

    /**
     * On Click, retrieve the right product / combination information
     * @returns {{productId, productAttributeId: *, quantity: (*|jQuery)}}
     */
    getSelectedProduct() {

        let productAttributeId = null;

        if (selectedCombination != undefined) {
            $.each(this.productCombinations, function (index, combination) {
                if (combination.reference == selectedCombination.reference) {
                    productAttributeId = combination.id_attribute;
                    return;
                }
            })
        }

        return {
            productId: this.product.id,
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

        $.ajax({
            url: this.url,
            method: 'POST',
            data: params,
            success: function (json) {
                if (json.state) {
                    oystCallBack(null, json.url);
                } else {
                    // display properly the error to try again
                    alert('Error occurred, please try later or contact us');
                }
            }
        });
    }
}
