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
$query->andWhere(['NOT', [AccountTariff::tableName() . '.service_type_id' => array_keys(ServiceType::$packages)]]);
$query->orderBy(new \yii\db\Expression('IF(tariff_period_id, 1, 0) DESC, uu_account_tariff.id DESC')); // Сначала действующие. Потом более свежие сверху

$key = 'uu_service_' . $filterModel->client_account_id . '-' . md5($query->createCommand()->rawSql);

$queryDep = clone $query;
$queryDep->select(['sum' => new \yii\db\Expression('count(*)+sum(uu_account_tariff.id) + sum(coalesce(tariff_period_id, -100))')]);
$sqlDep = $queryDep->createCommand()->rawSql;
$dbDep = new \yii\caching\DbDependency(['sql' => $sqlDep]);

echo \Yii::$app->cache->getOrSet($key,
    function () use ($query) {
        // сгруппировать одинаковые город-тариф-пакеты по строчкам

        $content = ';';
        $rows = AccountTariff::getGroupedObjectsLight($query);

        foreach ($rows as $hash => $row) {
            /** @var AccountTariff $accountTariffFirst */
            $accountTariffFirst = reset($row);

            $content .= $this->render('_indexVoipFormLight', [
                'accountTariffFirst' => $accountTariffFirst,
                'packageServiceTypeIds' => [],
                'row' => $row,
                'serviceType' => $accountTariffFirst->serviceType,
                'packagesList' => [],
            ]);

        }

        return $content;

    }, 3600 * 24 * 30, $dbDep
);