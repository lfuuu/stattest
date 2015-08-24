<?php 

use app\classes\Event;
use app\models\UsageVoip;
use app\models\ClientAccount;
use app\models\TariffVoip;
use app\models\TariffNumber;
use app\forms\usage\UsageVoipEditForm;

class VoipReservNumber
{
    public static function reserv($number, $clientId, $lineCount = 1, $tarifId = null, $isForceStart = false)
    {
        global $db;

        $number = $db->escape(trim($number));

        if ($tarifId !== null)
            $tarifId = $db->escape($tarifId);

        $voipNumber = $db->GetRow("select region,city_id,did_group_id from voip_numbers where number = '".$number."'");

        if (!$voipNumber)
            throw new Exception("Номер не найден");

        $region = $voipNumber["region"];



        $u = $db->GetValue("select id from usage_voip where 
                (  
                 cast(now() as date) between actual_from and actual_to 
                 or (actual_from > '3000-01-01' and actual_to > '3000-01-01')
                ) and E164 = '".$number."'");

        if ($u)
            throw new Exception("Номер уже используется");


        $client = ClientAccount::findOne(["id" => $clientId]);

        if (!$client)
            throw new Exception("Клиент не найден");

        $tarif = self::getDefaultTarif($region, $client->currency);

        if ($tarifId === null)
            $tarifId = $tarif['id_tarif'];
        
        $tarif = $db->GetRow("select * from tarifs_voip where id = '".$tarifId."' and connection_point_id = '".$region."'");

        if (!$tarif)
            throw new Exception("Тариф не найден");



        $transaction = Yii::$app->db->beginTransaction();
        try {
            //Создаем запись услуги
            $form = new UsageVoipEditForm;
            $form->scenario = 'add';
            $form->initModel($client);

            $form->tariff_main_id = $tarifId;
            $form->connecting_date = ($isForceStart ? date("Y-m-d") : "4000-01-01");
            $form->did = $number;
            $form->no_of_lines = $lineCount;

            $form->prepareAdd();

            if (!$form->validate() || !$form->add()) 
            {
                if ($form->errors)
                {
                    \Yii::error($form);
                    $errorKeys = array_keys($form->errors);
                    throw new Exception($form->errors[$errorKeys[0]], 500);
                } else {
                    throw new Exception("Unknown error", 500);
                }
            }

            $usageVoipId = $form->id;

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return array("tarif" => $tarif, "usage_id" => $usageVoipId);

    }

    private static function getDefaultTarif($regionId, $currency)
    {
        global $db;


        $def = array(
            'id_tarif_local_mob'=>0,
            'id_tarif_russia'=>0,
            'id_tarif_intern'=>0,
            'id_tarif'=>0
        );

        $def["id_tarif"] = $db->GetValue("select id from tarifs_voip where status='test' and connection_point_id = '".$regionId."' and currency_id='".$currency."'");

        $tarifs = $db->AllRecords($q = "
            select
                id, dest
            from
                tarifs_voip
            where
                status='public' and
                connection_point_id='".$regionId."' and
                currency_id='".$currency."'
            " . (($regionId == '99') ? "AND name LIKE('%Базовый%')" : '')
        );
        foreach ($tarifs as $r) {
            switch ($r['dest']) {
                case '1':
                    $def['id_tarif_russia'] = $r['id'];break;
                case '2':
                    $def['id_tarif_intern'] = $r['id'];break;
                case '5':
                    $def['id_tarif_local_mob'] = $r['id'];break;
            }
        }

        foreach($def as $v)
        {
            if (!$v)
            {
                if (defined("ADMIN_EMAIL") && ADMIN_EMAIL)
                {
                    mail(ADMIN_EMAIL, "VoipReservNumber", "Тариф не установлен. region: ".$regionId. ", currency: ".$currency);
                }

                throw new Exception("Тариф не установлен");
            }
        }


        return $def;
    }
}
