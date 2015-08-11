jQuery(document).ready(function() {

    var $virtualTypeAction = function() {
        var $value = $(this).val(),
            $type_blocks = $('[data-type]');

        $type_blocks
            .parents('div.form-group')
                .parent('div')
                     .hide()
            .find('[data-type="' + $value + '"]')
                .parents('div.form-group')
                    .parent('div')
                        .show();
    };

    $('input[name="virtual_type"]')
        .bind('change', $virtualTypeAction)
        .filter(':checked')
            .bind('input', $virtualTypeAction)
                .trigger('input');

});