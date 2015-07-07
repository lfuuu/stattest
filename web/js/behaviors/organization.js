/**
 * Использование:
 *   при необходимости базовые величины налоговой ставки меняются на строке №12 в массиве $vat_rate_by_country
 *
 * Подключение:
 *   <script type="text/javascript" src="/js/behaviors/organization.js"></script>
 *
 */

jQuery(document).ready(function() {

    var $vat_rate_by_country = {
            // Россия
            643: {'vat_rate': 18},
            // Венгрия
            348: {'vat_rate': 27},
            // Иная страна
            'default': {'vat_rate': 0}
        },
        $country_select = $('select#Country'),
        $vat_rate_input = $('input#VatRate'),
        $simple_tax_system_checkbox = $('input#IsSimpleTaxSystem'),
        init = function() {
            if (
                !$simple_tax_system_checkbox.is(':checked') &&
                !$country_select.find(':selected').val()
            )
                $country_select.trigger('change', {default: 1});
            if ($simple_tax_system_checkbox.is(':checked'))
                $vat_rate_input.parent('div').hide();
        };

    $country_select
        .change(function(e, input) {
            var $value = $(this).find(':selected').val();

            if (input && input.default) {
                $(this).find('option').eq(input.default).prop('selected', true).trigger('change');
                return false;
            }
            if (!$value) {
                $(this).find('option').eq(1).prop('selected', true).trigger('change');
                return false;
            }

            var $country = $vat_rate_by_country[$value] ? $value : 'default';

            if ($vat_rate_input.parent('div').is(':visible'))
                $vat_rate_input.val($vat_rate_by_country[ $country ][ 'vat_rate' ]);
        });

    $simple_tax_system_checkbox
        .click(function() {
            var $status = $(this).is(':checked'),
                $country = $country_select.find(':selected').val();

            if ($status) {
                $vat_rate_input.parent('div').hide();
                $vat_rate_input.val(0);
            }
            else {
                $vat_rate_input.parent('div').show();
                $vat_rate_input.val($vat_rate_by_country[ $vat_rate_by_country[ $country ] ? $country : 'default' ][ 'vat_rate' ]);
            }
        });

    init();

});