<?php
/**
 * Список универсальных услуг с пакетами. Форма. Пакеты
 *
 * @var \yii\web\View $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var int[] $packageServiceTypeIds
 * @var AccountTariff[][] $row
 * @var ServiceType $serviceType
 * @var AccountTariff[][] $packagesList
 * @var bool $isShowAddPackage
 */

use app\classes\Html;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;

$i = 0;
foreach ($packageServiceTypeIds as $packageServiceTypeId) {

    if ($i++) {
        echo Html::tag('h3');
    }

    echo Html::tag('b', ServiceType::findOne(['id' => $packageServiceTypeId])->name);

    if (isset($packagesList[$packageServiceTypeId])) {
        foreach ($packagesList[$packageServiceTypeId] as $accountTariffPackage) {
            // пакеты
            echo $this->render('_indexVoipFormTariffs', [
                'accountTariffFirst' => $accountTariffPackage,
                'packageServiceTypeIds' => $packageServiceTypeIds,
                'row' => $row,
                'serviceType' => $serviceType,
                'packagesList' => [],
                'isShowAddPackage' => $isShowAddPackage,
            ]);
        }
    }

    if ($isShowAddPackage) {
        echo Html::button(
            Html::tag('i', '', [
                'class' => 'glyphicon glyphicon-plus',
                'aria-hidden' => 'true',
            ]) .
            ' Добавить пакет',
            [
                'class' => 'btn btn-success account-tariff-voip-button account-tariff-voip-button-edit btn-xs',
                'title' => 'Добавить пакет',
                'data-id' => 0,
                'data-city_id' => (int)$accountTariffFirst->city_id,
                'data-service_type_id' => (int)$packageServiceTypeId,
            ]
        );
    }
}
