<?php
/**
 * свойства тарифа для телефонии. Подраздел Точки подключения
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\models\City;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffVoipCity;
use kartik\select2\Select2;

$tariffVoipCities = $formModel->tariffVoipCities;
$cityList = City::getList($isWithEmpty = false, $formModel->tariff->country_id);
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
    <?= $this->render('//layouts/_showHistory', [
        'parentModel' => [new TariffVoipCity(), $tariff->id],
    ]) ?>
<?php endif ?>
