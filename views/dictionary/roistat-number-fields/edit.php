<?php

use app\models\RoistatNumberFields;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $model RoistatNumberFields */

echo Html::formLabel('Настройка номера');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Roistat. Настройки параметров', 'url' => Url::toRoute(['/dictionary/roistat-number-fields/index'])],
        'Настройка номера',
    ],
]);

$number = $model->number ? $model->number : 'false';
$fields = $model->fields ? $model->fields : 'false';

$script = <<< JS
    $(document).ready(function() {
        fillInputs($number, $fields);
    });
JS;
$this->registerJs($script);
?>

<form action="" method="post" style="max-width: 40%">
    <div>
        <label for="number">Номер </label><br>
        <input class="form-control" type="number" name="number" id="number">
    </div>


    <div class="fields-container row" style="margin-top: 10px">
        <div class="col-xs-6">
            <label>Ключ</label> <br> <input class="form-control" type="text">
        </div>
        <div class="col-xs-6">
            <label>Значение</label> <br> <input class="form-control" type="text">
        </div>
    </div>



    <div class="additional_fields"> </div>
    <br>

    <input class="btn btn-primary" type="button" value="Добавить поля" onclick="addInputFields();">
    <input class="btn btn-success" type="submit" value="Сохранить" onclick="prepareInputNames();">
</form>
