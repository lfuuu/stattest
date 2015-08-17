jQuery(document).ready(function() {

    var $title = 'Адрес будет установлен автоматически',
        $placeholder = '',
        $setPlaceholder = function() {
            if ($(this).val()) {
                $(this).attr('title', '');
                $(this).removeClass('input_help_icon');
                $placeholder = $(this).attr('placeholder');
            }
            else {
                $(this).attr('title', $title);
                $(this).addClass('input_help_icon');
                $(this).attr('placeholder', $placeholder ? $placeholder : $(this).data('datacenter-address'));
            }
        };

    $('input[name*="address"]')
        .on('input keyup', $setPlaceholder)
        .trigger('input');

});