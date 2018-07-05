"use strict";

let firstLoad = true;

window.__OYST__ = window.__OYST__ || {};

window.__OYST__.getCart = async function() {
    console.log('passage get cart oyst.js');

    if (firstLoad) {
        firstLoad = false;
    } else {
        if (prestashop.page.page_name === 'product') {
            await addProductToCart();
        }
        return getCartId();
    }
};
//arrow function

function addProductToCart()
{
    return new Promise((resolve, reject) => {
        var xhr = new XMLHttpRequest();
        var url = prestashop.urls.pages.cart;
        var params = $('#add-to-cart-or-refresh').serialize() + "&add=1&action=update";
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = () => {
            //check status si != 200 => reject
            resolve(xhr.responseText);
        }
        xhr.onerror = () => reject(new Error(`Received code ${xhr.status} from GET id cart`));
        xhr.send(params);
    });
}

function getCartId() {
    return new Promise((resolve, reject) => {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", prestashop.urls.base_url + 'module/oyst/ajax?ajax=true&action=get_id_cart');
        xhr.onload = () => {
            resolve(xhr.responseText);
        }
        xhr.onerror = () => reject(new Error(`Received code ${xhr.status} from GET id cart`));
        xhr.send();
    });
}
