<?php
namespace app\classes;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use app\clesses\Form;

abstract class ListForm extends Model
{
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
                'pageSize' => Form::PAGE_SIZE,
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
