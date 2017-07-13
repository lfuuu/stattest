<?php
/**
 * Смысл класса в предоставлении дополнительного функционала
 * оборачивания запроса в CTE (WITH ... AS (...)).
 * В итоге можно получить стрктуру из нескольких запросов,
 * которые при отсутствии зависимостей друг между другом
 * могут выполняться параллельно
 */

namespace app\classes\yii;

use app\classes\traits\GetListTrait;
use yii\db\Command;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use Yii;
use app\classes\McnQueryBuilder;

/**
 * @property array $with
 *
 * Class CTEQuery
 */
class CTEQuery extends Query
{
    public $with = [];

    /**
     * Добавить CTE-запрос
     *
     * @param string|Query $query
     * @param array $params
     * @return $this
     */
    public function addWith($query, $params = [])
    {
        $this->with = array_merge($this->with, $query);
        return $this->addParams($params);
    }

    /**
     * Creates a DB command that can be used to execute this query.
     *
     * @param Connection $db the database connection used to generate the SQL statement.
     * If this parameter is not given, the `db` application component will be used.
     * @return Command the created DB command instance.
     */
    public function createCommand($db = null)
    {
        if ($db === null) {
            $db = Yii::$app->getDb();
        }
        
        $builder = new McnQueryBuilder($db);
        list ($sql, $params) = $builder->build($this);

        return $db->createCommand($sql, $params);
    }

    /**
     * Посчитать количество строк, которое может возвратить запрос
     *
     * @param Connection $db
     * @return int mixed
     */
    public function rowCount($db = null)
    {
        if ($db == null) {
            $db = Yii::$app->dbPgSlave;
        }

        $main = clone $this;

        foreach ($main->with as $query) {
            if ($query instanceof Query) {
                $query->limit(-1)->offset(-1)->orderBy([]);
            }
        }

        foreach ($main->union as $query) {
            if ($query instanceof Query) {
                $query->limit(-1)->offset(-1)->orderBy([]);
            }
        }

        $main->limit(-1)->offset(-1)->orderBy([]);
        $main_command = $main->createCommand($db);
        $sql = $main->createCommand($db)->getSql();
        return $db->createCommand('SELECT COUNT(*) FROM (' . $sql . ') c', $main_command->params)->queryScalar();
    }

    /**
     * Метод определение количества строк возвращаемых запросом на основе статистики
     *
     * @param null $db
     * @return mixed
     */
    public function liteRowCount($db = null)
    {
        if ($db == null) {
            $db = Yii::$app->dbPgSlave;
        }

        $main = clone $this;

        foreach ($main->with as $query) {
            if ($query instanceof Query) {
                $query->limit(-1)->offset(-1)->orderBy([]);
            }
        }

        foreach ($main->union as $query) {
            if ($query instanceof Query) {
                $query->limit(-1)->offset(-1)->orderBy([]);
            }
        }

        $main->limit(-1)->offset(-1)->orderBy([]);
        $mainCommand = $main->createCommand($db);
        $sql = $mainCommand->getSql();
        $first_row = $db->createCommand("EXPLAIN $sql", $mainCommand->params)->queryScalar();
        preg_match('/rows=(\d+)/', $first_row, $matches);
        return $matches[1];
    }

    /**
     * Добавление условия
     *
     * @param $param
     * @param $property
     * @return $this
     */
    public function reportCondition($param, $property)
    {
        if ($property) {
            $condition = [$param => $property];
            if (is_array($property)) {
                $nullOrNotNullCondition = [];

                if(($key = array_search(GetListTrait::$isNull, $property)) !== false) {
                    unset($property[$key]);
                    $nullOrNotNullCondition = [$param => null];
                } elseif (($key = array_search(GetListTrait::$isNotNull, $property)) !== false) {
                    unset($property[$key]);
                    $nullOrNotNullCondition = ['NOT', [$param => null]];
                }

                if ($nullOrNotNullCondition) {
                    if ($property) {
                        $condition = ['OR', [$param => $property], $nullOrNotNullCondition];
                    } else {
                        $condition = $nullOrNotNullCondition;
                    }
                }
            } else {
                if ($property == GetListTrait::$isNull) {
                    $condition = [$param => null];
                }

                if ($property == GetListTrait::$isNotNull) {
                    $condition = ['NOT', [$param => null]];
                }
            }

            $this->andWhere($condition);
        }

        return $this;
    }
}
