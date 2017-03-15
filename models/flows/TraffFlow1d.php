<?php
namespace app\models\flows;

use app\classes\DateFunction;
use app\helpers\DateTimeZoneHelper;
use app\models\usages\UsageInterface;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class TraffFlow1d
 *
 * @property  string datetime
 * @property  string router_ip
 * @property  string ip_addr
 * @property  integer in_bytes
 * @property  integer out_bytes
 * @property  integer type
 */
class TraffFlow1d extends ActiveRecord
{
    const STAT_GROUP_YEAR = 'year';
    const STAT_GROUP_MONTH = 'month';
    const STAT_GROUP_DAY = 'day';
    const STAT_GROUP_HOUR = 'hour';
    const STAT_GROUP_IP = 'ip';
    const STAT_TOTAL_ONLY = 'total_only';

    public $names = [
        self::STAT_GROUP_IP => 'по IP-адресу',
        self::STAT_GROUP_YEAR => 'по годам',
        self::STAT_GROUP_MONTH => 'по месяцам',
        self::STAT_GROUP_DAY => 'по дням',
        self::STAT_GROUP_HOUR => 'по часам',
    ];

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'flows.traf_flow_1d';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return \Yii::$app->dbPgNfDump;
    }

    /**
     * Получение статистики по Интернет
     *
     * @param \DateTime $fromDt
     * @param \DateTime $toDt
     * @param string $grouping
     * @param array $routes
     * @param bool $isWithTotal
     * @return array
     */
    public static function getStatistic(\DateTime $fromDt, \DateTime $toDt, $grouping, $routes, $isWithTotal = true)
    {
        $tz = new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC);

        $middleDt = new \DateTime(UsageInterface::MIDDLE_DATE, $tz);

        // выход за разумные пределы
        if ($fromDt > $middleDt) {
            return null;
        }

        $isDayTable = true;
        $format = false;

        $query = new Query();

        $query->select([
            'ts' => 'extract(epoch from datetime) '
        ]);

        $query->orderBy([
            'datetime' => SORT_ASC
        ]);

        if ($grouping == self::STAT_GROUP_YEAR || $grouping == self::STAT_TOTAL_ONLY) {
            $query->groupBy('extract(YEAR from datetime)');
            $query->select([
                'datetime' => 'extract(YEAR from datetime)'
            ]);
            $format = 'Y г.';
        } elseif ($grouping == self::STAT_GROUP_MONTH) {
            $query->groupBy('extract(YEAR from datetime), extract(MONTH from datetime)');
            $query->select([
                'ts' => 'extract(epoch from min(datetime))'
            ]);
            $query->orderBy([
                'ts' => SORT_ASC
            ]);

            $format = 'Месяц Y г.';
        } elseif ($grouping == self::STAT_GROUP_DAY) {
            $query->groupBy('datetime');
            $format = 'd месяца Y г.';
        } elseif ($grouping == self::STAT_GROUP_HOUR) {
            $isDayTable = false;
            $query->groupBy('datetime');
            $format = 'd месяца Y г. H:i';
        } elseif ($grouping == self::STAT_GROUP_IP) {
            $query->groupBy('ip_addr');
            $query->orderBy([
                'ip_addr' => SORT_ASC
            ]);
            $query->select([
                'ip' => 'ip_addr',
            ]);
        } else {
            return null;
        }

        $query->from($isDayTable ? TraffFlow1d::tableName() : TraffFlow1h::tableName());

        $routesFilterWhere = ['or'];

        $isValidedNetAdded = false;
        foreach ($routes as $k => $row) {
            if (
                $row['actual_from'] != '9999-00-00' &&
                $row['actual_from'] < '3000-01-01' &&
                $row['actual_to'] >= $fromDt->format(DateTimeZoneHelper::DATE_FORMAT)
            ) {
                $subQuery = ['and'];
                $subQuery[] = ['>=', 'datetime', $row['actual_from']];

                if ($row['actual_to'] != "9999-00-00" && $row['actual_to'] < "3000-01-01") {
                    $subQuery[] = ['<=', 'datetime', $row['actual_to'] . ' 23:59:59'];
                }

                $isValidedNetAdded = true;

                $subQuery[] = new Expression("inet '" . $row['net'] . "' >>= ip_addr");
                $routesFilterWhere[] = $subQuery;
            }
        }

        if ($routesFilterWhere) {
            $query->andWhere($routesFilterWhere);
        }

        $rows = [];
        $total = [
            'in_bytes' => 0,
            'out_bytes' => 0,
        ];

        if ($isValidedNetAdded) {
            $query->andWhere(['router_ip' => '85.94.32.5']);
            $query->andWhere(['>=', 'datetime', $fromDt->format(DATE_ATOM)]);
            if ($toDt < $middleDt) {
                $query->andWhere(['<', 'datetime', $toDt->format(DATE_ATOM)]);
            }

            $query->addSelect([
                'in_bytes' => (new Expression('sum(in_bytes)')),
                'out_bytes' => (new Expression('sum(out_bytes)')),
            ]);

            $query->limit(5000);

            $countRow = 0;
            $sql = $query->createCommand(self::getDb())->rawSql;
            foreach ($query->createCommand(self::getDb())->queryAll() as $row) {
                $row['tsf'] = $format ? DateFunction::mdate($row['ts'], $format) : $row['ts'];
                $row['is_total'] = 0;
                $rows[] = $row;

                $total['in_bytes'] += $row['in_bytes'];
                $total['out_bytes'] += $row['out_bytes'];

                $countRow++;
            }

            if ($countRow == 5000) {
                if (\Yii::$app instanceof \app\classes\WebApplication) {
                    \Yii::$app->session->addFlash('error', 'Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
                }
            }
        }

        if ($grouping == self::STAT_TOTAL_ONLY) {
            return $total;
        }

        if ($isWithTotal) {
            return [
                'rows' => $rows,
                'total' => $total,
            ];
        } else {
            $total['is_total'] = 1;
            $rows[] = $total;

            return $rows;
        }
    }

}
