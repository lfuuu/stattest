<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\voip\Prefixlist;

$prefixes = ArrayHelper::map(Prefixlist::find()->all(), 'id', 'name');

echo Html::formLabel($model->name ? 'Редактирование направления' : 'Новое направление');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Направления', 'url' => Url::toRoute(['voip/destination'])],
        $model->name ? 'Редактирование направления' : 'Новое направление'
    ],
]);
?>

<div class="well">
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
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['voip/destination']) . '";',
                        ]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    echo Html::activeHiddenInput($model, 'scenario', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>