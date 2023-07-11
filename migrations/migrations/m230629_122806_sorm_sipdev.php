<?php

use app\classes\Migration;
use app\modules\sorm\models\SipDevice\Reduced;
use app\modules\sorm\models\SipDevice\State;
use app\modules\sorm\models\SipDevice\StateLog;

/**
 * Class m230629_122806_sorm_sipdev
 */
class m230629_122806_sorm_sipdev extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(State::tableName(), [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer(),
            'region_id' => $this->integer(),
            'did' => $this->string(32),
            'ndc_type_id' => $this->integer(),
            'sip_login' => $this->string(64),
            'created_at' => $this->dateTime(),
        ]);

        $this->createTable(StateLog::tableName(), [
            'id' => $this->primaryKey(),
            'created_at' => $this->dateTime()->comment('transfer field value'),
            'is_add' => $this->boolean(),
            'account_id' => $this->integer(),
            'region_id' => $this->integer(),
            'did' => $this->string(32),
            'ndc_type_id' => $this->integer(),
            'sip_login' => $this->string(64),
            'insert_dt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createTable(Reduced::tableName(), [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer(),
            'region_id' => $this->integer(),
            'did' => $this->string(32),
            'service_id' => $this->integer(),
            'sip_login' => $this->string(64),
            'activate_dt' => $this->dateTime(),
            'expire_dt' => $this->dateTime(),
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(State::tableName());
        $this->dropTable(StateLog::tableName());
        $this->dropTable(Reduced::tableName());
    }
}
