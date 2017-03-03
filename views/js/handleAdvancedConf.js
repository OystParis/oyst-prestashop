$(document).ready(function() {
    $('#toggleConfig').click(function() {
        $('.advancedOptions').toggle();
        $('i', this).toggleClass('icon-eye').toggleClass('icon-eye-close');
        $('span', this).toggle();
    });
});
