<?php

use app\models\Number;
use app\models\UsageVoip;

class m160615_112827_voip_new_status extends \app\classes\Migration
{
    public function up()
    {
        $this->renameColumn(Number::tableName(), 'status', 'status2');
        $this->addColumn(Number::tableName(), 'status',
            "enum('notsale', 'instock', 'active_tested', 'active_commercial', 'notactive_reserved', 'notactive_hold', 'released') not null default 'notsale' AFTER number");

        $transaction = $this->getDb()->beginTransaction();
        $this->update(Number::tableName(), ['status' => 'instock'], ['status2' => 'instock']);
        $this->update(Number::tableName(), ['status' => 'notsale'], ['status2' => 'notsell']);
        $this->update(Number::tableName(), ['status' => 'notactive_reserved'], ['status2' => 'reserved']);
        $this->update(Number::tableName(), ['status' => 'active_commercial'], ['status2' => 'active']);
        $this->update(Number::tableName(), ['status' => 'notactive_hold'], ['status2' => 'hold']);

        $numbers = [];
        foreach(UsageVoip::find()->actual()->andWhere(['E164' => (Number::find()->select('number')->where(['status2' => 'active']))])->all() as $usage) {
            if ($usage->tariff->status == 'test') {
                $numbers[] = $usage->E164;
            }
        }

        if ($numbers) {
            $this->update(Number::tableName(), ['status' => 'active_tested'], ['number' => $numbers]);
        }

        $transaction->commit();
    }

    public function down()
    {
        $this->dropColumn(Number::tableName(), 'status');
        $this->renameColumn(Number::tableName(), 'status2', 'status');
    }
}