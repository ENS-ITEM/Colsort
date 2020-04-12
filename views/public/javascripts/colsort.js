$ = jQuery;
$(document).ready(function() {
    $('.montrer').click(function() {
        $(this).parent().nextAll('div.notices, div.collections').toggle();
    });
    $('.tout').click(function() {
        $('div.notices, div.collections').toggle();
        if ($(this).html() == 'Tout replier') {
            $(this).html('Tout d&eacute;plier');
        } else {
            $(this).html('Tout replier');
        }
    });
});
