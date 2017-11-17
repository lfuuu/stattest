<?php
/**
 * Список универсальных услуг с пакетами
 *
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10715249
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariffFilter $filterModel
 */

use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\db\ActiveQuery;

$serviceType = $filterModel->getServiceType();

/** @var ActiveQuery $query */
$query = $filterModel->search()->query;
if (!$serviceType) {
    $query->andWhere(['NOT', ['service_type_id' => array_keys(ServiceType::$packages)]]);
}

// сгруппировать одинаковые город-тариф-пакеты по строчкам
$rows = AccountTariff::getGroupedObjects($query);
?>

    <p>
        <?= ($serviceType ? $this->render('//layouts/_buttonCreate', ['url' => AccountTariff::getUrlNew($serviceType->id)]) : '') ?>
    </p>

<?php
foreach ($rows as $row) {
    /** @var AccountTariff $accountTariffFirst */
    $accountTariffFirst = reset($row);
    if (array_key_exists($accountTariffFirst->service_type_id, ServiceType::$packages)) {
        // пакеты отдельно не выводим. Только в комплекте с базовой услугой
        continue;
    }

    $serviceType = $accountTariffFirst->serviceType;

    switch ($accountTariffFirst->service_type_id) {
        case ServiceType::ID_VOIP:
            $packageServiceTypeIds = [ServiceType::ID_VOIP_PACKAGE_CALLS, ServiceType::ID_VOIP_PACKAGE_INTERNET];
            break;
        case ServiceType::ID_TRUNK:
            $packageServiceTypeIds = [ServiceType::ID_TRUNK_PACKAGE_ORIG, ServiceType::ID_TRUNK_PACKAGE_TERM];
            break;
        default:
            $packageServiceTypeIds = [];
            break;
    }

    // сгруппировать пакеты по типу
    $packagesList = [];
    foreach ($accountTariffFirst->nextAccountTariffs as $accountTariffPackage) {
        $isPackageDefault = $accountTariffPackage->getNotNullTariffPeriod()->tariff->is_default;
        $packagesList[$isPackageDefault ? 0 : $accountTariffPackage->service_type_id][] = $accountTariffPackage;
    }

    // форма
    echo $this->render('_indexVoipForm', [
        'accountTariffFirst' => $accountTariffFirst,
        'packageServiceTypeIds' => $packageServiceTypeIds,
        'row' => $row,
        'serviceType' => $serviceType,
        'packagesList' => $packagesList,
    ]);

}