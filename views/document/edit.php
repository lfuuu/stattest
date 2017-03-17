<?php

/** @var ClientDocument $model */

use app\assets\TinymceAsset;
use app\models\ClientDocument;
use kartik\widgets\ActiveForm;

TinymceAsset::register(Yii::$app->view);
?>

<h2><?= ClientDocument::$types[$model->type] ?></h2>

<?php $f = ActiveForm::begin([]); ?>

<div class="row">
    <div class="col-sm-4">
        <div class="row">
            <div class="col-sm-12">
                <?= $f->field($model, 'comment')->textInput() ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <?php $model->content = $model->getFileContent() ?>
        <?= $f->field($model, 'content')->textarea(['style' => 'height: 600px;']) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-2">
        <button type="submit" class="btn btn-primary col-sm-12">Сохранить</button>
    </div>
</div>
<?php ActiveForm::end(); ?>