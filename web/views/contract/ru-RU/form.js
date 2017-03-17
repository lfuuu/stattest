+function ($, businessTree, contractTypes) {
    'use strict';

    $(function () {
        var $businessList = $('#contracteditform-business_id'), // s1
            $businessProcessList = $('#contracteditform-business_process_id'), // s2
            $businessProcessStatusList = $('#contracteditform-business_process_status_id'), // s3
            $contractTypeList = $('#contracteditform-contract_type_id'); // s4

        var businessProcess = $businessProcessList.val(), // s2
            businessProcessStatus = $businessProcessStatusList.val(), // s3
            contractType = $contractTypeList.val(); // s4

        $businessProcessList.empty();
        $(businessTree.processes).each(function () {
            if ($businessList.val() == this.up_id) {
                $businessProcessList.append(
                    $('<option />')
                        .val(this.id)
                        .prop('selected', this.id == businessProcess)
                        .text(this.name)
                );
            }
        });

        $businessProcessStatusList.empty();
        $(businessTree.statuses).each(function () {
            if ($businessProcessList.val() == this.up_id) {
                $businessProcessStatusList.append(
                    $('<option />')
                        .val(this.id)
                        .prop('selected', this.id == businessProcessStatus)
                        .text(this.name)
                );
            }
        });

        if ($contractTypeList.length) {
            $contractTypeList.empty();
            $contractTypeList.append($('<option />').val('0').text('Не задано'));

            $(contractTypes).each(function () {
                if ($businessProcessList.val() == this.business_process_id) {
                    $contractTypeList.append(
                        $('<option />')
                            .val(this.id)
                            .prop('selected', this.id == contractType)
                            .text(this.name)
                    );
                }
            });
        }

        $businessList.on('change', function () {
            var $form = $(this).closest('form');
            $('<input type="hidden" name="notSave" value="1" />').appendTo($form);
            $form.submit();
        });

        $businessProcessList.on('change', function () {
            $businessProcessStatusList.empty();

            $(businessTree.statuses).each(function () {
                if ($businessProcessList.val() == this.up_id) {
                    $businessProcessStatusList.append(
                        $('<option />')
                            .val(this.id)
                            .prop('selected', this.id == businessProcessStatus)
                            .text(this.name)
                    );
                }
            });

            if ($contractTypeList.length) {
                $contractTypeList.empty();

                $(contractTypes).each(function () {
                    if ($businessProcessList.val() == this.business_process_id) {
                        $contractTypeList.append(
                            $('<option />')
                                .val(this.id)
                                .prop('selected', this.id == contractType)
                                .text(this.name)
                        );
                    }
                });
            }
        });

        $('.btn-disabled').on('click', function () {
            return false;
        });

        $('.period-type').on('change', function () {
            var month = $(this).parent().parent().next();

            $(this).val() == 'month' ? month.show() : month.hide();
        });
    })

}(
    jQuery,
    window.frontendVariables.contractRuRUForm.statuses,
    window.frontendVariables.contractRuRUForm.contractTypes
);