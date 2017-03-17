+function ($) {
    'use strict';

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

    $('.deleteFile')
        .on('click',function(e){
            e.preventDefault();

            var fid = $(this).data('id'),
                row = $(this).closest('tr');

            if (confirm('Вы уверены, что хотите удалить файл?')) {
                $.get('/file/delete-client-file', {id: fid}, function(data){
                    if(data['status'] == 'ok') {
                        row.remove();
                    }
                }, 'json');
            }
        });

}(jQuery);