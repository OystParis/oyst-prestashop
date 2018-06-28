"use strict";

window.__OYST__ = window.__OYST__ || {};

window.__OYST__.getCart = () => {
    return 16;
    console.log('passage');
    //If on product page, add product to cart
    if (typeof prestashop.page.page_name !== "undefined" && prestashop.page.page_name === 'product') {
        var params = $('#add-to-cart-or-refresh').serialize() + "&add=1&action=update";

        return $.ajax({
            type: "POST",
            url: prestashop.urls.pages.cart,
            data: params,
            dataType: "json"
        }).done(function() {
            console.log('product added to cart');
            return getIdCart();
        });
    } else {
        return getIdCart();
    }
};

function getIdCart() {
    return $.ajax({
        type: "GET",
        url: prestashop.urls.base_url+'module/oyst/ajax',
        data: {
            'ajax': true,
            'action':'get_id_cart'
        },
        dataType: "json"
    }).done(function(data) {
        console.log('return id_cart');
        console.log(data['id_cart']);
        return data['id_cart'];
    })
}
