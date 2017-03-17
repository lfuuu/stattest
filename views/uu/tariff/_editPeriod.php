<?php
/**
 * свойства тарифа (периоды)
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\uu\model\Period;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffPeriod;
use app\controllers\uu\TariffController;
use kartik\editable\Editable;
use unclead\widgets\TabularColumn;
use unclead\widgets\TabularInput;

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
    $showHistory = $this->render('//layouts/_showHistory', ['model' => $tariffPeriods, 'deleteModel' => [new TariffPeriod(), 'tariff_id', $tariff->id]]);
} else {
    $showHistory = '';
}

?>

<div class="well chargePeriod">
    <?php

    // для postpaid или пакетов - только помесячно
    $periodList = ($tariff->is_postpaid || in_array($tariff->service_type_id, ServiceType::$packages)) ?
        [Period::ID_MONTH => Period::findOne(['id' => Period::ID_MONTH])] :
        Period::getList();

    echo TabularInput::widget([
            'models' => array_values($tariffPeriods), // ключ должен быть автоинкрементный
            'allowEmptyList' => false,
            'columns' => [
                [
                    'name' => 'charge_period_id',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'charge_period_id'),
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => $periodList,
                        ],
                ],
                [
                    'name' => 'price_setup',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_setup'),
                    'options' => $options,
                ],
                [
                    'name' => 'price_per_period',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_per_period'),
                    'options' => $options,
                ],
                [
                    'name' => 'price_min',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_min'),
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