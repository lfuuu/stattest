jQuery(document).ready(function() {
    var $pricelistsElement = $('select[name*="pricelist_id"]'),
        $pricelistsValues = $pricelistsElement.find('option'),
        $pricelistCurrent = $pricelistsValues.filter(':selected'),
        $priceIncludeVatChange = function() {
            var
                $type = $(this).is(':checked') ? 1 : 0,
                $values = $pricelistsValues.filter('[data-type="' + $type + '"]');

            $('option:gt(0)', $pricelistsElement).detach();
            $pricelistsElement
                .select2('val', null)
                .append($values)
                .find('[value="' + $pricelistCurrent.val() + '"]')
                    .prop('selected', true)
                .trigger('change');
        };

    $('input[name*="price_include_vat"]')
        .bind('click', $priceIncludeVatChange)
        .bind('input', $priceIncludeVatChange)
            .trigger('input');

});