jQuery(document).ready(function() {
    var $pointsElement = $('select[name*="connection_point_id"]'),
        $pointsValues = $pointsElement.find('option'),
        $pointCurrent = $pointsValues.filter(':selected'),
        $connectionPointChange = function() {
            var
                $country = $(this).find(':selected'),
                $values = $pointsValues.filter('[data-country-id="' + $country.val() + '"]');

            $('option:gt(0)', $pointsElement).detach();
            $pointsElement
                .select2('val', null)
                .append($values)
                .find('[value="' + $pointCurrent.val() + '"]')
                    .prop('selected', true)
                .trigger('change');
        };

    $('select[name*="country_id"]')
        .bind('change', $connectionPointChange)
        .trigger('change');

});