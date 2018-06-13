console.log('Super tag chargé !');

$(function() {
    $('#content-wrapper').prepend('<input type="button" value="Oyst 1C" id="super-btn">');
    $('#super-btn').on('click', function(){
        console.log('Call oystOneClick, result :');
        console.log(oystOneClick());
    });
});
