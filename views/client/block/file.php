<?php
use \yii\helpers\Url;

?>
<?php $files = $account->contract->allFiles; ?>

        <div class="col-sm-12 fullTable" style="display: none;">
            <div class="row head3">
                <div class="col-sm-4">Имя файла</div>
                <div class="col-sm-4">Комментарий</div>
                <div class="col-sm-2">Кто</div>
                <div class="col-sm-2">Когда</div>
            </div>
            <?php foreach ($files as $file): ?>
                <div class="row">
                    <div class="col-sm-4">
                        <a href="/file/download?id=<?= $file->id ?>" target="_blank">
                            <?= $file->name ?>
                        </a>
                        <a href="#" data-id="<?= $file->id ?>" class="fileSend">
                            <img border=0 src='images/icons/envelope.gif'>
                        </a>
                    </div>
                    <div class="col-sm-4">
                        <?= $file->comment ?>
                    </div>
                    <div class="col-sm-2">
                        <?= $file->user->name ?>
                    </div>
                    <div class="col-sm-2">
                        <?= $file->ts ?>
                        <a href='#' class="deleteFile" data-id="<?= $file->id ?>">
                            <img style='margin: -3px 0 0 -2px;' class=icon src='/images/icons/delete.gif'
                                 alt="Удалить">
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="row">
                <form action="/file/upload?contractId=<?= $account->contract->id ?>&childId=<?=$account->id?>" method=post enctype="multipart/form-data">
                    <div class="col-sm-4">
                        <input class="form-control" type=text name="name" placeholder="Название файла">
                    </div>
                    <div class="col-sm-4">
                        <input class="form-control" type=text name="comment" placeholder="Комментарий">
                    </div>
                    <div class="col-sm-2">
                        <div class="file_upload form-control">Выбрать<input type="file" name="file"/></div>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-default col-sm-12">Загрузить</button>
                    </div>
                </form>
            </div>
        </div>


<div id="dialog-form" title="Отправить файл">
    <div class="col-sm-12">
        <div class="form-group">
            <form method="post" id="send-file-form" target="_blank"
                  action="http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5">
                <label for="client-email">Email</label>
                <select id="client-email" class="form-control" name="to">
                    <?php foreach ($account->allContacts as $contact)
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
        $.getJSON('/file/send', {id: $(this).data('id')}, function (data) {
            $('#file_content').val(data['file_content']);
            $('#file_name').val(data['file_name']);
            $('#file_mime').val(data['file_mime']);
            $('#msg_session').val(data['msg_session']);
            dialog.dialog("open");
        });
    });

    $('.deleteFile').on('click', function (e) {
        e.preventDefault();
        var fid = $(this).data('id');
        var row = $(this).closest('.row');
        if (confirm('Вы уверены, что хотите удалить файл?')) {
            $.getJSON('/file/delete', {id: fid}, function (data) {
                console.log(data);
                console.log(data['status'] == 'ok');
                if (data['status'] == 'ok')
                    row.remove();
            });
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
        top: 0;
        right: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        cursor: pointer;
    }
</style>