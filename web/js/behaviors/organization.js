jQuery(document).ready(function() {

    var $current = {},
        $tax_system = {
            643: [
                {'label': 'ОСНО', 'vat_rate': 18},
                {
                    'label': 'УСН',
                    'vat_rate': 0,
                    'action': function(element) {
                        element.parent('div').hide();
                    }
                }
            ],
            348: [
                {'label':'ОСНО', 'vat_rate': 27}
            ],
            'default': [
                {'label':'ОСНО', 'vat_rate': 0}
            ]
        },
        $actions = {
            'applyCountry': function() {
                var
                    value = $(this).find('option:selected').val(),
                    target = $(this).data('target'),
                    key = $tax_system[value] ? value : 'default',
                    tax_system = $tax_system[key];

                $current.country_code = key;

                $(target).find('option').detach();
                $.each(tax_system, function() {
                    $(target).append(
                        $('<option />')
                            .text(this.label)
                            .val(this.label)
                            .prop('selected', this.label == $(target).data('value'))
                    );
                });
                $(target).trigger('change');
            },
            'applyTaxSystem': function() {
                var
                    value = $(this).find('option:selected').val(),
                    tax_system = $tax_system[$current.country_code],
                    target = $(this).data('target');

                $.each(tax_system, function() {
                    if (this.label == value)
                        $current.tax_system = this;
                });

                $(target)
                    .val($(target).data('value') ? $(target).data('value') : $current.tax_system.vat_rate)
                    .parent('div')
                        .show();
                if ($.isFunction($current.tax_system.action))
                   $current.tax_system.action($(target));
            }
        };

    $('select[data-action]')
        .change(function() {
            var action = $(this).data('action');

            if ($.isFunction($actions[action]))
                $.proxy($actions[action], $(this))();
        })
        .trigger('change');

});