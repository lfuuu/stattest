+function ($) {
    'use strict';

    $(function () {
        $('#entrypoint-client_contract_business_id').on('change', function (event) {
            redrawProccess(event.target.value);
        });

        $('#entrypoint-client_contract_business_process_id').on('change', function (event) {
            redrawStatuses(event.target.value);
        });

        $('#entrypoint-country_id').on('change', function (event) {
            redrawRegions(event.target.value);
        });

        function redrawProccess(businessId) {
            var $businessProcessObj = $('#entrypoint-client_contract_business_process_id'),
                processId = 0;

            $businessProcessObj.empty();
            $.each(frontendVariables.dictionaryEntryPointEdit.statuses.processes, function (key, value) {
                if (businessId == value['up_id']) {
                    if (!processId) {
                        processId = value['id'];
                    }
                    $businessProcessObj.append($('<option>').val(value['id']).text(value['name']));
                }
            });

            redrawStatuses(processId);
        }

        function redrawStatuses(processId) {
            var $businessProcessStatusObj = $('#entrypoint-client_contract_business_process_status_id');

            $businessProcessStatusObj.empty();
            $.each(frontendVariables.dictionaryEntryPointEdit.statuses.statuses, function (key, value) {
                if (processId == value['up_id']) {
                    $businessProcessStatusObj.append($('<option>').val(value['id']).text(value['name']));
                }
            });
        }

        function redrawRegions(countryId) {
            var $regionObj = $("#entrypoint-region_id");

            $regionObj.empty();
            $.each(frontendVariables.dictionaryEntryPointEdit.regions, function (key, value) {
                if (countryId == value['country_id']) {
                    $regionObj.append($('<option>').val(value['id']).text(value['name']));
                }
            });
        }
    })

}(
    jQuery,
    window.frontendVariables.dictionaryEntryPointEdit.statuses,
    window.frontendVariables.dictionaryEntryPointEdit.regions
);