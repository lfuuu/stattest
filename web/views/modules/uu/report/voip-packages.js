+function ($, param) {
    'use strict';

    $(function () {
        $('select[name="filter[number]"]')
            .on('change', function() {
                var
                    current = $(this).find('option:selected').val(),
                    packages = $('select[name="filter[packages]"]'),
                    mode = $('select[name="filter[mode]"]').val(),
                    $buildBtn = $('button.build-report');

                packages.find('option:gt(0)').remove();

                if (current) {
                    if (param.packageList[current]) {
                        $.each(param.packageList[current], function () {
                            $('<option />')
                                .text(this.packageTitle)
                                .val(this.packageId)
                                .prop('selected', this.packageId == param.packageSelected)
                                .appendTo(packages);
                        });
                    }

                    if (packages.find('option').length > 1) {
                        $buildBtn
                            .prop('disabled', false)
                            .prev('div')
                            .hide();
                    }
                    else {
                        $buildBtn
                            .prop('disabled', true)
                            .prev('div')
                            .show();
                    }
                }
            })
            .trigger('change');

        $('select[name="filter[mode]"]')
            .on('change', function() {
                var current = $(this).find('option:selected').val(),
                    packages = $('select[name="filter[packages]"]');

                packages.find('option:eq(0)').prop('disabled', (current == 'by_package_calls' ? true : false));

                if (!param.packageSelected) {
                    packages.find('option:gt(0)').prop('selected', true);
                }
            })
            .trigger('change');
    })

}(
    jQuery,
    window.frontendVariables.modulesUuReportVoipPackagesUseReport
);