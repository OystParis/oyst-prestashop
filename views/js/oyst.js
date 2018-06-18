"use strict";

window.__OYST__ = window.__OYST__ || {};

window.__OYST__.getCartPageItems = () => {
    console.log('passage');
    return 18;
};

function oystOneClick() {
    //If on product page, add product to cart
    if ($('#product').length) {
        var params = $('#add-to-cart-or-refresh').serialize() + "&add=1&action=update";

        $.ajax({
            type: "POST",
            url: prestashop.urls.pages.cart,
            data: params,
            dataType: "json",
            success: function (data) {
                getIdCart();
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });
    } else {
        getIdCart();
    }
}

function getIdCart() {
    $.ajax({
        type: "GET",
        url: prestashop.urls.base_url+'module/oyst/ajax',
        data: {
            'ajax': true,
            'action':'get_id_cart'
        },
        dataType: "json",
        success: function (data) {
            console.log(data);
        },
        error: function() {
            console.log('Error :o');
        }
    })
}
