jQuery(document).ready(function() {
    var $pricelistsElement = $('select[name="pricelist_id"]'),
        $pricelistsValues = $pricelistsElement.find('option'),
        $pricelistCurrent = $pricelistsValues.filter(':selected'),

        $price_include_vat = $('input[name="price_include_vat"]');

    $('select[name="type"]')
        .change(function() {
            var
                $type = $(this).find(':selected').val(),
                $values = $pricelistsValues.filter('[data-type="' + $type + '"]');

            if ($type == 'operator') {
                $price_include_vat.prop('checked', false);
            }
            else {
                $price_include_vat.prop('checked', true);
            }

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