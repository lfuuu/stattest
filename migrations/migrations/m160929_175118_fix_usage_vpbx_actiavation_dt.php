<?php

use app\models\UsageVirtpbx;

class m160929_175118_fix_usage_vpbx_actiavation_dt extends \app\classes\Migration
{
    public function up()
    {
        foreach (UsageVirtpbx::find()
                     ->where(['activation_dt' => null])
                     ->andWhere(['not', ['actual_from' => '0000-00-00']])
                     ->all() as $usage) {

            //значение и так =1. Сохранение вызовет запуск поведения, которое проставит activation_dt && expire_dt
            $usage->amount = 1;
            $usage->save();
        }
    }

    public function down()
    {
        //nothing
    }
}