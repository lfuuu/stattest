<?php

namespace app\modules\sim\classes\query;

use app\models\Region;
use yii\db\ActiveQuery;

class CardStatusQuery extends ActiveQuery
{
    public function regionId(int $regionId)
    {
        /** @var Region $region */
        $region = Region::find()->where(['id' => $regionId])->one();
        if (!$region || !$region->name) {
            throw new \InvalidArgumentException(printf('Not found region with id: %s', $regionId));
        }

        $this->andWhere(['LIKE', 'name', $region->name . '%', false]);

        return $this;
    }

    public function isVirt()
    {
        $this->andWhere(['LIKE', 'name', '%(вирт%', false]);

        return $this;
    }
}