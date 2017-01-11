<?php

/** @var \app\forms\client\ContractEditForm $contract */

use yii\helpers\Url;
use app\widgets\TagsSelect2\TagsSelect2;
?>

<form
    action="<?= Url::toRoute(['/file/upload-client-file', 'model' => 'clients', 'contractId' => $contract->id]) ?>"
    method="post"
    enctype="multipart/form-data"
    >
    <div class="col-sm-2">
        <input class="form-control" type="text" name="name" placeholder="Название файла" />
    </div>
    <div class="col-sm-2">
        <input class="form-control" type="text" name="comment" placeholder="Комментарий" />
    </div>
    <div class="col-sm-2">
        <?= TagsSelect2::widget([
            'model' => (new \app\models\media\ClientFiles),
            'label' => null,
            'isApplyViaAjax' => false,
            'options' => [
                'name' => 'tags',
                'class' => 'form-control',
            ],
            'pluginOptions' => [
                'placeholder' => 'Метки',
            ],
        ]) ?>
    </div>
    <div class="col-sm-2">
        <div class="file_upload form-control">Выбрать<input type="file" name="file" /></div>
    </div>
    <div class="col-sm-2">
        <?= $this->render('//layouts/_submitButton', [
            'text' => 'Загрузить',
            'glyphicon' => 'glyphicon-upload',
            'params' => [
                'class' => 'btn btn-primary col-sm-12',
            ],
        ]) ?>
    </div>
</form>