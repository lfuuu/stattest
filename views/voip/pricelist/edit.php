<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Region;
use app\models\Currency;
use app\models\billing\NetworkConfig;

?>

<h2>
    <a href="/voip/pricelist/list?local=<?=$pricelist->local?>&orig=<?=$pricelist->orig?>&connectionPointId=<?=$model->connection_point_id?>">Прайслисты</a>
    -> Редактирование
</h2>
<?php
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

if ($pricelist->orig == false && $pricelist->local == false):
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
            'initiate_mgmn_cost' => ['type' => Form::INPUT_TEXT],
            'local' => ['type' => Form::INPUT_CHECKBOX, 'disabled' => true],
            'tariffication_full_first_minute' => ['type' => Form::INPUT_CHECKBOX],
            'initiate_zona_cost' => ['type' => Form::INPUT_TEXT],
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
            'orig' => ['type' => Form::INPUT_RAW, 'value' => 'Оригинация: ' . ($model->orig ? 'Да' : 'Нет')],
            'local' => ['type' => Form::INPUT_RAW, 'value' => 'Местные: ' . ($model->local ? 'Да' : 'Нет')],
        ],
    ]);
endif;
echo '<br/>';
echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        'local_network_config_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> ['' => '-- Выберите --'] + NetworkConfig::dao()->getList(), 'options' => ['class' => 'select2']],
        ['type' => Form::INPUT_RAW],
        ['type' => Form::INPUT_RAW],
    ],
]);

echo '<br/>';
echo Html::activeHiddenInput($model, 'id');
echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']);

$form->end();