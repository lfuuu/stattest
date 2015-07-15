jQuery(document).ready(function() {

    $('.select2-tag-support').select2({
        tags: [],
        tokenSeparators: [',']
    });

    var $type_id = $('input[name*="type_id"]'),
        $type_action = function() {
            var $value = $type_id.filter(':checked').val();
            $('div.type-id')
                .hide()
                .filter('[data-value="' + $value + '"]')
                .show();
        };

    $type_id
        .change($type_action)
        .eq(1)
            .trigger('change');
});