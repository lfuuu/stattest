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
use app\models\User;

class m160114_150954_create_vpbx_tariff extends \app\classes\Migration
{
    public function safeUp()
    {
        $this->createServiceType();
        $this->createTariffPerson();
        $this->createTariffStatus();
        $this->createPeriod();
        $this->createTariff();
        $this->createTariffPeriod();
        $this->createResource();
        $this->createTariffResource();
    }

    public function safeDown()
    {
        $this->dropTable(TariffResource::tableName());
        $this->dropTable(Resource::tableName());
        $this->dropTable(TariffPeriod::tableName());
        $this->dropTable(Tariff::tableName());
        $this->dropTable(Period::tableName());
        $this->dropTable(TariffStatus::tableName());
        $this->dropTable(TariffPerson::tableName());
        $this->dropTable(ServiceType::tableName());
    }

    /**
     * Создать таблицу Period
     */
    protected function createPeriod()
    {
        $tableName = Period::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // текст
            'name' => $this->string()->notNull(),
            //число
            'dayscount' => $this->integer()->notNull()->defaultValue(0),
            'monthscount' => $this->integer()->notNull()->defaultValue(0),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->insert($tableName, [
            'id' => Period::ID_DAY,
            'name' => 'День',
            'dayscount' => 1,
            'monthscount' => 0,
        ]);
        $this->insert($tableName, [
            'id' => Period::ID_MONTH,
            'name' => 'Месяц',
            'dayscount' => 0,
            'monthscount' => 1,
        ]);
        $this->insert($tableName, [
            'id' => Period::ID_QUARTER,
            'name' => 'Квартал',
            'dayscount' => 0,
            'monthscount' => 3,
        ]);
        $this->insert($tableName, [
            'id' => Period::ID_HALFYEAR,
            'name' => 'Полгода',
            'dayscount' => 0,
            'monthscount' => 6,
        ]);
        $this->insert($tableName, [
            'id' => Period::ID_YEAR,
            'name' => 'Год',
            'dayscount' => 0,
            'monthscount' => 12,
        ]);
    }

    /**
     * Создать таблицу TariffStatus
     */
    protected function createTariffStatus()
    {
        $tableName = TariffStatus::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // текст
            'name' => $this->string()->notNull(),
            // fk
            'service_type_id' => $this->integer(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $fieldName = 'service_type_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ServiceType::tableName(),
            'id', 'RESTRICT');

        $this->insert($tableName, [
            'id' => TariffStatus::ID_PUBLIC,
            'name' => 'Публичный',
        ]);
        $this->insert($tableName, [
            'id' => TariffStatus::ID_ARCHIVE,
            'name' => 'Архивный',
        ]);
        $this->insert($tableName, [
            'id' => TariffStatus::ID_SPECIAL,
            'name' => 'Специальный',
        ]);
        $this->insert($tableName, [
            'id' => TariffStatus::ID_TEST,
            'name' => 'Тестовый',
        ]);
    }

    /**
     * Создать таблицу TariffPerson
     */
    protected function createTariffPerson()
    {
        $tableName = TariffPerson::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // текст
            'name' => $this->string()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->insert($tableName, [
            'id' => TariffPerson::ID_ALL,
            'name' => 'Для всех',
        ]);

        $this->insert($tableName, [
            'id' => TariffPerson::ID_LEGAL_PERSON,
            'name' => 'Только для юр. лиц',
        ]);

        $this->insert($tableName, [
            'id' => TariffPerson::ID_NATURAL_PERSON,
            'name' => 'Только для физ. лиц',
        ]);
    }

