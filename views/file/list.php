<table class=insblock cellpadding=5 border=0 width="80%">
    <thead>
    <tr>
        <th>Имя файла</th>
        <th>Комментарий</th>
        <th>Кто</th>
        <th>Когда</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($model->files as $file): ?>
        <tr>
            <td>
                <a href="/file/download?id=<?= $file->id ?>" target="_blank">
                    <?= $file->name ?>
                </a>
                <a href="#" data-id="<?= $file->id ?>" class="fileSend">
                    <img border=0 src='images/icons/envelope.gif'>
                </a>
            </td>
            <td><?= $file->comment ?></td>
            <td><?= $file->user->name ?></td>
            <td style='font-size:85%'><?= $file->ts ?></td>
            <td>
                <a href='#' class="deleteFile" data-id="<?= $file->id ?>">
                    <img style='margin: -3px 0 0 -2px;' class=icon src='/images/icons/delete.gif' alt="Удалить">
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <form action="/file/upload?userId=<?= $model->id ?>" method=post enctype="multipart/form-data">
        <tr>
            <td><input class="col-sm-12 form-control" type=text name="name" placeholder="Название файла"></td>
            <td><input class="col-sm-12 form-control" type=text name="comment" placeholder="Комментарий"></td>
            <td><div class="file_upload form-control">Выбрать<input type="file" name="file" /></div></td>
            <td colspan=2><button type="submit" class="btn btn-default col-sm-12">Загрузить</button></td>
        </tr>
    </form>
    </tfoot>
</table>

<div id="dialog-form" title="Отправить файл">
    <div class="col-sm-12">
        <div class="form-group">
            <form method="post" id="send-file-form" target="_blank"
                action="http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5">
                <label for="client-email">Email</label>
                <select id="client-email" class="form-control" name="to">
                    <?php foreach ($model->allContacts as $contact)
                        if ($contact->is_active && $contact->type == 'email'):?>
                            <option value="<?= $contact->data ?>"><?= $contact->data ?></option>
                        <?php endif; ?>
                </select>
                <input type="hidden" name="file_content" id="file_content">
                <input type="hidden" name="file_name" id="file_name">
                <input type="hidden" name="file_mime" id="file_mime">
                <input type="hidden" name="msg_session" id="msg_session">
                <input type="hidden" name="send_from_stat" value="1">
            </form>
        </div>
    </div>
</div>


<script>
    var dialog;

    $(function () {
        dialog = $("#dialog-form").dialog({
            autoOpen: false,
            height: 200,
            width: 400,
            modal: true,
            buttons: {
                "Отправить": function () {
                    $('#send-file-form').submit();
                    dialog.dialog("close");
                },
                "Отмена": function () {
                    dialog.dialog("close");
                }
            }
        });
    });

    $('.fileSend').on('click', function (e) {
        e.preventDefault();
        $.get('/file/send', {id: $(this).data('id')}, function (data) {
            $('#file_content').val(data['file_content']);
            $('#file_name').val(data['file_name']);
            $('#file_mime').val(data['file_mime']);
            $('#msg_session').val(data['msg_session']);
            dialog.dialog("open");
        }, 'json');
    });

    $('.deleteFile').on('click',function(e){
        e.preventDefault();
        var fid = $(this).data('id');
        var row = $(this).closest('tr');
        if(confirm('Вы уверены, что хотите удалить файл?')) {
            $.get('/file/delete', {id: fid}, function(data){
                if(data['status'] == 'ok')
                row.remove();
            }, 'json');
        }
    });
</script>

<style>
    .file_upload {
        position: relative;
        overflow: hidden;
        text-align: center;
        width: 100%;
    }

    .insblock td, .insblock th {
        padding: 2px 5px;
    }

    .file_upload input[type=file] {
        position: absolute;
        top: 0; right: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        cursor: pointer;
    }
</style>