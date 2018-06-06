"use strict";

function oystOneClick() {
    //If on product page, add product to cart
    if ($('#product').length) {
        let params = $('#add-to-cart-or-refresh').serialize() + "&add=1&action=update";

        $.ajax({
            type: "POST",
            url: "http://prestashop-1_7_3_0.dijon.bwagence.fr:8084/panier",
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
        url: oyst_ajax_url,
        data: {'action':'get_id_cart'},
        dataType: "json",
        success: function (data) {
            console.log(data);
        },
        error: function() {
            console.log('Error :o');
        }
    })
}
