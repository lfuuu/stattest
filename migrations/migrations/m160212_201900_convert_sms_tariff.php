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

class m160212_201900_convert_sms_tariff extends \app\classes\Migration
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
            'service_type_id' => ServiceType::ID_SMS,
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
            'id' => ServiceType::ID_SMS,
            'name' => 'SMS',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_SMS,
        ]);
    }

    /**
     * Создать ресурс
     */
    protected function addResource()
    {
        $tableName = Resource::tableName();

        $this->insert($tableName, [
            'id' => Resource::ID_SMS,
            'name' => 'СМС',
            'unit' => 'шт.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_SMS,
        ]);
    }

    /**
     * Удалить ресурс
     */
    protected function deleteResource()
    {
        $tableName = Resource::tableName();
        $this->delete($tableName, [
            'id' => Resource::ID_SMS,
        ]);
    }

    /**
     * Конвертировать Tariff
     */
    protected function convertTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;

        $serviceTypeIdSms = ServiceType::ID_SMS;
        $countryIdRussia = Country::RUSSIA;

        $personIdAll = TariffPerson::ID_ALL;
        $tariffTableName = Tariff::tableName();
        $deltaItParkTariff = Tariff::DELTA_SMS;

        $this->execute("INSERT INTO {$tariffTableName}
          (id,  service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
          is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
          insert_user_id, insert_time, update_user_id, update_time)

  SELECT tarifs_sms.id + {$deltaItParkTariff},
      {$serviceTypeIdSms},
      tarifs_sms.currency,
      tarifs_sms.description,
      {$statusIdPublic},
      tarifs_sms.price_include_vat,
      {$personIdAll},
      {$countryIdRussia},
      1,
      0,
      0,
      0,
      user_users.id,
      tarifs_sms.edit_time,
      user_users.id,
      tarifs_sms.edit_time

  FROM tarifs_sms
  LEFT JOIN user_users
    ON tarifs_sms.edit_user = user_users.id
    ");
    }

    /**
     * Конвертировать TariffPeriod
     */
    protected function convertTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaSmsTariff = Tariff::DELTA_SMS;
        $tariffPeriodTableName = TariffPeriod::tableName();

        $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)

  SELECT per_month_price, 0, 0, id + {$deltaSmsTariff},
      {$periodIdMonth},
      {$periodIdMonth}
  FROM tarifs_sms
  ");
    }

    /**
     * Конвертировать TariffPeriodResource
     */
    protected function convertTariffResource()
    {
        $deltaSmsTariff = Tariff::DELTA_SMS;
        $resourceIdSms = Resource::ID_SMS;
        $tariffResourceTableName = TariffResource::tableName();

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, price_min, resource_id, tariff_id)

  SELECT 0, per_sms_price, 0, {$resourceIdSms}, id + {$deltaSmsTariff}
  FROM tarifs_sms
  ");
    }
}