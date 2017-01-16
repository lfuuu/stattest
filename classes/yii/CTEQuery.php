<?php
/**
 * Смысл класса в предоставлении дополнительного функционала
 * оборачивания запроса в CTE (WITH ... AS (...)).
 * В итоге можно получить стрктуру из нескольких запросов,
 * которые при отсутствии зависимостей друг между другом
 * могут выполняться параллельно
 */

namespace app\classes\yii;

use yii\db\Connection;
use yii\db\Exception;
use yii\db\Query;
use Yii;
use app\classes\McnQueryBuilder;

/**
 * @property array $linkQueries
 *
 * Class CTEQuery
 */
class CTEQuery extends Query
{
    public $linkQueries = [];

    /**
     * Добавить CTE-запросы к основному запросу
     *
     * @param array $query
     *
     * @return $this
     * @throws Exception
     */
    public function addLinkQueries($query)
    {
        if (!is_array($query)) {
            throw new Exception('CTE-запросы должны быть заданы массивом');
        }

        foreach ($query as $key => $value) {
            if ($value instanceof Query) {
                if (is_int($key)) {
                    throw new Exception('Задайте имя для CTE-запроса');
                } else {
                    $this->linkQueries[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Удалить CTE-запрос
     *
     * @param string $index
     *
     * @return $this
     */
    public function removeLinkQuery($index)
    {
        unset($this->linkQueries[$index]);
        return $this;
    }

    /**
     * Переопределенный метод,
     * делающий возможным выполнения сложных запросов с WITH
     *
     * @param Connection $db
     *
     * @return array
     */
    public function all($db = null)
    {
        if ($db == null) {
            $db = Yii::$app->dbPgSlave;
        }

        list($sql, $params) = $this->createSQL($db);
        $rows = $db->createCommand($sql, $params)->queryAll();
        return $this->populate($rows);
    }

    /**
     * Сформирования полный текст запроса из основного и связанных
     *
     * @param Connection $db
     *
     * @return array
     */
    protected function createSQL($db)
    {
        $sql = [];
        $params = [];
        if ($this->linkQueries) {
            foreach ($this->linkQueries as $name => $query) {
                if ($query instanceof Query) {
                    list($sql[], $query_params) = $this->createCommandWithCTE($query, $name, $db);
                    $params = $params + $query_params;
                }
            }

            $builder = new McnQueryBuilder($db);
            list($sql_last, $query_params) = $builder->build($this);
            $sql = 'WITH ' . implode(',', $sql) . ' ' . $sql_last;
            $params = $params + $query_params;
        } else {
            $sql = $this->createCommand()->getSql();
            $params = $this->params;
        }

        return [$sql, $params];
    }

    /**
     * Сформировать из связанного запроса строку в виде $cte_name AS ($sql)
     *
     * @param Query $query
     * @param string $cte_name
     * @param Connection $db
     *
     * @return array
     */
    protected function createCommandWithCTE($query, $cte_name, $db)
    {
        $builder = new McnQueryBuilder($db);
        list($sql, $params) = $builder->build($query);

        $sql = "$cte_name as ($sql)";

        return [$sql, $params];
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

        foreach ($main->linkQueries as $query) {
            $query->limit(-1)->offset(-1)->orderBy([]);
        }

        foreach ($main->union as $query) {
            if ($query instanceof Query) {
                $query->limit(-1)->offset(-1)->orderBy([]);
            }
        }

        $main->limit(-1)->offset(-1)->orderBy([]);
        list($sql, $params) = $main->createSQL($db);
        return $db->createCommand('SELECT COUNT(*) FROM (' . $sql . ') c', $params)->queryScalar();
    }
}
