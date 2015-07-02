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
            'applyCountry': function(value) {
                var
                    key = $tax_system[value] ? value : 'default',
                    tax_system = $tax_system[key],
                    target = $('select[data-action="applyTaxSystem"]');

                $current.country_code = key;

                target.find('option').detach();
                $.each(tax_system, function() {
                    target.append(
                        $('<option />').text(this.label).val(this.label)
                    );
                });
                target.trigger('change');
            },
            'applyTaxSystem': function(value) {
                var tax_system = $tax_system[$current.country_code],
                    target = $('[data-value="vatRate"]');

                $.each(tax_system, function() {
                    if (this.label == value)
                        $current.tax_system = this;
                });

                target.val($current.tax_system.vat_rate).parent('div').show();
                if ($.isFunction($current.tax_system.action))
                   $current.tax_system.action(target);
            }
        };

    $('select[data-action]')
        .change(function() {
            var action = $(this).data('action'),
                value = $(this).find('option:selected').val();

            if ($.isFunction($actions[action]))
                $actions[action](value);
        })
        .trigger('change');

});

/*
var taxSystem = [
    {
        name: 'PackageA',
        locationOptions : [
            { location: "UK", price: 1 },
            { location: "USA", price: 2 }
        ]
    },
    {
        sku : "101",
        name: "PackageB",
        description: "its cool",
        locationOptions : [
            { location: "UK", price: 5 },
            { location: "USA", price: 6 },
            { location: "Pluto", price: 1 }
        ]
    }
];
*/
/*
function OrganizationViewModel() {
    var self = this;

    self.answers = ko.observableArray([
        {text: "1"}, {text: "2"}, {text: "3"}
    ]);
    self.applyCountry = ko.observable(function() {
        console.log(this);
    });
}

ko.applyBindings(new OrganizationViewModel());
*/

/*
 echo $form->field($model, 'country_id')->dropDownList([
 '1' => 'test1',
 '2' => 'test2',
 '3' => 'test3',
 ], [
 'data-bind' => 'value: applyCountry',
 'options' => [
 '1' => ['data-bind' => 'value: 1, text: text'],
 '2' => ['data-bind' => 'value: 2, text: text'],
 '3' => ['data-bind' => 'value: 3, text: text'],
 ],
 ]);
*/