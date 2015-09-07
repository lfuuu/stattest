<?php
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\classes\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\DidGroup;

echo Html::formLabel('DID группа');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'DID группы', 'url' => Url::toRoute(['list'])],
        Html::encode($model->name)
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'name' => ['type' => Form::INPUT_TEXT, 'options' => ['disabled' => 'disabled']],
            'city_id' => ['type' => Form::INPUT_TEXT, 'options' => ['disabled' => 'disabled']],
            'beauty_level' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => DidGroup::$beautyLevelNames, 'options' => ['disabled' => 'disabled']],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Вернуться', [
                            'class' => 'btn btn-default',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['list']) . '";',
                        ]),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);
    ActiveForm::end();
    ?>
</div>
