<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
?>

<div class="well">
    <legend>DID группа -> <?=Html::encode($model->name)?></legend>
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'name' => ['type' => Form::INPUT_TEXT, 'options' => ['disabled' => 'disabled']],
            'city_id' => ['type' => Form::INPUT_TEXT, 'options' => ['disabled' => 'disabled']],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-offset-2 col-md-10">' .
                    Html::a('Назад', ['list'], ['class'=>'btn btn-default btn-sm']) .
                    '</div>'
            ],
        ],
    ]);
    ActiveForm::end();
    ?>
</div>
