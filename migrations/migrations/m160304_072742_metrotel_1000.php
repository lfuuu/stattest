<?php

use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\LogTarif;
use app\helpers\DateTimeZoneHelper;
use app\models\usages\UsageInterface;


/*
 * !!!!!!!!!!! Разовая миграция. Будет уничтожена после первого запуска на проде. !!!!!!!!!!!!!
 */

class m160304_072742_metrotel_1000 extends \app\classes\Migration
{
    public function up()
    {
        /*
         * Массовое изменние тарифа с 1 апреля.
         */
        $transaction = Yii::$app->getDb()->beginTransaction();

        $now = new \DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));
        foreach([
                    36166 => 640, //Краснодар (97)
                    36252 => 653, //Казань (93)
                    36253 => 667, //Самара (96)
                    36254 => 660, //Ростов на Дону (87)
                    36917 => 681, //Нижний Новгород (88)
                    36918 => 646  //Екатеринбург (95)
                ] as $accountId => $newTariffId) {
            $client = ClientAccount::findOne(['id' => $accountId]);

            if (!$client) {
                $transaction->rollBack();
                return true;
            }

            foreach(UsageVoip::find()->client($client->client)->actual()->all() as $usage) {

                echo "\n  > number: ".$usage->E164 . ', account: ' . $client->id;
                if ($usage->getLogTariff(UsageInterface::MAX_POSSIBLE_DATE)->id_tarif != $newTariffId) {
                    echo " (+)";
                    $newLogTariff = new LogTarif;
                    $newLogTariff->setAttributes($usage->logTariff->attributes, false);

                    $newLogTariff->id = null;
                    $newLogTariff->id_tarif = $newTariffId;
                    $newLogTariff->ts = $now->format(DateTime::ATOM);
                    $newLogTariff->date_activation = '2016-04-01';
                    $newLogTariff->id_user = 48;
                    $newLogTariff->save();
                }
            }
        }

        $transaction->commit();

        return true;
    }

    public function down()
    {
        echo "m160304_072742_metrotel_1000 cannot be reverted.\n";

        return false;
    }
}