<?php

namespace app\modules\uu\filter;

use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffStatus;
use app\modules\uu\models\TariffTags;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipCountry;
use app\modules\uu\models\TariffVoipNdcType;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;

/**
 * Фильтрация для Tariff
 */
class TariffFilter extends Tariff
{
    public $id = '';
    public $name = '';
    public $tariff_status_id = '';
    public $tariff_person_id = '';
    public $tag_id = '';
    public $currency_id = '';
    public $country_id = '';

    public $voip_group_id = '';
    public $voip_country_id = '';
    public $voip_city_id = '';
    public $voip_ndc_type_id = '';
    public $organization_id = '';

    public $service_type_id = '';

    public $is_autoprolongation = '';
    public $is_charge_after_blocking = '';
    public $is_include_vat = '';
    public $is_default = '';
    public $is_postpaid = '';

    public $is_show_archive = false;


    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id', 'country_id', 'voip_country_id', 'voip_city_id', 'voip_ndc_type_id', 'organization_id'], 'integer'];
        return $rules;
    }

    public function initExtraValues()
    {
        if (isset($_COOKIE['Form' . $this->formName() . 'Data'])) {
            $data = Json::decode($_COOKIE['Form' . $this->formName() . 'Data']);

            if (isset($data['is_show_archive'])) {
                $this->is_show_archive = $data['is_show_archive'];
            }
        }
    }

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
            ->joinWith('status')
            ->with('tariffPeriods')
            ->with('tariffCountries')
            ->with('tariffVoipCountries')
            ->with('voipCities');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $tariffTableName = Tariff::tableName();

        $this->id !== '' && $query->andWhere([$tariffTableName . '.id' => $this->id]);
        $this->name !== '' && $query->andWhere(['like', $tariffTableName . '.name', $this->name]);
        $this->tariff_status_id !== '' && $query->andWhere([$tariffTableName . '.tariff_status_id' => $this->tariff_status_id]);
        $this->tariff_person_id !== '' && $query->andWhere([$tariffTableName . '.tariff_person_id' => $this->tariff_person_id]);
        $this->tag_id !== '' && $query->andWhere([$tariffTableName . '.tag_id' => $this->tag_id]);
        $this->currency_id !== '' && $query->andWhere([$tariffTableName . '.currency_id' => $this->currency_id]);
        $this->service_type_id !== '' && $query->andWhere([$tariffTableName . '.service_type_id' => $this->service_type_id]);
        $this->is_autoprolongation !== '' && $query->andWhere([$tariffTableName . '.is_autoprolongation' => (int)$this->is_autoprolongation]);
        $this->is_charge_after_blocking !== '' && $query->andWhere([$tariffTableName . '.is_charge_after_blocking' => (int)$this->is_charge_after_blocking]);
        $this->is_include_vat !== '' && $query->andWhere([$tariffTableName . '.is_include_vat' => (int)$this->is_include_vat]);
        $this->is_default !== '' && $query->andWhere([$tariffTableName . '.is_default' => (int)$this->is_default]);
        $this->is_postpaid !== '' && $query->andWhere([$tariffTableName . '.is_postpaid' => (int)$this->is_postpaid]);
        $this->voip_group_id !== '' && $query->andWhere([$tariffTableName . '.voip_group_id' => $this->voip_group_id]);

        !$this->is_show_archive && $query->andWhere(['NOT', ['tariff_status_id' => TariffStatus::ARCHIVE_LIST]]);


        if ($this->country_id !== '') {
            $query->joinWith('tariffCountries');
            $query->andWhere([TariffCountry::tableName() . '.country_id' => $this->country_id]);
        }

        if ($this->voip_country_id !== '') {
            $query->joinWith('tariffVoipCountries');
            $query->andWhere([TariffVoipCountry::tableName() . '.country_id' => $this->voip_country_id]);
        }

        if ($this->voip_city_id !== '') {
            $query->joinWith('voipCities');
            $query->andWhere([TariffVoipCity::tableName() . '.city_id' => $this->voip_city_id]);
        }

        if ($this->voip_ndc_type_id !== '') {
            $query->joinWith('voipNdcTypes');
            $query->andWhere([TariffVoipNdcType::tableName() . '.ndc_type_id' => $this->voip_ndc_type_id]);
        }

        if ($this->organization_id !== '') {
            $query->joinWith('organizations');
            $query->andWhere([TariffOrganization::tableName() . '.organization_id' => $this->organization_id]);
        }

        return $dataProvider;
    }

    /**
     * Получить запрос для списка
     *
     * @param int $id
     * @param int $serviceTypeId
     * @param int $countryId
     * @param int $currencyId
     * @param int $isDefault
     * @param int $isPostPaid
     * @param int $tariffStatusId
     * @param int $tariffPersonId
     * @param int $tariffTagId
     * @param int $voipGroupId
     * @param int $voipCityId
     * @param int $voipNdcTypeId
     * @param int $organizationId
     * @param int $isIncludeVat
     * @param int $voipCountryId
     * @return \yii\db\ActiveQuery
     */
    public static function getListQuery(
        $id,
        $serviceTypeId,
        $countryId,
        $currencyId,
        $isDefault,
        $isPostPaid,
        $tariffStatusId,
        $tariffPersonId,
        $tariffTagId,
        $tariffTagsId,
        $voipGroupId,
        $voipCityId,
        $voipNdcTypeId,
        $organizationId,
        $isIncludeVat,
        $voipCountryId
    )
    {
        $query = Tariff::find();
        $tariffTable = Tariff::tableName();

        $query
            ->with('tariffPeriods')
            ->with('tariffPeriods.chargePeriod')
            ->with('tariffCountries')
            ->with('tariffVoipCountries.country')
            ->with('package')
            ->with('serviceType')
            ->with('status')
            ->with('person')
            ->with('tag')
            ->with('tariffResources.resource.serviceType')
            ->with('voipGroup')
            ->with('voipCities.city')
            ->with('voipNdcTypes.ndcType')
            ->with('organizations.organization')
            ->with('packageMinutes.destination')
            ->with('packagePrices.destination')
            ->with('packagePricelists.pricelist')
            ->with('tariffTags')
            ->with('tariffTags.tag');

        $id && $query->andWhere(["{$tariffTable}.id" => $id]);
        $serviceTypeId && $query->andWhere(["{$tariffTable}.service_type_id" => (int)$serviceTypeId]);
        $currencyId && $query->andWhere(["{$tariffTable}.currency_id" => $currencyId]);
        null !== $isDefault && $query->andWhere(["{$tariffTable}.is_default" => (int)$isDefault]);
        null !== $isPostPaid && $query->andWhere(["{$tariffTable}.is_postpaid" => (int)$isPostPaid]);
        null !== $isIncludeVat && $query->andWhere(["{$tariffTable}.is_include_vat" => (int)$isIncludeVat]);
        $tariffStatusId && $query->andWhere(["{$tariffTable}.tariff_status_id" => (int)$tariffStatusId]);
        $tariffPersonId && $query->andWhere(["{$tariffTable}.tariff_person_id" => [TariffPerson::ID_ALL, $tariffPersonId]]);
        $tariffTagId && $query->andWhere(["{$tariffTable}.tariff_tag_id" => $tariffTagId]);
        $voipGroupId && $query->andWhere(["{$tariffTable}.voip_group_id" => (int)$voipGroupId]);

        if ($tariffTagsId) {
            $query->joinWith('tariffTags')
                ->andWhere([TariffTags::tableName() . '.tag_id' => $tariffTagsId]);
        }

        if ($countryId) {
            $query
                ->joinWith('tariffCountries')
                ->andWhere([TariffCountry::tableName() . '.country_id' => $countryId]);
        }

        if ($voipCountryId) {
            $query
                ->joinWith('tariffVoipCountries')
                ->andWhere([
                    'OR',
                    [TariffVoipCountry::tableName() . '.country_id' => $voipCountryId],
                    [TariffVoipCountry::tableName() . '.country_id' => null]
                ]);
        }

        if ($voipCityId) {
            $query
                ->joinWith('voipCities')
                ->andWhere([
                    'OR',
                    [TariffVoipCity::tableName() . '.city_id' => $voipCityId], // если в тарифе хоть один город, то надо только точное соотвествие
                    [TariffVoipCity::tableName() . '.city_id' => null] // если в тарифе ни одного города нет, то это означает "любой город этой страны"
                ]);
        }

        if ($voipNdcTypeId) {
            $query
                ->joinWith('voipNdcTypes')
                ->andWhere([TariffVoipNdcType::tableName() . '.ndc_type_id' => $voipNdcTypeId]);
        }

        if ($organizationId) {
            $query
                ->joinWith('organizations')
                ->andWhere([TariffOrganization::tableName() . '.organization_id' => $organizationId]);
        }

        return $query;
    }
}
