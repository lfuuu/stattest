<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\widgets\Breadcrumbs;
use app\classes\Html;

$currentStep = ($model->isNewRecord ? 'Создание' : 'Редактирование') . ' канала продаж';
echo Html::formLabel($currentStep);
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Каналы продаж', 'url' => ['sale-channel/']],
        $currentStep
    ],
]);
?>

<div class="well">

    <?php $f = ActiveForm::begin(); ?>
    <div class="row" style="width: 1100px;">
        <?php

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 3,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'name' => [],
                'dealer_id' => [],
                'is_agent' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => [0 => 'Нет', 1 => 'Да']],
                'interest' => [],
                'courier_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => \app\models\Courier::dao()->getList(false, true)],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'attributes' => [
                'actions' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        Html::tag(
                            'div',
                            Html::button('Отменить', [
                                'class' => 'btn btn-link',
                                'style' => 'margin-right: 15px;',
                                'onClick' => 'self.location = "/sale-channel";',
                            ]) .
                            Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']),
                            ['style' => 'text-align: right; padding-right: 0px;']
                        )
                ],
            ],
        ]);
        ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
