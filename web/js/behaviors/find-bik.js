/**
 * Предназначение:
 *   Получение доп. информации о банке по его БИК
 *
 * Использование:
 *   на элементе input выставляются аттрибуты:
 *      class = search-bik
 *   в форме должны существовать поля соответствующие селекторам:
 *      [name*="bank_correspondent_account"] - имя поля содержит bank_correspondent_account (рассчетный счет)
 *      [name*="bank_name"] - имя поля содержит bank_name (название банка)
 *   при необходимости селекторы полей можно изменить на строке №18 в массиве $fields
 *
 * Подключение:
 *   <script type="text/javascript" src="/js/behaviors/find-bik.js"></script>
 *
 */

jQuery(document).ready(function() {

    $('input.search-bik').bind('keyup change', function() {
        var $that = $(this),
            $fields = {
                'corr_acc' : '[name*="bank_correspondent_account"]',
                'bank_name' : '[name*="bank_name"]'
            };

        $.ajax({
            url: '/data/rpc-find-bank-1c/?value=' + $that.val(),
            dataType: 'json',
            beforeSend: function() {
                $that.addClass('ui-autocomplete-loading');
            },
            success: function(result) {
                try {
                    result = $.parseJSON(result);
                } catch (e) {
                    alert('JSON parse error, check URL for reason');
                }

                for (var key in result) {
                    if (result.hasOwnProperty(key) && $fields[key]) {
                        $('input' + $fields[key]).val(result[key]);
                    }
                }

                $that.removeClass('ui-autocomplete-loading');
            }
        });
    });

});