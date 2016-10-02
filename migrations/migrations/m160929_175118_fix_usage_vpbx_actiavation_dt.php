<?php

use app\models\UsageVirtpbx;

class m160929_175118_fix_usage_vpbx_actiavation_dt extends \app\classes\Migration
{
    public function up()
    {
        //нужно пересохранение, для точго что бы запустилось поведение, проставляющее activation_dt && expire_dt
        foreach (UsageVirtpbx::find()
                     ->where(['activation_dt' => null])
                     ->andWhere(['not', ['actual_from' => '0000-00-00']])
                     ->all() as $usage) {

            $usage->comment .= ' ';
            $usage->save();

            $usage->comment = trim($usage->comment);
            $usage->save();
        }
    }

    public function down()
    {
        //nothing
    }
}