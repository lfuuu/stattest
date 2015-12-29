<?php

use app\classes\uu\model\Period;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\models\Country;

class m160212_192300_convert_extra_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->addServiceType();
        $this->convertTariff();
        $this->convertTariffPeriod();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $ids = [
            ServiceType::ID_IT_PARK,
            ServiceType::ID_DOMAIN,
            ServiceType::ID_MAILSERVER,
            ServiceType::ID_ATS,
            ServiceType::ID_SITE,
            ServiceType::ID_SMS_GATE,
            ServiceType::ID_USPD,
            ServiceType::ID_WELLSYSTEM,
            ServiceType::ID_WELLTIME,
            ServiceType::ID_EXTRA,
        ];

        // TariffPeriod и TariffResource должны удалиться CASCADE
        $this->delete(Tariff::tableName(), [
            'service_type_id' => $ids,
        ]);

        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => $ids,
        ]);
    }

    /**
     * Создать тип услуги
     */
    protected function addServiceType()
    {
        $tableName = ServiceType::tableName();

        $this->insert($tableName, [
            'id' => ServiceType::ID_IT_PARK,
            'name' => 'IT Park',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_DOMAIN,
            'name' => 'Регистрация доменов',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_MAILSERVER,
            'name' => 'Виртуальный почтовый сервер',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_ATS,
            'name' => 'Старый ВАТС',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_SITE,
            'name' => 'Сайт',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_SMS_GATE,
            'name' => 'SMS Gate',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_USPD,
            'name' => 'Провайдер',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_WELLSYSTEM,
            'name' => 'Wellsystem',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_WELLTIME,
            'name' => 'Welltime',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_EXTRA,
            'name' => 'Дополнительные услуги',
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

        $serviceTypeIdItpark = ServiceType::ID_IT_PARK;
        $serviceTypeIdDomain = ServiceType::ID_DOMAIN;
        $serviceTypeIdMailserver = ServiceType::ID_MAILSERVER;
        $serviceTypeIdAts = ServiceType::ID_ATS;
        $serviceTypeIdSite = ServiceType::ID_SITE;
        $serviceTypeIdSms = ServiceType::ID_SMS_GATE;
        $serviceTypeIdUspd = ServiceType::ID_USPD;
        $serviceTypeIdWellsystem = ServiceType::ID_WELLSYSTEM;
        $serviceTypeIdWelltime = ServiceType::ID_WELLTIME;
        $serviceTypeIdExtra = ServiceType::ID_EXTRA;

        $countryIdRussia = Country::RUSSIA;

        $personIdAll = TariffPerson::ID_ALL;
        $tariffTableName = Tariff::tableName();
        $deltaExtraTariff = Tariff::DELTA_EXTRA;

        $this->execute("INSERT INTO {$tariffTableName}
          (id,  service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
          is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
          insert_user_id, insert_time, update_user_id, update_time)

  SELECT tarifs_extra.id + {$deltaExtraTariff},
      IF(tarifs_extra.status = 'itpark', {$serviceTypeIdItpark},
          CASE tarifs_extra.code
            WHEN 'domain' THEN {$serviceTypeIdDomain}
            WHEN 'ip' THEN {$serviceTypeIdWelltime}
            WHEN 'mailserver' THEN {$serviceTypeIdMailserver}
            WHEN 'phone_ats' THEN {$serviceTypeIdAts}
            WHEN 'site' THEN {$serviceTypeIdSite}
            WHEN 'sms_gate' THEN {$serviceTypeIdSms}
            WHEN 'uspd' THEN {$serviceTypeIdUspd}
            WHEN 'wellsystem' THEN {$serviceTypeIdWellsystem}
            WHEN 'welltime' THEN {$serviceTypeIdWelltime}
            WHEN 'workingtable' THEN {$serviceTypeIdItpark}
            ELSE {$serviceTypeIdExtra}
          END
      ),
      tarifs_extra.currency,
      tarifs_extra.description,
      CASE tarifs_extra.status
        WHEN 'public' THEN {$statusIdPublic}
        WHEN 'special' THEN {$statusIdSpecial}
        WHEN 'archive' THEN {$statusIdArchive}
        ELSE {$statusIdTest}
      END,
      tarifs_extra.price_include_vat,
      {$personIdAll},
      {$countryIdRussia},
      1,
      0,
      0,
      0,
      user_users.id,
      tarifs_extra.edit_time,
      user_users.id,
      tarifs_extra.edit_time

  FROM tarifs_extra
  LEFT JOIN user_users
    ON tarifs_extra.edit_user = user_users.id
    ");
    }

    /**
     * Конвертировать TariffPeriod
     */
    protected function convertTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $periodIdQuarter = Period::ID_QUARTER;
        $periodIdHalfYear = Period::ID_HALFYEAR;
        $periodIdYear = Period::ID_YEAR;

        $deltaExtraTariff = Tariff::DELTA_EXTRA; // без разницы, какой именно дельта, у всех одинаковый

        $tariffPeriodTableName = TariffPeriod::tableName();

        $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)

  SELECT 0, price, 0, id + {$deltaExtraTariff}, {$periodIdMonth}, {$periodIdYear}
  FROM tarifs_extra
  WHERE period = 'once'
  ");

        $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)

  SELECT price, 0, 0, id + {$deltaExtraTariff},
      CASE period
        WHEN 'month' THEN {$periodIdMonth}
        WHEN '3mon' THEN {$periodIdQuarter}
        WHEN '6mon' THEN {$periodIdHalfYear}
        WHEN 'year' THEN {$periodIdYear}
      END,
      CASE period
        WHEN 'month' THEN {$periodIdMonth}
        WHEN '3mon' THEN {$periodIdQuarter}
        WHEN '6mon' THEN {$periodIdHalfYear}
        WHEN 'year' THEN {$periodIdYear}
      END
  FROM tarifs_extra
  WHERE period != 'once'
  ");
    }
}