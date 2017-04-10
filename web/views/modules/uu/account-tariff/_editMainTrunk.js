+function ($, param) {
    'use strict';

    $(function () {
        $('#accounttariff-region_id').on('change', function () {
            $.get(
                '/uu/voip/get-trunks', {
                    regionId: $(this).val(),
                    format: param.format
                }, function (html) {
                    $('#accounttariff-trunk_id')
                        .html(html)
                        .trigger('change');
                }
            );
        });
    })
}(
    jQuery,
    window.frontendVariables.modulesUuAccountTariffEditMainTrunk
);