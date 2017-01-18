<?php

use app\modules\notifier\models\Schemes;
use app\models\Country;
use app\models\important_events\ImportantEventsNames;

class m161219_151942_notifier_schemes extends \app\classes\Migration
{

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableName = Schemes::tableName();
        $countryTableName = Country::tableName();
        $eventNamesTableName = ImportantEventsNames::tableName();

        $this->createTable(
            $tableName,
            [
                'country_code' => $this->integer(4)->notNull(),
                'event' => $this->string(50)->notNull(),
                'do_email' => $this->integer()->defaultValue(0),
                'do_sms' => $this->integer()->defaultValue(0),
                'do_email_monitoring' => $this->integer()->defaultValue(0),
                'do_email_operator' => $this->integer()->defaultValue(0),
            ],
            'ENGINE=InnoDB CHARSET=utf8'
        );

        $this->createIndex(
            'country_code-event',
            $tableName,
            [
                'country_code',
                'event'
            ],
            $unique = true
        );

        $this->addForeignKey('fk-' . $tableName . '-country_code', $tableName, 'country_code', $countryTableName, 'code');
        $this->addForeignKey('fk-' . $tableName . '-event', $tableName, 'event', $eventNamesTableName, 'code');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(Schemes::tableName());
    }

}
