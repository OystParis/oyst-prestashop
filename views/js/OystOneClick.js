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
        $('#add_to_cart').append($('<div>', {
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
