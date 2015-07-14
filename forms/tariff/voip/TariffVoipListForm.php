<?php
namespace app\forms\tariff\voip;

use app\models\TariffVoip;
use yii\db\Query;
use yii\db\Expression;

class TariffVoipListForm extends TariffVoipForm
{

    public function rules()
    {
        return [
            [['country_id', 'connection_point_id', 'dest',], 'integer'],
            [['currency_id', 'status',], 'string']
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        $orderBy = new Expression('CASE dest >= 4 WHEN true THEN dest ELSE dest + 10 END, name');

        return TariffVoip::find()
            ->orderBy([$orderBy]);
    }

    public function applyFilter(Query $query)
    {
        if ($this->country_id) {
            $query->andWhere(['tarifs_voip.country_id' => $this->country_id]);
        }

        if ($this->connection_point_id) {
            $query->andWhere(['tarifs_voip.connection_point_id' => $this->connection_point_id]);
        }

        if ($this->currency_id) {
            $query->andWhere(['tarifs_voip.currency_id' => $this->currency_id]);
        }

        if ($this->status) {
            $query->andWhere(['tarifs_voip.status' => $this->status]);
        }
        else {
            $query->andWhere(['!=', 'tarifs_voip.status', 'archive']);
        }

        if ($this->dest) {
            $query->andWhere(['tarifs_voip.dest' => $this->dest]);
        }
    }

}