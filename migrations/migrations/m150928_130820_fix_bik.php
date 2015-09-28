<?php

use app\models\Bik;
use yii\helpers\Json;

class m150928_130820_fix_bik extends \app\classes\Migration
{
    public function up()
    {
        $history = Yii::$app->getDb()->createCommand("
            SELECT * FROM `history_version` WHERE `data_json` REGEXP '\"bik\":(\"[^0][0-9]+\"|[0-9]+),' AND `date` > CAST('2015-07-01' AS date);
        ")->queryAll();

        foreach ($history as $record) {
            $data = Json::decode($record['data_json']);

            if (!($bik = Bik::findOne(['bik' => '0' . $data['bik']])) instanceof Bik)
                continue;

            print_r($bik);
            $data['bik'] = $bik->bik;
            $data['bank_name'] = $bik->bank_name;
            $data['bank_city'] = $bik->bank_city;
            $data['corr_acc'] = $bik->corr_acc;

            $this->execute("
                UPDATE `history_version`
                SET
                    `data_json` = :dataJson
                WHERE
                  `model` = :model
                  AND `model_id` = :model_id
            ", [
                ':model' => $record['model'],
                ':model_id' => $record['model_id'],
                ':dataJson' => Json::encode($data),
            ]);
        }
    }

    public function down()
    {
        echo "m150928_130820_fix_bik cannot be reverted.\n";

        return true;
    }
}