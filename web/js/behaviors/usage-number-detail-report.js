jQuery(document).ready(function() {

    var numberTypeList = $('input[type=radio].numberTypeList');

    numberTypeList.change(function () {
        var numberTypeId = numberTypeList.filter(':checked').val();

        var subList = $('input[type=checkbox].subList');

        subList
            .parent() //label
            .css('color', 'gray');

        subList
            .filter('[data-number-type-id=' + numberTypeId + ']')
            .parent() //label
            .css('color', 'black');

    }).first().trigger('change');
});