<?php

use app\classes\Migration;
use app\models\dictionary\FormInfo;
use app\models\dictionary\FormInfoData;
use app\models\PaymentApiChannel;

/**
 * Class m210311_154045_payment_api_channel
 */
class m210311_154045_payment_api_channel extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(PaymentApiChannel::tableName(), [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull(),
            'access_token' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'is_active' => $this->tinyInteger()->notNull()->defaultValue(0),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->alterColumn(\app\models\Payment::tableName(), 'ecash_operator', $this->string());
        $this->alterColumn(\app\models\Payment::tableName(), 'type', "enum('bank','prov','ecash','neprov','creditnote','terminal','api') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bank'");
        $this->insert(FormInfo::tableName(), [
            'id' => 9,
            'form_url' => '/dictionary/payment-api-channel/edit',
        ]);

        $this->insert(FormInfo::tableName(), [
            'id' => 10,
            'form_url' => '/dictionary/payment-api-channel/new/',
        ]);

        $this->insert(FormInfoData::tableName(), [
            'form_id' => 9,
            'key' => 'paymentapichannel-access_token',
            'url' => '',
            'text' => 'Токен доступа к каналу. Если токен удалить - то он сгенерируется заново.'
        ]);

        $this->insert(FormInfoData::tableName(), [
            'form_id' => 9,
            'key' => 'paymentapichannel-code',
            'url' => '',
            'text' => 'Код канала. Идентификатор при добавлении через API.'
        ]);

        $this->insert(FormInfoData::tableName(), [
            'form_id' => 9,
            'key' => 'paymentapichannel-name',
            'url' => '',
            'text' => 'Название канал. Используется для отображения в списке платежей.'
        ]);

        $this->insert(FormInfoData::tableName(), [
            'form_id' => 10,
            'key' => 'paymentapichannel-access_token',
            'url' => '',
            'text' => 'Токен доступа к каналу. Если токен удалить - то он сгенерируется заново.'
        ]);

        $this->insert(FormInfoData::tableName(), [
            'form_id' => 10,
            'key' => 'paymentapichannel-code',
            'url' => '',
            'text' => 'Код канала. Идентификатор при добавлении через API.'
        ]);

        $this->insert(FormInfoData::tableName(), [
            'form_id' => 10,
            'key' => 'paymentapichannel-name',
            'url' => '',
            'text' => 'Название канал. Используется для отображения в списке платежей.'
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PaymentApiChannel::tableName());
        $this->alterColumn(\app\models\Payment::tableName(), 'ecash_operator', "enum('', 'cyberplat','paypal','yandex','sberbank','qiwi','stripe','sberOnlineMob') DEFAULT NULL");
        $this->alterColumn(\app\models\Payment::tableName(), 'type', 'enum(\'bank\',\'prov\',\'ecash\',\'neprov\',\'creditnote\',\'terminal\') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT \'bank\'');

        $this->delete(FormInfoData::tableName(), ['form_id' => [9, 10]]);
        $this->delete(FormInfo::tableName(), ['id' => [9, 10]]);
    }
}
