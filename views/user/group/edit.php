<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

/** @var UsersGroup $model */
?>

<legend>
    <span>Редактирование группы - <?= $model->usergroup; ?></span>
</legend>

<div class="breadcrumb">
    <?= Html::a('Группы', '/user/group'); ?> -> <?= $model->usergroup; ?>
</div>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'usergroup' => ['type' => Form::INPUT_TEXT],
            'comment' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo $this->render('rights', ['model' => $model]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::hiddenInput($model->formName() . '[id]', $model->usergroup)],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-12" style="text-align: right; padding-right: 0px;">' .
                        Html::submitButton('Изменить', ['class' => 'btn btn-primary']) .
                    '</div>'
            ],
        ],
    ]);

    ActiveForm::end();
    ?>

</div>