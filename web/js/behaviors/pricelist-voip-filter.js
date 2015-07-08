jQuery(document).ready(function() {
    var $pricelistsElement = $('select[name="pricelist_id"]'),
        $pricelistsValues = $pricelistsElement.find('option'),
        $pricelistCurrent = $pricelistsValues.filter(':selected');

    $('select[name="type"]')
        .change(function() {
            var $values = $pricelistsValues.filter('[data-type="' + $(this).find(':selected').val() + '"]');

            $('option', $pricelistsElement).detach();
            $pricelistsElement
                .select2('val', null)
                .append($values)
                    .find('[value=' + $pricelistCurrent.val() + ']')
                        .prop('selected', true)
                .trigger('change');
        })
        .trigger('change');

});