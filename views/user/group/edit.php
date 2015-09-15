<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

/** @var UsersGroup $model */

echo Html::formLabel('Редактирование группы');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Группы', 'url' => Url::toRoute(['user/group'])],
        'Редактирование группы'
    ],
]);
?>

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

    ?>
    <br />
    <?php
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::hiddenInput($model->formName() . '[id]', $model->usergroup)],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['user/group']) . '";',
                        ]) .
                        Html::submitButton('Изменить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    ActiveForm::end();
    ?>

</div>