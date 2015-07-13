<?php
namespace app\forms\tariff;

use app\models\TariffNumber;
use yii\db\Query;

class TariffNumberListForm extends TariffNumberForm
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['country_id','city_id'], 'integer'],
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return TariffNumber::find()->joinWith(['country','city']);
    }

    public function applyFilter(Query $query)
    {
        if ($this->id) {
            $query->andWhere(['tarifs_number.id' => $this->id]);
        }
        if ($this->name) {
            $query->andWhere("tarifs_number.name like :name", [':name' => '%' . $this->name . '%']);
        }
        if ($this->country_id) {
            $query->andWhere(['tarifs_number.country_id' => $this->country_id]);
        }
        if ($this->city_id) {
            $query->andWhere(['tarifs_number.city_id' => $this->city_id]);
        }
    }
}