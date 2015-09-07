<?php
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Region;
use app\models\billing\GeoCity;
use app\models\billing\GeoOperator;

echo Html::formLabel('Редактирование префикса');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Местные префиксы', 'url' => Url::toRoute(['voip/network-config/list'])],
        'Редактирование префикса'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'name' => ['type' => Form::INPUT_TEXT],
            'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true), 'options' => ['class' => 'select2', 'disabled' => true]],
            'geo_city_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> ['' => '-- Город --'] + GeoCity::dao()->getList(), 'options' => ['class' => 'select2']],
            'geo_operator_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> ['' => '-- Оператор --'] + GeoOperator::dao()->getList(), 'options' => ['class' => 'select2']],
        ],
    ]);

    echo '<br/>';
    echo Html::activeHiddenInput($model, 'id');
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['voip/network-config/list']) . '";',
                        ]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    $form->end();
    ?>
</div>