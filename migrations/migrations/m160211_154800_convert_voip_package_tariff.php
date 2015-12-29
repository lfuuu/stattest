<?php

use app\classes\uu\model\Period;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\classes\uu\model\TariffVoipCity;

class m160211_154800_convert_voip_package_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->convertTariff();
        $this->convertTariffPeriod();
        $this->addConnectionPoint();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        // TariffPeriod, TariffResource, TariffVoipCity должны удалиться CASCADE
        $this->delete(Tariff::tableName(), [
            'service_type_id' => ServiceType::ID_VOIP_PACKAGE,
        ]);
    }

    /**
     * Конвертировать Tariff
     */
    protected function convertTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $personIdAll = TariffPerson::ID_ALL;
        $serviceTypeIdVoipPackage = ServiceType::ID_VOIP_PACKAGE;
        $tariffTableName = Tariff::tableName();
        $deltaVoipPackageTariff = Tariff::DELTA_VOIP_PACKAGE;

        $this->execute("INSERT INTO {$tariffTableName}
          (id,  service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
          is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
          insert_user_id, insert_time, update_user_id, update_time)

  SELECT id + {$deltaVoipPackageTariff},
      {$serviceTypeIdVoipPackage},
      currency_id,
      name,
      {$statusIdPublic},
      price_include_vat,
      {$personIdAll},
      country_id,
      1,
      0,
      0,
      0,
      null,
      null,
      null,
      null

  FROM tarifs_voip_package
    ");
    }

    /**
     * заполнить TariffVoipCity
     */
    protected function addConnectionPoint()
    {
        $deltaVoipPackageTariff = Tariff::DELTA_VOIP_PACKAGE;
        $tariffVoipCityTableName = TariffVoipCity::tableName();

        $this->execute("INSERT INTO {$tariffVoipCityTableName}
          (tariff_id, city_id)

  SELECT tarifs_voip_package.id + {$deltaVoipPackageTariff}, city.id
  FROM tarifs_voip_package, city
  WHERE tarifs_voip_package.connection_point_id = city.connection_point_id ");
    }

    /**
     * Конвертировать TariffPeriod
     */
    protected function convertTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaVoipPackageTariff = Tariff::DELTA_VOIP_PACKAGE;
        $tariffPeriodTableName = TariffPeriod::tableName();

        $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)

  SELECT periodical_fee, 0, min_payment, id + {$deltaVoipPackageTariff}, {$periodIdMonth}, {$periodIdMonth}
  FROM tarifs_voip_package");

    }

}