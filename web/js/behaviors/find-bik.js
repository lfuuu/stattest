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

    $('#accounteditform-custom_properties').on('click', function(e){
        var f = $('#accounteditform-corr_acc, #accounteditform-bank_name, #accounteditform-bank_city, #accounteditform-bank_properties');
        f.prop('disabled',  !f.prop('disabled'));
    });
    $('#accounteditform-corr_acc, #accounteditform-bank_name, #accounteditform-bank_city, #accounteditform-pay_acc').on('blur', function(){
        genBankProp();
    });

    $(' #accounteditform-pay_acc').closest('form').on('submit', function(){
        var f = $('#accounteditform-corr_acc, #accounteditform-bank_name, #accounteditform-bank_city, #accounteditform-bank_properties');
        f.prop('disabled',  false);
    })

    var substringMatcher = function () {
        return function findMatches(q, cb) {
            $.getJSON('search/bank', {
                search: $("#accounteditform-bik").val()
            }, function (matches) {
                cb(matches);
            });
        };
    };

    function genBankProp()
    {
        var pa = $('#accounteditform-pay_acc').val();
        var ca = $('#accounteditform-corr_acc').val();
        var bn = $('#accounteditform-bank_name').val();
        var bc = $('#accounteditform-bank_city').val();
        var v = 'р/с '+ pa + "\n" + bn + ' ' + bc + (ca ? ("\n" +'к/с '+ ca) : '');
        $('#accounteditform-bank_properties').val(v);
    }

    $('#accounteditform-bik').typeahead({
            autoselect: true,
            hint: true,
            highlight: true,
            minLength: 3,
            async: true,
        },
        {
            name: 'accounteditform-bik',
            source: substringMatcher(),
            templates: {
                suggestion: function(obj){ return obj['value']; }
            }
        })
        .on('typeahead:selected', function($e, data) {
            $('#accounteditform-bank_name').val(data['bank_name']);
            $('#accounteditform-corr_acc').val(data['corr_acc']);
            $('#accounteditform-bank_city').val(data['bank_city']);
            genBankProp();
        });

});