<?php
namespace app\forms\tariff\voip;

use app\models\TariffVoip;
use yii\db\Query;
use yii\db\Expression;

class TariffVoipListForm extends TariffVoipForm
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['country_id', 'connection_point_id', 'dest','ndc_type_id'], 'integer'],
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

    /**
     * @param Query $query
     */
    public function applyFilter(Query $query)
    {
        $this->country_id &&          $query->andWhere(['tarifs_voip.country_id' => $this->country_id]);
        $this->connection_point_id && $query->andWhere(['tarifs_voip.connection_point_id' => $this->connection_point_id]);
        $this->currency_id &&         $query->andWhere(['tarifs_voip.currency_id' => $this->currency_id]);
        $this->dest &&                $query->andWhere(['tarifs_voip.dest' => $this->dest]);
        $this->ndc_type_id != '' &&   $query->andWhere(['tarifs_voip.ndc_type_id' => $this->ndc_type_id]);

        if ($this->status) {
            $query->andWhere(['tarifs_voip.status' => $this->status]);
        } else {
            $query->andWhere(['!=', 'tarifs_voip.status', 'archive']);
        }
    }
}