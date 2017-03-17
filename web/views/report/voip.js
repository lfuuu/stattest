+function ($) {
    'use strict';

    function ajaxTrunkUpdate() {
        $.post(
            '/report/voip/cost-report',
            {
                'operation': 'update_trunks',
                'server_id': $('#server').val(),
                'operator_id': $('#operator').val()
            },
            function(r) {
                if (r.status == 'success') {
                    $('#trunk option').remove();

                    $.each(r.data, function(index, value) {
                        $('#trunk').append('<option value="' + value.id + '">' + value.text + '</option>');
                    });
                }
            },
            'json'
        );
    }

    $(function () {
        $('#server').change(function (event) {
            event.preventDefault();
            ajaxTrunkUpdate();
        });

        $('#operator').change(function (event) {
            event.preventDefault();
            ajaxTrunkUpdate();
        });
    })
}(jQuery);