    /**
     * Создать таблицу ServiceType
     */
    protected function createServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // текст
            'name' => $this->string()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->insert($tableName, [
            'id' => ServiceType::ID_VPBX,
            'name' => 'ВАТС',
        ]);
    }

    /**
     * Создать таблицу Tariff
     */
    protected function createTariff()
    {
        $tableName = Tariff::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // текст
            'name' => $this->string()->notNull(),

            //число
            'period_days' => $this->integer()->notNull()->defaultValue(0),
            'n_prolongation_periods' => $this->integer()->notNull()->defaultValue(0),
            'count_of_validity_period' => $this->integer()->notNull()->defaultValue(0),

            // bool
            'is_autoprolongation' => $this->integer()->notNull(),
            'is_charge_after_period' => $this->integer()->notNull(),
            'is_charge_after_blocking' => $this->integer()->notNull(),
            'is_include_vat' => $this->integer()->notNull(),

            // fk
            'currency_id' => 'char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT "RUB"',
            'service_type_id' => $this->integer()->notNull(),
            'tariff_status_id' => $this->integer()->notNull(),
            'country_id' => $this->integer()->notNull(),
            'tariff_person_id' => $this->integer()->notNull(),

            'insert_time' => $this->timestamp()->notNull(), // dateTime
            'insert_user_id' => $this->integer(),
            'update_time' => $this->dateTime(),
            'update_user_id' => $this->integer(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

//        $this->alterColumn('currency', 'id', $this->string(3)->notNull());

        $fieldName = 'currency_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Currency::tableName(), 'id',
            'RESTRICT');

        $fieldName = 'service_type_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ServiceType::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'tariff_status_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffStatus::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'country_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Country::tableName(),
            'code', 'RESTRICT');

        $fieldName = 'tariff_person_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffPerson::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'insert_user_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, User::tableName(), 'id',
            'SET NULL');

        $fieldName = 'update_user_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, User::tableName(), 'id',
            'SET NULL');
    }

    /**
     * Создать таблицу TariffPeriod
     */
    protected function createTariffPeriod()
    {
        $tableName = TariffPeriod::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // float
            'price_per_period' => $this->float()->notNull()->defaultValue(0.0),
            'price_setup' => $this->float()->notNull()->defaultValue(0.0),
            'price_min' => $this->float()->notNull()->defaultValue(0.0),
            // fk
            'tariff_id' => $this->integer()->notNull(),
            'period_id' => $this->integer()->notNull(),
            'charge_period_id' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $fieldName = 'tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Tariff::tableName(), 'id',
            'CASCADE');

        $fieldName = 'period_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Period::tableName(), 'id',
            'RESTRICT');

        $fieldName = 'charge_period_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Period::tableName(), 'id',
            'RESTRICT');
    }

    /**
     * Создать таблицу Resource
     */
    protected function createResource()
    {
        $tableName = Resource::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // текст
            'name' => $this->string()->notNull(),
            'unit' => $this->string()->notNull(),

            // float
            'min_value' => $this->float()->notNull()->defaultValue(0.0),
            'max_value' => $this->float(),

            // fk
            'service_type_id' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $fieldName = 'service_type_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ServiceType::tableName(),
            'id', 'RESTRICT');

        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_DISK,
            'name' => 'Дисковое пространство',
            'unit' => 'Гб',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_ABONENT,
            'name' => 'Абоненты',
            'unit' => 'шт.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_EXT_DID,
            'name' => 'Подключение номера другого оператора',
            'unit' => 'шт.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_RECORD,
            'name' => 'Запись звонков с сайта',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_WEB_CALL,
            'name' => 'Звонки с сайта',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_FAX,
            'name' => 'Факс',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);
    }

    /**
     * Создать таблицу TariffResource
     */
    protected function createTariffResource()
    {
        $tableName = TariffResource::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // float
            'amount' => $this->float()->notNull()->defaultValue(0.0),
            'price_per_unit' => $this->float()->notNull()->defaultValue(0.0),
            'price_min' => $this->float()->notNull()->defaultValue(0.0),

            // fk
            'resource_id' => $this->integer()->notNull(),
            'tariff_id' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $fieldName = 'resource_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Resource::tableName(), 'id',
            'RESTRICT');

        $fieldName = 'tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Tariff::tableName(), 'id',
            'CASCADE');

        $this->createIndex('u-' . $tableName . '-resource_id-tariff_id', $tableName, ['resource_id', 'tariff_id']);
    }
}