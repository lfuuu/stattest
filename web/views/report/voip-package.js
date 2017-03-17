+function ($) {
    'use strict';

    $(function () {
        var $packages = $('select[name="filter[packages]"]');

        $('select[name="filter[number]"]')
            .on('change', function() {
                var
                    current = $(this).find('option:selected').val(),
                    mode = $('select[name="filter[mode]"]').val(),
                    $buildBtn = $('button.build-report');

                $packages.find('option:gt(0)').remove();

                if (current) {
                    if (frontendVariables.reportVoipPackageUseReport.packageList[current]) {
                        $.each(frontendVariables.reportVoipPackageUseReport.packageList[current], function () {
                            $('<option />')
                                .text(this.packageTitle)
                                .val(this.packageId)
                                .prop('selected', this.packageId == frontendVariables.reportVoipPackageUseReport.packageSelected)
                                .appendTo(packages);
                        });
                    }

                    if ($packages.find('option').length > 1) {
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
                var current = $(this).find('option:selected').val();

                $packages.find('option:eq(0)').prop('disabled', (current == 'by_package_calls' ? true : false));

                if (!frontendVariables.reportVoipPackageUseReport.packageSelected) {
                    $packages.find('option:gt(0)').prop('selected', true);
                }
            })
            .trigger('change');
    })

}(
    jQuery,
    window.frontendVariables.reportVoipPackageUseReport.packageList,
    window.frontendVariables.reportVoipPackageUseReport.packageSelected
);