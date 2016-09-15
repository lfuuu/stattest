<?php

namespace app\classes\uu\filter;

use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffVoipCity;
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
    public $voip_city_id = '';

    public $service_type_id = '';

    public $is_uu = '';

    /**
     * @param int $serviceTypeId
     */
    public function __construct($serviceTypeId)
    {
        $this->service_type_id = $serviceTypeId;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['voip_city_id', 'is_uu'], 'integer'],
            ]);
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
            ->with('tariffPeriods')
            ->with('voipCities');

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

        if ($this->is_uu !== '') {
            $query->andWhere([$this->is_uu ? '>=' : '<=', $tariffTableName . '.id', Tariff::DELTA]);
        }

        $this->voip_tarificate_id !== '' && $query->andWhere([$tariffTableName . '.voip_tarificate_id' => $this->voip_tarificate_id]);
        $this->voip_group_id !== '' && $query->andWhere([$tariffTableName . '.voip_group_id' => $this->voip_group_id]);

        if ($this->voip_city_id !== '') {
            $query->joinWith('voipCities');
            $query->andWhere([TariffVoipCity::tableName() . '.city_id' => $this->voip_city_id]);
        }

        return $dataProvider;
    }
}
