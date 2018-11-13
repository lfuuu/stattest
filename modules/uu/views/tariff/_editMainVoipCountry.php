<?php
/**
 * свойства тарифа для телефонии. Подраздел Страны
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\models\Country;
use app\modules\uu\models\TariffVoipCountry;
use kartik\select2\Select2;

$tariff = $formModel->tariff;
$tariffCountries = $tariff->tariffCountries;
?>

<div class="row">

	<div class="col-sm-12">
		<label>
            <?= Yii::t('models/' . TariffVoipCountry::tableName(), 'country_id') ?>
            <?= $this->render('//layouts/_helpConfluence', $tariff->serviceType->getHelpConfluence()) ?>
		</label>
        <?= Select2::widget([
            'name' => 'TariffVoipCountry[]',
            'value' => array_keys($formModel->tariffVoipCountries),
            'data' => Country::getList($isWithEmpty = false),
            'options' => [
                'multiple' => true,
            ],
        ]) ?>
	</div>

</div>

<?php if (!$tariff->isNewRecord) : ?>
    <?= $this->render('//layouts/_showHistory', [
        'parentModel' => [new TariffVoipCountry(), $tariff->id],
    ]) ?>
<?php endif ?>
