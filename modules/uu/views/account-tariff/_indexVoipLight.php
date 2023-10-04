<?php
/**
 * Список универсальных услуг с пакетами
 *
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10715249
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\helpers\DependecyHelper;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;


/** @var ActiveQuery $query */
$query = $filterModel->search()->query;
$query->andWhere(['NOT', [AccountTariff::tableName() . '.service_type_id' => array_keys(ServiceType::$packages)]]);
$query->orderBy(new \yii\db\Expression('IF(tariff_period_id, 1, 0) DESC, uu_account_tariff.id DESC')); // Сначала действующие. Потом более свежие сверху

$key = 'uu_service_' . $filterModel->client_account_id . '-' . md5($query->createCommand()->rawSql);

$queryDep = clone $query;
$queryDep->select(['sum' => new \yii\db\Expression('SUM(COALESCE((SELECT sum(CONCAT(COALESCE(tariff_period_id, -1010)+ CAST(REPLACE(COALESCE(actual_from_utc, \'3020\'), \'-\', \'\') AS UNSIGNED)))
                   FROM uu_account_tariff_log
                   WHERE account_tariff_id = uu_account_tariff.id
                  ), 0)) + count(*) + sum(uu_account_tariff.id) + sum(coalesce(tariff_period_id, -uu_account_tariff.id))'),
                'voip_status' => new \yii\db\Expression("md5(group_concat(vn.status))")]);
$queryDep->leftJoin(['vn' => \app\models\Number::tableName()], 'vn.number = voip_number');


$sqlDep = $queryDep->createCommand()->rawSql;
$dbDep = new \yii\caching\DbDependency(['sql' => $sqlDep]);
$tagDep = (new TagDependency(['tags' => [DependecyHelper::TAG_UU_SERVICE_LIST, DependecyHelper::TAG_USAGE]]));

$chainDep = (new \yii\caching\ChainedDependency(['dependencies' => [$tagDep, $dbDep]]));

echo \Yii::$app->cache->getOrSet($key,
    function () use ($query) {
        // сгруппировать одинаковые город-тариф-пакеты по строчкам

        $content = '';
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
//        echo $content;

    }, 3600 * 24 * 30, $chainDep
);