$ = jQuery;
$(document).ready(function() {
    $('.montrer').click(function() {
        $(this).parent().find('> .notices, > .collections').toggle();
    });
    $('.tout').click(function() {
        if ($(this).html() == 'Tout replier') {
            $(this).html('Tout d&eacute;plier');
            $('#collection-tree .notices, #collection-tree .collections').hide();
        } else {
            $(this).html('Tout replier');
            $('#collection-tree .notices, #collection-tree .collections').show();
        }
    });
});
