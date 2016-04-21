<?php
/**
 * свойства тарифа для телефонии. Подраздел Точки подключения
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffVoipCity;
use app\classes\uu\model\TariffVoipGroup;
use app\models\City;
use kartik\select2\Select2;

$tariffVoipCities = $formModel->tariffVoipCities;
$cityList = City::dao()->getList(false, $formModel->id ? $formModel->tariff->country_id : null);
$tariff = $formModel->tariff;

$tariffVoipCityTableName = TariffVoipCity::tableName();
$tariffTableName = Tariff::tableName();
?>

<div class="row">

    <div class="col-sm-4">
        <label><?= Yii::t('models/' . $tariffVoipCityTableName, 'city_id') ?></label>
        <?= Select2::widget([
            'name' => 'TariffVoipCity[]',
            'value' => array_keys($tariffVoipCities),
            'data' => $cityList,
            'options' => [
                'multiple' => true,
            ],
        ]) ?>
    </div>

</div>
