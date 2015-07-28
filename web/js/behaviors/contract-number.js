jQuery(document).ready(function() {

    /**
     * TypeId: [not possibility states]
     */
    var $excludeStates = {
        3: [], // Operator
        'default': ['external']
    };

    var $statesAction = {
        'external': function() {
            $contractNumber.parents('div.field-parent').show();
            $('div[data-not-external="1"]').hide();
        },
        'default': function() {
            $contractNumber.parents('div.field-parent').hide();
            $('div[data-not-external="1"]').show();
        }
    };

    var $contractType = $('select[name*="contract_type_id"]'),
        $contractState = $('select[name*="state"]'),
        $contractNumber = $('input[name*="number"]');

    $contractType
        .on('change', function() {
            var value = $(this).find(':selected').val(),
                states = $excludeStates[ value ] ? $excludeStates[ value ] : $excludeStates['default'];

            if (states.length) {
                for (var i=0; i<states.length; i++) {
                    $contractState
                        .find('option[value="' + states[i] + '"]')
                            .prop('disabled', true)
                            .prop('selected', false);
                }
            }
            else
                $contractState.find('option').prop('disabled', false);
            $contractState.trigger('change');
        })
        .trigger('change');

    $contractState
        .on('change', function() {
            var value = $(this).find(':selected').val();

            if ($.isFunction($statesAction[ value ]))
                $statesAction[ value ]();
            else
                $statesAction['default']();
        })
        .trigger('change');
});