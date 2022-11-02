<?php
/**
 * свойства тарифа для телефонии. Подраздел Регион (точки подключения)
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

$tariffVoipCityTableName = TariffVoipCity::tableName();
$tariffTableName = Tariff::tableName();
$tariff = $formModel->tariff;

$tariffCountries = $tariff->tariffCountries;
$cityList = (count($tariffCountries) == 1) ?
    City::getList($isWithEmpty = false, reset($tariffCountries)->country_id) : // для одной страны - ее города
    []; // для многих стран - города нет смысла выбирать

if ($editableType <= \app\modules\uu\controllers\TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

?>

<div class="row">

    <div class="col-sm-12">
        <label>
            <?= Yii::t('models/' . $tariffVoipCityTableName, 'city_id') ?>
            <?= $this->render('//layouts/_helpConfluence', $tariff->serviceType->getHelpConfluence()) ?>
        </label>
        <?= Select2::widget([
            'name' => 'TariffVoipCity[]',
            'value' => array_keys($formModel->tariffVoipCities),
            'data' => $cityList,
            'options' => [
                'multiple' => true,
            ] + $options,
        ]) ?>
    </div>

</div>

<?php if (!$tariff->isNewRecord) : ?>
    <?= $this->render('//layouts/_showHistory', [
        'parentModel' => [new TariffVoipCity(), $tariff->id],
    ]) ?>
<?php endif ?>
