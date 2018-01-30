+function ($) {
    'use strict';

    $(function () {
        $('#usagetrunkeditform-trunk_id').on('change', function () {
            $.getJSON('/usage/trunk/get-groups', {'trunkId': this.value}, function (data) {
                $('#orig_trunk_group').text(data.orig);
                $('#term_trunk_group').text(data.term);
            })
        })
            .trigger('change');
    })

}(
    jQuery
);