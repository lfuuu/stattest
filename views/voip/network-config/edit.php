<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Region;

?>

<h2>
    <a href="/voip/network-config/list">Местные префиксы</a>
    -> Редактирование
</h2>
<?php
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        'name' => ['type' => Form::INPUT_TEXT],
        'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true), 'options' => ['class' => 'select2', 'disabled' => true]],
    ],
]);

echo '<br/>';
echo Html::activeHiddenInput($model, 'id');
echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']);

$form->end();