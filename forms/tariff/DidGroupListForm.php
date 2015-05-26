<?php
namespace app\forms\tariff;

use app\models\DidGroup;
use yii\db\Query;

class DidGroupListForm extends DidGroupForm
{
    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return DidGroup::find()->joinWith('city');
    }

    public function applyFilter(Query $query)
    {
        if ($this->id) {
            $query->andWhere(['did_group.id' => $this->id]);
        }
        if ($this->name) {
            $query->andWhere("did_group.name like :name", [':name' => '%' . $this->name . '%']);
        }
        if ($this->city_id) {
            $query->andWhere(['did_group.city_id' => $this->city_id]);
        }
    }
}