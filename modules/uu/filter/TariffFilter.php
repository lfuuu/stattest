<?php

namespace app\modules\uu\filter;

use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffVoipCity;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Tariff
 */
class TariffFilter extends Tariff
{
    public $name = '';
    public $tariff_status_id = '';
    public $tariff_person_id = '';
    public $tag_id = '';
    public $currency_id = '';
    public $country_id = '';

    public $voip_group_id = '';
    public $voip_city_id = '';

    public $service_type_id = '';

    public $is_autoprolongation = '';
    public $is_charge_after_blocking = '';
    public $is_include_vat = '';
    public $is_default = '';
    public $is_postpaid = '';

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
            ->with('tariffPeriods')
            ->with('voipCities');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $tariffTableName = Tariff::tableName();

        $this->name !== '' && $query->andWhere(['like', $tariffTableName . '.name', $this->name]);
        $this->tariff_status_id !== '' && $query->andWhere([$tariffTableName . '.tariff_status_id' => $this->tariff_status_id]);
        $this->tariff_person_id !== '' && $query->andWhere([$tariffTableName . '.tariff_person_id' => $this->tariff_person_id]);
        $this->tag_id !== '' && $query->andWhere([$tariffTableName . '.tag_id' => $this->tag_id]);
        $this->currency_id !== '' && $query->andWhere([$tariffTableName . '.currency_id' => $this->currency_id]);
        $this->country_id !== '' && $query->andWhere([$tariffTableName . '.country_id' => $this->country_id]);
        $this->service_type_id !== '' && $query->andWhere([$tariffTableName . '.service_type_id' => $this->service_type_id]);
        $this->is_autoprolongation !== '' && $query->andWhere([$tariffTableName . '.is_autoprolongation' => (int)$this->is_autoprolongation]);
        $this->is_charge_after_blocking !== '' && $query->andWhere([$tariffTableName . '.is_charge_after_blocking' => (int)$this->is_charge_after_blocking]);
        $this->is_include_vat !== '' && $query->andWhere([$tariffTableName . '.is_include_vat' => (int)$this->is_include_vat]);
        $this->is_default !== '' && $query->andWhere([$tariffTableName . '.is_default' => (int)$this->is_default]);
        $this->is_postpaid !== '' && $query->andWhere([$tariffTableName . '.is_postpaid' => (int)$this->is_postpaid]);
        $this->voip_group_id !== '' && $query->andWhere([$tariffTableName . '.voip_group_id' => $this->voip_group_id]);

        if ($this->voip_city_id !== '') {
            $query->joinWith('voipCities');
            $query->andWhere([TariffVoipCity::tableName() . '.city_id' => $this->voip_city_id]);
        }

        return $dataProvider;
    }
}
