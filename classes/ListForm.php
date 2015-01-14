<?php
namespace app\classes;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;

abstract class ListForm extends Model
{
    const PAGE_SIZE = 20;

    /**
     * @return ActiveDataProvider
     */
    public function spawnDataProvider()
    {
        $query = $this->spawnQuery();

        if ($this->validate()) {
            $this->applyFilter($query);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
            ],
        ]);
    }

    /**
     * @return ActiveQuery
     */
    public function spawnFilteredQuery()
    {
        $query = $this->spawnQuery();
        $this->applyFilter($query);
        return $query;
    }

    public function applyFilter(Query $query)
    {

    }

    /**
     * @return ActiveQuery
     */
    abstract public function spawnQuery();
}
