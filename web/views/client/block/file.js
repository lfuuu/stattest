+function ($) {
    'use strict';

    $(function () {
        var dialog = $('#dialog-form').dialog({
            autoOpen: false,
            height: 200,
            width: 400,
            modal: true,
            buttons: {
                'Отправить': function () {
                    $('#send-file-form').submit();
                    dialog.dialog('close');
                },
                'Отмена': function () {
                    dialog.dialog('close');
                }
            }
        });

        $('.fileSend')
            .on('click', function (e) {
                e.preventDefault();
                $.getJSON('/file/send', {id: $(this).data('id')}, function (data) {
                    $('#file_content').val(data['file_content']);
                    $('#file_name').val(data['file_name']);
                    $('#file_mime').val(data['file_mime']);
                    $('#msg_session').val(data['msg_session']);
                    dialog.dialog("open");
                });
            });

        $('.deleteFile')
            .on('click', function (e) {
            e.preventDefault();

                var fid = $(this).data('id'),
                    row = $(this).closest('.row');

                if (confirm('Вы уверены, что хотите удалить файл?')) {
                    $.getJSON('/file/delete', {id: fid}, function (data) {
                        if (data['status'] == 'ok') {
                            row.remove();
                        }
                    });
                }
            });
    })

}(jQuery);