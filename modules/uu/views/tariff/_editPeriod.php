<?php
/**
 * свойства тарифа (периоды)
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\Period;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use kartik\editable\Editable;
use unclead\multipleinput\TabularColumn;
use app\widgets\TabularInput\TabularInput;

$tariff = $formModel->tariff;
$tariffPeriods = $formModel->tariffPeriods;
$tariffPeriodTableName = TariffPeriod::tableName();

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

if (!$tariff->isNewRecord) {
    // это нужно сделать ДО TabularInput, иначе он попортит данные $tariffPeriods
    $showHistory = $this->render('//layouts/_showHistory', [
        'parentModel' => [new TariffPeriod(), $tariff->id],
    ]);
} else {
    $showHistory = '';
}

?>

<div class="well chargePeriod">
    <h2>Тариф-период <?= $this->render('//layouts/_helpConfluence', Tariff::getHelpConfluence()) ?></h2>
    <?php

    // для postpaid или пакетов - только помесячно
    $periodList = (array_key_exists($tariff->service_type_id, ServiceType::$packages)) ?
        [Period::ID_MONTH => Period::findOne(['id' => Period::ID_MONTH])] :
        Period::getList();

    echo TabularInput::widget([
            'models' => array_values($tariffPeriods), // ключ должен быть автоинкрементный
            'allowEmptyList' => false,
            'columns' => [
                [
                    'name' => 'charge_period_id',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'charge_period_id') .
                        $this->render('//layouts/_helpConfluence', AccountLogPeriod::getHelpConfluence()),
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => $periodList,
                        ],
                ],
                [
                    'name' => 'price_setup',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_setup') .
                        $this->render('//layouts/_helpConfluence', AccountLogSetup::getHelpConfluence()),
                    'options' => $options,
                ],
                [
                    'name' => 'price_per_period',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_per_period') .
                        $this->render('//layouts/_helpConfluence', AccountLogPeriod::getHelpConfluence()),
                    'options' => $options,
                ],
                [
                    'name' => 'price_min',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_min') .
                        $this->render('//layouts/_helpConfluence', AccountLogMin::getHelpConfluence()),
                    'options' => $options,
                ],
                [
                    'name' => 'id', // чтобы идентифицировать модель
                    'type' => TabularColumn::TYPE_HIDDEN_INPUT,
                ],
            ],
        ]
    );
    ?>

    <?= $showHistory ?>

</div>