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
    const DEFAULT_TIMELIFE = 2678400; //3600*24*30;

    const ALL = 'all';

    const TAG_USAGE = 'usages';
    const TAG_USAGE_VOIP = 'usage_voip';
    const TAG_UU_SERVICE_LIST = 'uu_service_list';
    const TAG_TROUBLE_COUNT = 'trouble_count';
    const TAG_CALLS_RAW = 'calls_raw';
    const TAG_GRID_FOLDER = 'grid_folder';
    const TAG_API = 'api';
    const TAG_UU_API = 'uu_api';

    const LIST_TAGS = [
        self::ALL => 'Всё',
        self::TAG_USAGE  => 'Список услуг',
        self::TAG_USAGE_VOIP  => 'Список услуг по телефонии',
        self::TAG_UU_SERVICE_LIST => 'Список УУ-услуг',
        self::TAG_TROUBLE_COUNT => 'Кол-во траблов в открытых без привязки',
        self::TAG_CALLS_RAW => 'CallsRaw',
        self::TAG_GRID_FOLDER => 'Кол-во в гриде',
        self::TAG_API => 'API',
        self::TAG_UU_API => 'УУ-API',
    ];

    public function getKey($name, $value, $value2 = null)
    {
        return $name . '-' . $value . ($value2 ? '-' . $value2 : '');
    }

    public function getLsUsagesDependency($client)
    {
        $sql = "SELECT sum(a + replace(a_date, '-', '') + b + replace(b_date, '-', '') + id + replace(actual_from, '-', '') +
           replace(actual_to, '-', '') + is_actual*1000 + no_of_lines+ if (status = 'working', 1000,9999)) as sum
FROM (
       SELECT
         (SELECT id_tarif
          FROM log_tarif
          WHERE service = 'usage_voip' AND id_service = u.id AND date_activation <= cast(NOW() AS DATE)
          ORDER BY date_activation DESC, id DESC
          LIMIT 1)                a,
         (SELECT date_activation
          FROM log_tarif
          WHERE service = 'usage_voip' AND id_service = u.id AND date_activation <= cast(NOW() AS DATE)
          ORDER BY date_activation DESC, id DESC
          LIMIT 1)                a_date,
         coalesce((SELECT id_tarif
                   FROM log_tarif
                   WHERE service = 'usage_voip' AND id_service = u.id AND date_activation > cast(NOW() AS DATE)
                   ORDER BY date_activation, id
                   LIMIT 1), 0)   b,
         coalesce((SELECT date_activation
                   FROM log_tarif
                   WHERE service = 'usage_voip' AND id_service = u.id AND date_activation > cast(NOW() AS DATE)
                   ORDER BY date_activation, id
                   LIMIT 1), '.') b_date,
         u.id,
         actual_from,
         actual_to,
         if (cast(now() as date) between actual_from and actual_to, id, 0) as is_actual,
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