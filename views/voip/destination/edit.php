<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\voip\Prefixlist;

$prefixes = ArrayHelper::map(Prefixlist::find()->all(), 'id', 'name');
?>

<div class="well">
    <legend>Направления -> <?= ($model->name ? Html::encode($model->name) : 'Новое направление'); ?></legend>
    <?php

    $form = ActiveForm::begin([
        'id' => 'DestinationForm',
        'type' => ActiveForm::TYPE_VERTICAL,
        'enableClientValidation' => true,
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'name' => ['type' => Form::INPUT_TEXT],
        ]
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'prefixes' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $prefixes, 'options' => ['class' => 'select2', 'multiple' => 'multiple']],
        ]
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-offset-2 col-md-10" style="text-align: right;">' .
                    Html::a(
                        'Отмена',
                        ['index'],
                        [
                            'class' => 'btn btn-default btn-sm',
                            'style' => 'margin-right: 15px;',
                        ]
                    ) .
                    Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) .
                    '</div>'
            ],
        ],
    ]);

    echo Html::activeHiddenInput($model, 'scenario', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>