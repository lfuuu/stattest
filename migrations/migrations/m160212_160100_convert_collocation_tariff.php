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

class m160212_160100_convert_collocation_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->addServiceType();
        $this->addResource();
        $this->convertTariff();
        $this->convertTariffPeriod();
        $this->convertTariffResource();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        // TariffPeriod и TariffResource должны удалиться CASCADE
        $this->delete(Tariff::tableName(), [
            'service_type_id' => ServiceType::ID_COLLOCATION,
        ]);
        $this->deleteResource();
        $this->deleteServiceType();
    }

    /**
     * Создать тип услуги
     */
    protected function addServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->insert($tableName, [
            'id' => ServiceType::ID_COLLOCATION,
            'name' => 'Collocation',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_COLLOCATION,
        ]);
    }

    /**
     * Создать ресурс
     */
    protected function addResource()
    {
        $tableName = Resource::tableName();

        $this->insert($tableName, [
            'id' => Resource::ID_COLLOCATION_TRAFFIC_RUSSIA,
            'name' => 'Трафик Russia',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_COLLOCATION,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_COLLOCATION_TRAFFIC_RUSSIA2,
            'name' => 'Трафик Russia2',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_COLLOCATION,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_COLLOCATION_TRAFFIC_FOREINGN,
            'name' => 'Трафик Foreign',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_COLLOCATION,
        ]);
    }

    /**
     * Удалить ресурс
     */
    protected function deleteResource()
    {
        $tableName = Resource::tableName();
        $this->delete($tableName, [
            'id' => [
                Resource::ID_COLLOCATION_TRAFFIC_RUSSIA,
                Resource::ID_COLLOCATION_TRAFFIC_RUSSIA2,
                Resource::ID_COLLOCATION_TRAFFIC_FOREINGN,
            ]
        ]);
    }

    /**
     * Конвертировать Tariff
     */
    protected function convertTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $statusIdSpecial = TariffStatus::ID_SPECIAL;
        $statusIdArchive = TariffStatus::ID_ARCHIVE;

        $serviceTypeIdCollocation = ServiceType::ID_COLLOCATION;
        $countryIdRussia = Country::RUSSIA;

        $personIdAll = TariffPerson::ID_ALL;
        $tariffTableName = Tariff::tableName();
        $deltaCollocationTariff = Tariff::DELTA_COLLOCATION;

        $this->execute("INSERT INTO {$tariffTableName}
          (id,  service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
          is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
          insert_user_id, insert_time, update_user_id, update_time)

  SELECT id + {$deltaCollocationTariff},
      {$serviceTypeIdCollocation},
      currency,
      name,
      CASE status
        WHEN 'public' THEN {$statusIdPublic}
        WHEN 'special' THEN {$statusIdSpecial}
        WHEN 'archive' THEN {$statusIdArchive}
      END,
      price_include_vat,
      {$personIdAll},
      {$countryIdRussia},
      1,
      0,
      0,
      0,
      edit_user,
      edit_time,
      edit_user,
      edit_time

  FROM tarifs_internet
  WHERE type = 'C'
    ");
    }

    /**
     * Конвертировать TariffPeriod
     */
    protected function convertTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaCollocationTariff = Tariff::DELTA_COLLOCATION;
        $tariffPeriodTableName = TariffPeriod::tableName();

        $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)

  SELECT pay_month, pay_once, 0, id + {$deltaCollocationTariff}, {$periodIdMonth}, {$periodIdMonth}
  FROM tarifs_internet
  WHERE type = 'C'
  ");

    }

    /**
     * Конвертировать TariffPeriodResource
     */
    protected function convertTariffResource()
    {
        $deltaCollocationTariff = Tariff::DELTA_COLLOCATION;
        $resourceIdTrafficRussia = Resource::ID_COLLOCATION_TRAFFIC_RUSSIA;
        $resourceIdTrafficRussia2 = Resource::ID_COLLOCATION_TRAFFIC_RUSSIA2;
        $resourceIdTrafficForeign = Resource::ID_COLLOCATION_TRAFFIC_FOREINGN;
        $tariffResourceTableName = TariffResource::tableName();

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, price_min, resource_id, tariff_id)

  SELECT month_r, pay_r, 0, {$resourceIdTrafficRussia}, id + {$deltaCollocationTariff}
  FROM tarifs_internet
  WHERE type = 'C'
  ");

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, price_min, resource_id, tariff_id)

  SELECT month_r2, pay_r2, 0, {$resourceIdTrafficRussia2}, id + {$deltaCollocationTariff}
  FROM tarifs_internet
  WHERE type = 'C'
  ");

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, price_min, resource_id, tariff_id)

  SELECT month_f, pay_f, 0, {$resourceIdTrafficForeign}, id + {$deltaCollocationTariff}
  FROM tarifs_internet
  WHERE type = 'C'
  ");
    }
}