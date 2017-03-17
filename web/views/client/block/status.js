+function ($) {
    'use strict';

    $(function () {
        $('.status-block-toggle').on('click', function () {
            $('#statuses').toggle();
            $('#w1 .row').slice(0, 2).toggle();
            return false;
        });

        $(function () {
            document.cookie = 'openedBlock=;';
            if (frontendVariables.clientBlockStatus.openedBlock) {
                $('.status-block-toggle').click();
            }

            var $s1 = $('#contracteditform-business_id'),
                $s2 = $('#contracteditform-business_process_id'),
                $s3 = $('#contracteditform-business_process_status_id'),
                vals2 = $s2.val(),
                vals3 = $s3.val();

            $s2.empty();
            $(frontendVariables.clientBlockStatus.statuses.processes).each(function (k, v) {
                if ($s1.val() == v['up_id']) {
                    $s2.append('<option ' + (v['id'] == vals2 ? 'selected' : '') + ' value="' + v['id'] + '">' + v['name'] + '</option>');
                }
            });

            $s3.empty();
            $(frontendVariables.clientBlockStatus.statuses.statuses).each(function (k, v) {
                if ($s2.val() == v['up_id']) {
                    $s3.append('<option ' + (v['id'] == vals3 ? 'selected' : '') + ' value="' + v['id'] + '">' + v['name'] + '</option>');
                }
            });

            $s1.on('change', function () {
                $s2.empty();
                $(frontendVariables.clientBlockStatus.statuses.processes).each(function (k, v) {
                    if ($s1.val() == v['up_id']) {
                        $s2.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
                    }
                });

                $s3.empty();
                $(frontendVariables.clientBlockStatus.statuses.statuses).each(function (k, v) {
                    if ($s2.val() == v['up_id']) {
                        $s3.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
                    }
                });
            });

            $s2.on('change', function () {
                $s3.empty();
                $(frontendVariables.clientBlockStatus.statuses.statuses).each(function (k, v) {
                    if ($s2.val() == v['up_id']) {
                        $s3.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
                    }
                });
            });

            $('#buttonSave').closest('form').on('submit', function () {
                document.cookie = 'openedBlock=statuses';
                return true;
            });
        });
    })

}(
    jQuery,
    window.frontendVariables.clientBlockStatus.statuses,
    window.frontendVariables.clientBlockStatus.openedBlock
);