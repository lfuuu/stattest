+function ($) {
    'use strict';

    $(function () {

        function applyRegionSettings(regionSettingsId) {
            $.getJSON('/sim/registry/get-region-setting', {
                regionSettingsId: regionSettingsId
            }, function(data) {
                var json = $.parseJSON(data);
                if (json && json.iccid_prefix && json.imsi_prefix) {
                    $(".label_prefix_iccid").each(function() {
                        $(this).text(json.iccid_prefix);
                    });

                    $(".label_prefix_imsi").each(function() {
                        $(this).text(json.imsi_prefix);
                    });
                }

                if (json && json.iccid_last_used >= 0) {
                    $("#registry_add_iccid_from").each(function() {
                        $(this).val(json.iccid_last_used);
                    });
                    $("#registry_add_iccid_to").each(function() {
                        $(this).val('');
                    });
                }

                if (json && json.iccid_length && json.imsi_length) {
                    $(".label_iccid_length").each(function() {
                        $(this).text(json.iccid_length);
                    });

                    $(".label_imsi_length").each(function() {
                        $(this).text(json.imsi_length);
                    });
                }

                if (json && json.imsi_last_used >= 0) {
                    $("#registry_add_imsi_from").each(function() {
                        $(this).val(json.imsi_last_used);
                    });
                    $("#registry_add_imsi_to").each(function() {
                        $(this).val('');
                    });
                }
            });
        }

        $('.formReload').on('change', function () {
            applyRegionSettings($(this).val());
        });

        applyRegionSettings($('select.formReload')[0].value);
    })

}(
    jQuery,
    window.frontendVariables.modulesSimRegistryAdd.simFormId
);

