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
            ServiceType::ID_WELLTIME_PRODUCT,
            ServiceType::ID_EXTRA,
        ];

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
            'id' => ServiceType::ID_WELLTIME_PRODUCT,
            'name' => 'Welltime',
        ]);

        $this->insert($tableName, [
            'id' => ServiceType::ID_EXTRA,
            'name' => 'Дополнительные услуги',
        ]);
    }
}