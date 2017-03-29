<?php
/**
 * свойства тарифа для телефонии. Подраздел Точки подключения
 *
 * @var \app\classes\BaseView $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffVoipCity;
use app\models\City;
use kartik\select2\Select2;

$tariffVoipCities = $formModel->tariffVoipCities;
$cityList = City::getList($isWithEmpty = false, $formModel->id ? $formModel->tariff->country_id : $formModel->countryId);
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

<?php if (!$tariff->isNewRecord) : ?>
    <?= $this->render('//layouts/_showHistory', ['model' => $tariffVoipCities, 'deleteModel' => [new TariffVoipCity(), 'tariff_id', $tariff->id]]) ?>
<?php endif; ?>
