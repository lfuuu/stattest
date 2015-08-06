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
    <?php foreach ($model->allFiles as $file): ?>
        <tr>
            <td>
                <a href="/file/get-file?model=clients&id=<?= $file->id ?>" target="_blank">
                    <?= $file->name ?>
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
    <form action="/file/upload-client-file?contractId=<?= $model->id ?>" method=post enctype="multipart/form-data">
        <tr>
            <td><input class="col-sm-12 form-control" type=text name="name" placeholder="Название файла"></td>
            <td><input class="col-sm-12 form-control" type=text name="comment" placeholder="Комментарий"></td>
            <td><div class="file_upload form-control">Выбрать<input type="file" name="file" /></div></td>
            <td colspan=2><button type="submit" class="btn btn-primary col-sm-12">Загрузить</button></td>
        </tr>
    </form>
    </tfoot>
</table>



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


    $('.deleteFile').on('click',function(e){
        e.preventDefault();
        var fid = $(this).data('id');
        var row = $(this).closest('tr');
        if(confirm('Вы уверены, что хотите удалить файл?')) {
            $.get('/file/delete-client-file', {id: fid}, function(data){
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
