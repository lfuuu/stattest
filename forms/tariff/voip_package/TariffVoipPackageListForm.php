<?php
namespace app\forms\tariff\voip_package;

use app\models\TariffVoipPackage;
use yii\db\Query;

class TariffVoipPackageListForm extends TariffVoipPackageForm
{

    public function rules()
    {
        return [
            [['country_id','connection_point_id','destination_id','currency_id'], 'integer'],
            [['currency_id','name'], 'string']
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return TariffVoipPackage::find()->orderBy('name asc');
    }

    public function applyFilter(Query $query)
    {
        if ($this->country_id) {
            $query->andWhere(['tarifs_voip_package.country_id' => $this->country_id]);
        }

        if ($this->connection_point_id) {
            $query->andWhere(['tarifs_voip_package.connection_point_id' => $this->connection_point_id]);
        }

        if ($this->currency_id) {
            $query->andWhere(['tarifs_voip_package.currency_id' => $this->currency_id]);
        }

        if ($this->destination_id) {
            $query->andWhere(['tarifs_voip.destination_id' => $this->dest]);
        }
    }

}