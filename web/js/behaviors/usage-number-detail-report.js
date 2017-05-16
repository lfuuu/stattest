jQuery(document).ready(function() {

    var ndcTypeList = $('input[type=radio].ndcTypeList');

    ndcTypeList.change(function () {
        var ndcTypeId = ndcTypeList.filter(':checked').val();

        var subList = $('input[type=checkbox].subList');

        subList
            .parent() //label
            .css('color', 'gray');

        subList
            .filter('[data-ndc-type-id=' + ndcTypeId + ']')
            .parent() //label
            .css('color', 'black');

    }).first().trigger('change');
});