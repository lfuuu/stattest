/**
 * Предназначение:
 *   Фильтрация списка доступных прайслистов от значение флага "Цена включает НДС"
 *
 * Использование:
 *   в форме должны существовать поля соответствующие селекторам:
 *      input[name="price_include_vat"] - флаг "Цена включает НДС"
 *      select[name="pricelist_id"] - выпадающий список с прайслистами
 *   при необходимости селекторы полей можно изменить на строках №19, №37
 *
 * Подключение:
 *   <script type="text/javascript" src="/js/behaviors/pricelist-voip-filter.js"></script>
 *
 */

jQuery(document).ready(function() {
    var $pricelistsElement = $('select[name="pricelist_id"]'),
        $pricelistsValues = $pricelistsElement.find('option'),
        $pricelistCurrent = $pricelistsValues.filter(':selected'),
        $priceIncludeVatChange = function() {
            var
                $type = $(this).is(':checked') ? 1 : '',
                $values = $pricelistsValues.filter('[data-type="' + $type + '"]');

            $('option', $pricelistsElement).detach();
            $pricelistsElement
                .select2('val', null)
                .append($values)
                .find('[value=' + $pricelistCurrent.val() + ']')
                .prop('selected', true)
                .trigger('change');

        };

    $('input[name="price_include_vat"]')
        .bind('click', $priceIncludeVatChange)
        .bind('input', $priceIncludeVatChange)
            .trigger('input');

});