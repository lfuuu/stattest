jQuery(document).ready(function() {

    var $set_country_flag = function(current) {
        var $element = $(current.element);

        // element is optgroup
        if (!current.id)
            return current.text;

        return $('<div />')
                .addClass('country-flag')
                .addClass('country-flag-' + $element.data('country-id'))
                .append(
                    $('<div />')
                        .addClass('country-name-text')
                        .text(current.text)
                );
    }

    $('.select2-with-flags').select2({
        formatResult: $set_country_flag,
        formatSelection: $set_country_flag
    });

});