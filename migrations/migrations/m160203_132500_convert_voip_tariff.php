<?php

use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffStatus;
use app\classes\uu\model\TariffVoipCity;
use app\classes\uu\model\TariffVoipTarificate;

class m160203_132500_convert_voip_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->convertTariff();
        $this->convertTariffPeriod();
        $this->convertTariffResource();
        $this->addConnectionPoint();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        // TariffPeriod, TariffResource, TariffVoipCity и addVoip должны удалиться CASCADE
        $this->delete(Tariff::tableName(), [
            'service_type_id' => ServiceType::ID_VOIP,
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
        $statusIdTest = TariffStatus::ID_TEST;
        $statusId8800 = TariffStatus::ID_VOIP_8800;
        $statusIdOperator = TariffStatus::ID_VOIP_OPERATOR;
        $statusIdTransit = TariffStatus::ID_VOIP_TRANSIT;

        $personIdAll = TariffPerson::ID_ALL;
        $serviceTypeIdVoip = ServiceType::ID_VOIP;
        $tariffTableName = Tariff::tableName();
        $deltaVoipTariff = Tariff::DELTA_VOIP;

        $tarificateBySecond = TariffVoipTarificate::ID_VOIP_BY_SECOND;
        $tarificateBySecondFree = TariffVoipTarificate::ID_VOIP_BY_SECOND_FREE;
        $tarificateByMinute = TariffVoipTarificate::ID_VOIP_BY_MINUTE;
        $tarificateByMinuteFree = TariffVoipTarificate::ID_VOIP_BY_MINUTE_FREE;

        $this->execute("INSERT INTO {$tariffTableName}
          (id,  service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
          is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
          insert_user_id, insert_time, update_user_id, update_time, voip_tarificate_id)

  SELECT id + {$deltaVoipTariff},
      {$serviceTypeIdVoip},
      currency_id,
      name,
      CASE status
        WHEN 'public' THEN {$statusIdPublic}
        WHEN 'special' THEN {$statusIdSpecial}
        WHEN 'archive' THEN {$statusIdArchive}
        WHEN 'test' THEN {$statusIdTest}
        WHEN '7800' THEN {$statusId8800}
        WHEN 'operator' THEN {$statusIdOperator}
        WHEN 'transit' THEN {$statusIdTransit}
      END,
      price_include_vat,
      {$personIdAll},
      country_id,
      1,
      0,
      0,
      0,
      edit_user,
      edit_time,
      edit_user,
      edit_time,
      IF(tariffication_by_minutes=0,
        IF(tariffication_free_first_seconds=0, {$tarificateBySecond}, {$tarificateBySecondFree}),
        IF(tariffication_free_first_seconds=0, {$tarificateByMinute}, {$tarificateByMinuteFree})
    )

  FROM tarifs_voip
    ");

    }

    /**
     * Конвертировать TariffPeriod
     */
    protected function convertTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaVoipTariff = Tariff::DELTA_VOIP;
        $tariffPeriodTableName = TariffPeriod::tableName();

        $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)

  SELECT month_number, once_number, 0, id + {$deltaVoipTariff}, {$periodIdMonth}, {$periodIdMonth}
  FROM tarifs_voip");

    }

    /**
     * Конвертировать TariffPeriodResource
     */
    protected function convertTariffResource()
    {
        $deltaVoipTariff = Tariff::DELTA_VOIP;
        $resourceIdLine = Resource::ID_VOIP_LINE;
        $resourceIdCalls = Resource::ID_VOIP_CALLS;
        $tariffResourceTableName = TariffResource::tableName();

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, price_min, resource_id, tariff_id)

  SELECT 1, month_line, 0, {$resourceIdLine}, id + {$deltaVoipTariff}
  FROM tarifs_voip");

        $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, price_min, resource_id, tariff_id)

  SELECT 0, 1, 0, {$resourceIdCalls}, id + {$deltaVoipTariff}
  FROM tarifs_voip");
    }

    /**
     * заполнить TariffVoipCity
     */
    protected function addConnectionPoint()
    {
        $deltaVoipTariff = Tariff::DELTA_VOIP;
        $tariffVoipCityTableName = TariffVoipCity::tableName();

        $this->execute("INSERT INTO {$tariffVoipCityTableName}
          (tariff_id, city_id)

  SELECT tarifs_voip.id + {$deltaVoipTariff}, city.id
  FROM tarifs_voip, city
  WHERE tarifs_voip.connection_point_id = city.connection_point_id ");
    }
}