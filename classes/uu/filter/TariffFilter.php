<?php

namespace app\classes\uu\filter;

use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Tariff
 */
class TariffFilter extends Tariff
{
    public $name = '';
    public $tariff_status_id = '';
    public $tariff_person_id = '';
    public $currency_id = '';
    public $country_id = '';

    public $voip_tarificate_id = '';
    public $voip_group_id = '';

    public $service_type_id = '';

    /**
     * @param int $serviceTypeId
     */
    public function __construct($serviceTypeId)
    {
        $this->service_type_id = $serviceTypeId;
        parent::__construct();
    }

    /**
     * @return ServiceType
     */
    public function getServiceType()
    {
        return ServiceType::findOne($this->service_type_id);
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Tariff::find()
            ->joinWith('country')
            ->joinWith('status')
            ->with('tariffPeriods');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $tariffTableName = Tariff::tableName();

        $this->name !== '' && $query->andWhere(['like', $tariffTableName . '.name', $this->name]);
        $this->tariff_status_id !== '' && $query->andWhere([$tariffTableName . '.tariff_status_id' => $this->tariff_status_id]);
        $this->tariff_person_id !== '' && $query->andWhere([$tariffTableName . '.tariff_person_id' => $this->tariff_person_id]);
        $this->currency_id !== '' && $query->andWhere([$tariffTableName . '.currency_id' => $this->currency_id]);
        $this->country_id !== '' && $query->andWhere([$tariffTableName . '.country_id' => $this->country_id]);
        $this->service_type_id !== '' && $query->andWhere([$tariffTableName . '.service_type_id' => $this->service_type_id]);

        $this->voip_tarificate_id !== '' && $query->andWhere([$tariffTableName . '.voip_tarificate_id' => $this->voip_tarificate_id]);
        $this->voip_group_id !== '' && $query->andWhere([$tariffTableName . '.voip_group_id' => $this->voip_group_id]);

        return $dataProvider;
    }
}
