<?php
use app\classes\Html;
use yii\helpers\Url;

$files = $account->contract->allFiles;
?>

<div class="col-sm-12 fullTable collapse">
    <div class="row head3">
        <div class="col-sm-4">Имя файла</div>
        <div class="col-sm-4">Комментарий</div>
        <div class="col-sm-2">Кто</div>
        <div class="col-sm-2">Когда</div>
    </div>
    <?php foreach ($files as $file): ?>
        <div class="row">
            <div class="col-sm-4">
                <?= Html::a($file->name, ['/file/download', 'id' => $file->id], ['target' => '_blank']) ?>
                <a href="#" data-id="<?= $file->id ?>" class="fileSend">
                    <img border="0" src="images/icons/envelope.gif" />
                </a>
            </div>
            <div class="col-sm-4">
                <?= $file->comment ?>
            </div>
            <div class="col-sm-2">
                <?= ($file->user ? $file->user->name : '')  ?>
            </div>
            <div class="col-sm-2">
                <?= $file->ts ?>
                <a href="#" class="deleteFile" data-id="<?= $file->id ?>">
                    <img style="margin: -3px 0 0 -2px;" class="icon" src="/images/icons/delete.gif" alt="Удалить" />
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="row">
        <form action="<?= Url::toRoute(['/file/upload', 'contractId' => $account->contract->id, 'childId' => $account->id]) ?>" method=post enctype="multipart/form-data">
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
                <button type="submit" class="btn btn-primary col-sm-12">Загрузить</button>
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
                    <?php foreach ($account->allContacts as $contact) :
                        if ($contact->type == 'email') : ?>
                            <option value="<?= $contact->data ?>"><?= $contact->data ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
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