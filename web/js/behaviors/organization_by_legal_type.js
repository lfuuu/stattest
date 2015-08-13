/**
 * Предназначение:
 *   Создание зависимости между типом контрагента и организацией по-умолчанию.
 *
 * Использование:
 *   Зависимость между типом контрагента и организацией задается на строке №24 в массиве $defaultOrganization.
 *      [ключ типа контрагента] - [ID организации]
 *      [default] - [ID организации]
 *   Зависимость "default" срабатывает для всех типов контрагентов не найденых в списке зависимостей
 *
 *   поведение срабатывает в случае наличия на форме:
 *     #type-select button - список кнопок табов, переключающих зависимости от типа контрагента
 *     select[name*="organization_id"] - список организаций
 *   или
 *     select[name*="contragent_id"] - список существующих контрагентов
 *
 *   при необходимости селекторы полей можно изменить на строках №26, №27, №28
 *
 * Подключение:
 *   <script type="text/javascript" src="/js/behaviors/organization_by_legal_type.js"></script>
 *
 */

jQuery(document).ready(function() {

    var
        $defaultOrganization = {
            'legal': 1, // ООО "МСН Телеком"
            'default': 11 // ООО "МСМ Телеком"
        },
        $legalTypes = $('#type-select button'),
        $organizations = $('select[name*="organization_id"]'),
        $contragents = $('select[name*="contragent_id"]'),
        $applyOrganization = function(value) {
            var organization =
                $defaultOrganization[value]
                    ? $defaultOrganization[value]
                    : $defaultOrganization['default'];

            $organizations
                .find('option[value="' + organization + '"]')
                .prop('selected', true)
                .trigger('change');
        };

    $legalTypes
        .on('click', function() {
            $applyOrganization($(this).data('tab').replace(/#/, ''));
        })
        .filter('.btn-primary')
            .trigger('click');

    $contragents
        .on('change', function() {
            $applyOrganization($(this).find('option:selected').data('legal-type'));
        });
    if ($organizations.data('is-new') == 1)
        $contragents.trigger('change');

});