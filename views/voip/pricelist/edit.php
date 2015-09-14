<?php
use app\classes\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Region;
use app\models\Currency;
use app\models\billing\NetworkConfig;
use app\models\billing\Pricelist;

/** @var Pricelist $pricelist */

echo Html::formLabel('Редактирование прайс-листа');
echo Breadcrumbs::widget([
    'links' => [
        [
            'label' => 'Прайс-листы',
            'url' => Url::toRoute([
                'voip/pricelist/list',
                'type' => $model->type,
                'orig' => $model->orig,
                'connectionPointId' => $model->connection_point_id,
            ])
        ],
        'Редактирование прайс-листа'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    if ($pricelist->orig == false && $pricelist->isLocal()):
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'name' => ['type' => Form::INPUT_TEXT],
                'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true), 'options' => ['disabled' => true]],
                'currency_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Currency::map(), 'options' => ['disabled' => true]],
                'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => ['client' => 'Клиент', 'operator' => 'Оператор', 'network_prices' => 'Местные'], 'options' => ['disabled' => true]],
                'orig' => ['type' => Form::INPUT_RAW, 'value' => 'Оригинация: ' . ($model->orig ? 'Да' : 'Нет')],
                'price_include_vat' => ['type' => Form::INPUT_RAW, 'value' => 'Цена включает НДС: ' . ($model->price_include_vat ? 'Да' : 'Нет')],
                'local_network_config_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> ['' => '-- Выберите --'] + NetworkConfig::dao()->getList(), 'options' => ['class' => 'select2']],
            ],
        ]);
    else:
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'name' => ['type' => Form::INPUT_TEXT],
                'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true), 'options' => ['class' => 'select2', 'disabled' => true]],
                'currency_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Currency::map(), 'options' => ['class' => 'select2', 'disabled' => true]],
                'orig' => ['type' => Form::INPUT_CHECKBOX, 'disabled' => true],
                'tariffication_by_minutes' => ['type' => Form::INPUT_CHECKBOX],
                'price_include_vat' => ['type' => Form::INPUT_RAW, 'value' => 'Цена включает НДС: ' . ($model->price_include_vat ? 'Да' : 'Нет')],
                'initiate_mgmn_cost' => ['type' => Form::INPUT_TEXT],
                'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => ['client' => 'Клиент', 'operator' => 'Оператор', 'network_prices' => 'Местные'], 'options' => ['disabled' => true]],
                'tariffication_full_first_minute' => ['type' => Form::INPUT_CHECKBOX],
                'initiate_zona_cost' => ['type' => Form::INPUT_TEXT],
                'local_network_config_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> ['' => '-- Выберите --'] + NetworkConfig::dao()->getList(), 'options' => ['class' => 'select2']],
            ],
        ]);
    endif;

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
                            'onClick' => 'self.location = "' . Url::toRoute([
                                    'voip/pricelist/list',
                                    'type' => $model->type,
                                    'orig' => $model->orig,
                                    'connectionPointId' => $model->connection_point_id,
                                ]) . '";',
                        ]) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    $form->end();
?>
</div>