<?php

use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffStatus;
use app\models\Country;
use app\models\Currency;

/**
 * Handles the creation for table `uu_one_time`.
 */
class m160902_091721_add_uu_one_time extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_ONE_TIME,
            'name' => 'Разовая услуга',
        ]);

        $this->insert(Resource::tableName(), [
            'id' => Resource::ID_ONE_TIME,
            'name' => 'Стоимость',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_ONE_TIME,
            'unit' => 'у.е.',
        ]);

        $this->addTariff('Разовая услуга', Country::RUSSIA, Currency::RUB);
        $this->addTariff('Single-Service', Country::GERMANY, Currency::EUR);
        $this->addTariff('Jedno-time ponuka', Country::SLOVAKIA, Currency::EUR);
        $this->addTariff('Egyszeri ajánlat', Country::HUNGARY, Currency::HUF);
    }

    /**
     * Создать тариф "разовая услуга"
     * @param string $name
     * @param int $countryId
     * @param int $currencyId
     * @throws Exception
     */
    public function addTariff($name, $countryId, $currencyId)
    {
        $tariff = new Tariff();
        $tariff->name = $name;
        $tariff->is_autoprolongation = 0;
        $tariff->is_charge_after_period = 1;
        $tariff->is_charge_after_blocking = 1;
        $tariff->is_include_vat = 1;
        $tariff->currency_id = $currencyId;
        $tariff->service_type_id = ServiceType::ID_ONE_TIME;
        $tariff->tariff_status_id = TariffStatus::ID_SPECIAL;
        $tariff->country_id = $countryId;
        $tariff->tariff_person_id = TariffPerson::ID_ALL;
        $tariff->is_default = 1;
        if (!$tariff->save() || !$tariff->id) {
            throw new Exception(implode(' ', $tariff->getErrors()));
        }

        $tariffPeriod = new TariffPeriod();
        $tariffPeriod->price_setup = 0;
        $tariffPeriod->price_min = 0;
        $tariffPeriod->price_per_period = 0;
        $tariffPeriod->tariff_id = $tariff->id;
        $tariffPeriod->period_id = Period::ID_DAY;
        $tariffPeriod->charge_period_id = Period::ID_DAY; // чтобы псевдо-услуга долго не висела, а закрылась в тот же день
        if (!$tariffPeriod->save() || !$tariffPeriod->id) {
            throw new Exception(implode(' ', $tariffPeriod->getErrors()));
        }

        $tariffResource = new TariffResource();
        $tariffResource->amount = 0;
        $tariffResource->price_per_unit = 1; // стоимость определяется кол-вом ресурса
        $tariffResource->price_min = 0;
        $tariffResource->resource_id = Resource::ID_ONE_TIME;
        $tariffResource->tariff_id = $tariff->id;
        if (!$tariffResource->save() || !$tariffResource->id) {
            throw new Exception(implode(' ', $tariffResource->getErrors()));
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->delete(TariffResource::tableName(), [
            'resource_id' => Resource::ID_ONE_TIME,
        ]);

        $this->delete(Resource::tableName(), [
            'id' => Resource::ID_ONE_TIME,
        ]);

        $this->delete(Tariff::tableName(), [
            'service_type_id' => ServiceType::ID_ONE_TIME,
        ]);
        // TariffPeriod удалится рекурсивно сам

        $this->delete(ServiceType::tableName(), [
            'id' => ServiceType::ID_ONE_TIME,
        ]);
    }
}
