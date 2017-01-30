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
     * @inheritdoc
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
}
