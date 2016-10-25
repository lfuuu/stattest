<?php

use app\models\ClientContactPersonal;
use app\models\ClientContactType;
use app\models\ClientContract;

class m161021_122211_personal_contact extends \app\classes\Migration
{
    public function up()
    {

        $this->createTable(ClientContactType::tableName(), [
            'id' => $this->primaryKey(),
            'code' => $this->string(),
            'name' => $this->string()
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex('idx-' . ClientContactType::tableName() . '-code', ClientContactType::tableName(), 'code');

        $types = [
            [ClientContactType::TYPE_ID_PHONE, 'phone', 'Телефон'],
            [ClientContactType::TYPE_ID_EMAIL, 'email', 'Email'],
            [ClientContactType::TYPE_ID_FAX, 'fax', 'Факс'],
            [ClientContactType::TYPE_ID_SMS, 'sms', 'СМС'],
            [ClientContactType::TYPE_ID_EMAIL_INVOICE, 'email_invoice', 'Email - Invoice'],
            [ClientContactType::TYPE_ID_EMAIL_RATE, 'email_rate', 'Email - Rate'],
            [ClientContactType::TYPE_ID_EMAIL_SUPPORT, 'email_support', 'Email - Support'],
        ];

        $this->batchInsert(ClientContactType::tableName(), ['id', 'code', 'name'], $types);

        $this->createTable(ClientContactPersonal::tableName(), [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer(),
            'create_time' => $this->dateTime(),
            'type_id' => $this->integer(),
            'contact' => $this->string()
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addForeignKey(
            'fk-' . ClientContactType::tableName() . '-id',
            ClientContactPersonal::tableName(),
            'type_id',
            ClientContactType::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . ClientContract::tableName() . '-id',
            ClientContactPersonal::tableName(),
            'contract_id',
            ClientContract::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable(ClientContactPersonal::tableName());
        $this->dropTable(ClientContactType::tableName());
    }
}