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

class m160118_091320_convert_vpbx_tariff extends \app\classes\Migration
{
    public function safeUp()
    {
        $this->convertTariff();
        $this->convertTariffPeriod();
        $this->convertTariffResource();
        $this->makeBeauty();
    }

    public function safeDown()
    {
        // TariffPeriod и TariffResource удалятся CASCADE
        $this->delete(Tariff::tableName(), ['service_type_id' => ServiceType::ID_VPBX]);
    }

    protected function convertTariff()
    {
// выбрать уникальные имена тарифов (раньше тарифы дублировались с добавлением услуг в название)
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $statusIdSpecial = TariffStatus::ID_SPECIAL;
        $statusIdArchive = TariffStatus::ID_ARCHIVE;

        $groupIdAll = TariffPerson::ID_ALL;

        $countryRussia = Country::RUSSIA;
        $countryHungary = Country::HUNGARY;

        $serviceTypeIdVpbx = ServiceType::ID_VPBX;
        $deltaVpbxTariff = Tariff::DELTA_VPBX;

        $tariffTableName = Tariff::tableName();

        $this->execute("INSERT INTO {$tariffTableName}
          (id, service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
          is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
          insert_user_id, insert_time, update_user_id, update_time)

  SELECT
      id + {$deltaVpbxTariff},
      {$serviceTypeIdVpbx},
      currency,
      description,
      CASE status
        WHEN 'public' THEN {$statusIdPublic}
        WHEN 'special' THEN {$statusIdSpecial}
        WHEN 'archive' THEN {$statusIdArchive}
      END,
      price_include_vat,
      {$groupIdAll},
      IF(currency = 'HUF', {$countryHungary}, {$countryRussia}),
      IF(description LIKE 'Тест%' OR description LIKE 'test%', 0, 1),
      1,
      1,
      0,
      edit_user,
      edit_time,
      edit_user,
      edit_time
  FROM tarifs_virtpbx
  WHERE (description NOT LIKE '%+%' OR description = 'Тариф Лайт+Запись')
    AND description != 'Запись разговоров'
    AND description != 'Дисковое пространство 1 Gb'
    AND description != 'Виртуальный Факс'
    AND description != 'Дополнительный внутренний пользователь'
    ");
    }

    protected function convertTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();

        $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)

  SELECT old.price, 0, 0, new.id, {$periodIdMonth}, {$periodIdMonth}
  FROM tarifs_virtpbx old,
        {$tariffTableName} new
  WHERE old.description = new.name");

    }

    protected function convertTariffResource()
    {
        $resourceIdAbonent = Resource::ID_VPBX_ABONENT;
        $resourceIdDisk = Resource::ID_VPBX_DISK;
        $resourceIdExtDid = Resource::ID_VPBX_EXT_DID;
        $resourceIdRecord = Resource::ID_VPBX_RECORD;
        $resourceIdWebCall = Resource::ID_VPBX_WEB_CALL;
        $resourceIdFax = Resource::ID_VPBX_FAX;

        $serviceTypeIdVpbx = ServiceType::ID_VPBX;

        $tariffTableName = Tariff::tableName();
        $tariffResourceTableName = TariffResource::tableName();

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, resource_id, tariff_id)

  SELECT num_ports, overrun_per_port, {$resourceIdAbonent}, new.id
  FROM tarifs_virtpbx old, {$tariffTableName} new
  WHERE old.description = new.name");

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, resource_id, tariff_id)

  SELECT space/1024, overrun_per_gb, {$resourceIdDisk}, new.id
  FROM tarifs_virtpbx old, {$tariffTableName} new
  WHERE old.description = new.name");

        // @todo добавляю стоимость 190 у.е. Очевидно, что в разных странах разная, но пока никто не знает, какая именно. Поэтому менеджер потом вручную исправит
        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, resource_id, tariff_id)

  SELECT 0, 190, {$resourceIdExtDid}, {$tariffTableName}.id
  FROM {$tariffTableName}
  WHERE {$tariffTableName}.service_type_id = {$serviceTypeIdVpbx}
    ");

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, resource_id, tariff_id)

  SELECT is_record, 590, {$resourceIdRecord}, new.id
  FROM tarifs_virtpbx old, {$tariffTableName} new
  WHERE old.description = new.name");

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, resource_id, tariff_id)

  SELECT is_web_call, 354, {$resourceIdWebCall}, new.id
  FROM tarifs_virtpbx old, {$tariffTableName} new
  WHERE old.description = new.name");

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, resource_id, tariff_id)

  SELECT is_web_call, 118, {$resourceIdFax}, new.id
  FROM tarifs_virtpbx old,
        {$tariffTableName} new
  WHERE old.description = new.name");
    }

    protected function makeBeauty()
    {
        // привести названия в красивый вид
        $tariffTableName = Tariff::tableName();
        $this->execute("UPDATE {$tariffTableName}
  SET name = 'Тариф Лайт плюс Запись'
  WHERE name = 'Тариф Лайт+Запись'");

        $this->execute("UPDATE {$tariffTableName}
  SET name = REPLACE(name, '  ', ' ')
  WHERE name LIKE '%  %'");

    }
}