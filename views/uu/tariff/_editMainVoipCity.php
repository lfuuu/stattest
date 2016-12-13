<?php
/**
 * свойства тарифа для телефонии. Подраздел Точки подключения
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffVoipCity;
use app\controllers\uu\TariffController;
use app\models\City;
use kartik\select2\Select2;

$tariffVoipCities = $formModel->tariffVoipCities;
$cityList = City::dao()->getList(false, $formModel->id ? $formModel->tariff->country_id : $formModel->countryId);
$tariff = $formModel->tariff;

$tariffVoipCityTableName = TariffVoipCity::tableName();
$tariffTableName = Tariff::tableName();
?>

<div class="row">

    <div class="col-sm-12">
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

<script type="text/javascript">
jQuery(document).ready(function() {
    $('select[name="Tariff[country_id]"]').on('change', function() {
        var location = self.location.href.replace(/&?countryId=[0-9]+/, '');
        if (confirm('Страница будет перезагружена, для установки нового списка городов, уверены ?')) {
            self.location.href = location + '&countryId=' + $(this).val();
        }
    });
});
</script>
