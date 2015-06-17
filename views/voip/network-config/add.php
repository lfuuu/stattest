<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Region;
use app\models\billing\GeoCity;
use app\models\billing\GeoOperator;

?>

<h2>
    <a href="/voip/network-config/list">Местные префиксы</a>
    -> Добавление
</h2>
<?php
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 2,
    'attributes' => [
        'name' => ['type' => Form::INPUT_TEXT],
        'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true), 'options' => ['class' => 'select2']],
        'geo_city_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> GeoCity::dao()->getList(true), 'options' => ['class' => 'select2']],
        'geo_operator_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> GeoOperator::dao()->getList(true), 'options' => ['class' => 'select2']],
    ],
]);

echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']);

$form->end();