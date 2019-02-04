<?php


namespace app\classes\grid;


use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\db\QueryInterface;

class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    const cache_duration = 3600;
    /**
     * Вместо count(*) посчитать примерно через explain - так значительно быстрее на больших таблицах
     *
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     */
//    protected function prepareTotalCount()
//    {
//        if (!$this->db) {
//            throw new InvalidConfigException('Empty ActiveDataProvider db.');
//        }
//
//        if (!$this->query instanceof QueryInterface) {
//            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
//        }
//
//        /** @var Query $query */
//        $query = clone $this->query;
//        $query->limit(-1)->offset(-1)->orderBy([]);
//        $mainCommand = $query->createCommand($this->db);
//        $sql = $mainCommand->getSql();
//        $first_row = $this->db
//            ->createCommand("EXPLAIN $sql", $mainCommand->params)
//            ->queryScalar();
//        preg_match('/rows=(\d+)/', $first_row, $matches);
//        return $matches[1];
//    }

    protected function prepareTotalCount()
    {
        if (!$this->db) {
            throw new InvalidConfigException('Empty ActiveDataProvider db.');
        }

        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }

        /** @var Query $query */
        $query = clone $this->query;
        $query->limit(-1)->offset(-1)->orderBy([]);

        $mainCommand = $query->createCommand($this->db);
        $sql = $mainCommand->getSql();

        $key = $this->_getSqlKey($sql);

        if ($count = \Yii::$app->cache->get($key)) {
            return $count;
        }

        $count = $query->count('*');

        \Yii::$app->cache->set($key, $count, self::cache_duration);

        return $count;
    }

    private function _getSqlKey($sql)
    {
        return 'count_all_query_' . md5($sql);
    }
}