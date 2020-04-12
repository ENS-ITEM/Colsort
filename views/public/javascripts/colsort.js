$ = jQuery;
$(document).ready(function() {
    $('.montrer').click(function() {
        $(this).parent().nextAll('div.notices, div.collections').toggle();
    });
    $('.tout').click(function() {
        if ($(this).html() == 'Tout replier') {
            $(this).html('Tout d&eacute;plier');
            $('div.notices, div.collections').hide();
        } else {
            $(this).html('Tout replier');
            $('div.notices, div.collections').show();
        }
    });
});
