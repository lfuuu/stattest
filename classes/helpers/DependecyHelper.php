<?php

namespace app\classes\helpers;


use app\classes\Singleton;
use yii\caching\ChainedDependency;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;

/**
 * Class DependecyHelper
 *
 * @method static DependecyHelper me($args = null)
 */
class DependecyHelper extends Singleton
{
    const TIMELIFE_MINUTE = 60;
    const TIMELIFE_3MINUTE = 180;
    const TIMELIFE_HOUR = 3600;
    const TIMELIFE_DAY = 86400; //3600*24;
    const TIMELIFE_MONTH = 2678400; //3600*24*30;
    const TIMELIFE_HALF_MONTH = 1339200; //3600*24*15;
    const TIMELIFE_NEXT_BILL_NO = 300; // 5 min

    const DEFAULT_TIMELIFE = self::TIMELIFE_MONTH;

    const ALL = 'all';

    const TAG_USAGE = 'usages';
    const TAG_USAGE_VOIP = 'usage_voip';
    const TAG_UU_SERVICE_LIST = 'uu_service_list';
    const TAG_TROUBLE_COUNT = 'trouble_count';
    const TAG_CALLS_RAW = 'calls_raw';
    const TAG_GRID_FOLDER = 'grid_folder';
    const TAG_BILL = 'bill';
    const TAG_PRICELIST = 'pricelist';
    const TAG_NUMBER_INFO = 'number_info';

    const LIST_TAGS = [
        self::TAG_USAGE  => 'Список услуг',
        self::TAG_USAGE_VOIP  => 'Список услуг по телефонии',
        self::TAG_UU_SERVICE_LIST => 'Список УУ-услуг',
        self::TAG_TROUBLE_COUNT => 'Кол-во траблов в открытых без привязки',
        self::TAG_CALLS_RAW => 'CallsRaw',
        self::TAG_GRID_FOLDER => 'Кол-во в гриде',
        self::TAG_BILL => 'Счета',
        self::TAG_PRICELIST => 'Прайс-лист (api тариф)',
        self::TAG_NUMBER_INFO => 'Информация по номерам',
    ];

    public function getKey($name, $value, $value2 = null)
    {
        return $name . '-' . $value . ($value2 ? '-' . $value2 : '');
    }

    public function getLsUsagesDependency($client)
    {
        $sql = "SELECT sum(a + b + id +
           CAST(REPLACE(COALESCE(actual_from, '2020'), '-', '') AS UNSIGNED) +
           CAST(REPLACE(COALESCE(actual_to, '3030'), '-', '') AS UNSIGNED) +
           is_actual * 1000 + no_of_lines +
           IF(status = 'working', 1000, 9999) + current_tariff_id) AS sum
FROM (
       SELECT
         #логи тарифов
         COALESCE((SELECT sum(COALESCE(id, -100) + COALESCE(id_tarif, -200) + COALESCE(id_tarif_local_mob, -300) +
                              COALESCE(id_tarif_russia, -400) + COALESCE(id_tarif_intern, -500) +
                              CAST(REPLACE(COALESCE(date_activation, '1000'), '-', '') AS UNSIGNED))
                   FROM log_tarif
                   WHERE id_service = u.id AND service = 'usage_voip'
                   GROUP BY id_service), 100)                               AS a,
         #пакеты
         COALESCE((SELECT sum(id + COALESCE(tariff_id, -200) + COALESCE(usage_trunk_id, -100) +
                              CAST(REPLACE(COALESCE(actual_from, '3020'), '-', '') AS UNSIGNED) +
                              CAST(REPLACE(COALESCE(actual_to, '3010'), '-', '') AS UNSIGNED) +
                              IF(status = 'working', 1000, 9999) +
                              IF(CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to, id, 0) * 555)
                   FROM `usage_voip_package`
                   WHERE usage_voip_id = u.id AND client = u.client), -111) AS b,
         #текущий тариф
         COALESCE((SELECT id_tarif
                   FROM log_tarif
                   WHERE service = 'usage_voip' AND id_service = u.id AND date_activation <= CAST(NOW() AS DATE)
                   ORDER BY date_activation DESC, id DESC
                   LIMIT 1), -300)                                             current_tariff_id,

         u.id,
         actual_from,
         actual_to,
         IF(CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to, id, 0)   AS is_actual,
         u.no_of_lines,
         u.status
       FROM usage_voip u
       WHERE client = :client
     ) a";

        $tagsDep = new TagDependency(['tags' => [self::TAG_USAGE_VOIP, self::TAG_USAGE]]);
        $dbDep = new DbDependency(['sql' => $sql, 'params' => [':client' => $client]]);

        $chainedDep = new ChainedDependency(['dependencies' => [$dbDep, $tagsDep]]);

        return $chainedDep;
    }

    public function getUuListDependency(ActiveQuery $query)
    {
        $queryDep = clone $query;
        $queryDep->select(['sum' => new \yii\db\Expression('count(*)+sum(uu_account_tariff.id) + sum(coalesce(tariff_period_id, -100))')]);
        $sqlDep = $queryDep->createCommand()->rawSql;
        $dbDep = new DbDependency(['sql' => $sqlDep]);
        $tagsDep = new TagDependency(['tags' => [self::TAG_USAGE, self::TAG_UU_SERVICE_LIST]]);

        return $chainedDep = new ChainedDependency(['dependencies' => [$dbDep, $tagsDep]]);
    }
}