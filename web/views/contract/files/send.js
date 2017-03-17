+function ($) {
    'use strict';

    $(function () {
        var dialog = $('#dialog-form').dialog({
            autoOpen: false,
            height: 200,
            width: 400,
            modal: true,
            buttons: {
                "Отправить": function () {
                    $('#send-file-form').submit();
                    dialog.dialog('close');
                },
                "Отмена": function () {
                    dialog.dialog('close');
                }
            }
        });

        $('.fileSend').on('click', function (e) {
            e.preventDefault();
            $.getJSON('/file/send-client-file', {id: $(this).data('id')}, function (data) {
                $('#file_content').val(data['file_content']);
                $('#file_name').val(data['file_name']);
                $('#file_mime').val(data['file_mime']);
                $('#msg_session').val(data['msg_session']);
                dialog.dialog("open");
            });
        });
    })

}(jQuery);