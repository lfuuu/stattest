/**
 * Предназначение:
 *   Получение списков менеджеров/аккаунт менеджеров в зависиомсти от типа договора и отдела.
 *
 * Использование:
 *   В форме должны существовать поля соответствующие селекторам:
 *      [name*="manager"], [name*="account_manager"] - изменяемые списки
 *      [name*="contract_type_id"] - список типов договора
 *   при необходимости селекторы полей можно изменить на строках №21, №22
 *   Зависимость типа договора и отдела задаается на строке №23 в массиве $contractTypeDepts
 *
 * Подключение:
 *   <script type="text/javascript" src="/js/behaviors/managers_by_contract_type.js"></script>
 *
 */

jQuery(document).ready(function() {

    var
        $managerList = '[name*="manager"], [name*="account_manager"]',
        $contractType = '[name*="contract_type_id"]',
        /** ContractTypeId -> DeptId */
        $contractTypeDepts = {
            '4': 29, // Закупки
            '5': 29, // Закупки
            'default': 28  // Sales
        },
        $getManagers = function(deptId) {
            $.ajax({
                url: '/user/control/ajax-dept-users?id=' + deptId,
                dataType: 'json',
                success: function (result) {
                    $($managerList).each(function() {
                        var element = $(this),
                            currentValue = element.data('current-value');

                        element.find('option:gt(0)').detach();
                        element.select2({
                            'val': null,
                            'allowClear': true
                        });

                        $.each(result, function () {
                            element.append(
                                $('<option />')
                                    .text($(this).attr('text'))
                                    .val($(this).attr('id'))
                            );
                        });

                        element.find('[value=' + currentValue + ']')
                            .prop('selected', true)
                            .trigger('change');
                    });
                }
            });
        };

    $($contractType)
        .on('change', function() {
            var value = $(this).find(':selected').val();
            if ($contractTypeDepts[ value ])
                $getManagers($contractTypeDepts[ value ]);
            else
                $getManagers($contractTypeDepts['default']);
        })
        .trigger('change');

});