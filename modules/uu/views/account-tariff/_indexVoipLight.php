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


/** @var ActiveQuery $query */
$query = $filterModel->search()->query;
$query->andWhere(['NOT', ['service_type_id' => array_keys(ServiceType::$packages)]]);

// сгруппировать одинаковые город-тариф-пакеты по строчкам
$rows = AccountTariff::getGroupedObjectsLight($query);
foreach ($rows as $row) {
    /** @var AccountTariff $accountTariffFirst */
    $accountTariffFirst = reset($row);

    // форма
    echo $this->render('_indexVoipFormLight', [
        'accountTariffFirst' => $accountTariffFirst,
        'packageServiceTypeIds' => [],
        'row' => $row,
        'serviceType' => $accountTariffFirst->serviceType,
        'packagesList' => [],
    ]);

}