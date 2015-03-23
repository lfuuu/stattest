<?php 

class VoipReservNumber
{
    public static function reserv($number, $clientId, $lineCount = 1, $tarifId = null)
    {
        global $db;

        $number = $db->escape(trim($number));

        if ($tarifId !== null)
            $tarifId = $db->escape($tarifId);

        $region = $db->GetValue("select region from voip_numbers where number = '".$number."'");

        if (!$region)
            throw new Exception("Номер не найден");


        $u = $db->GetValue("select id from usage_voip where 
                (  
                 cast(now() as date) between actual_from and actual_to 
                 or (actual_from = '2029-01-01' and actual_to = '2029-01-01')
                ) and E164 = '".$number."'");

        if ($u)
            throw new Exception("Номер уже используется");


        $client = $db->GetValue("select client from clients where id='".$clientId."'");

        if (!$client)
            throw new Exception("Клиент не найден");

        $tarifs = self::getDefaultTarifs();

        if ($tarifId === null)
            $tarifId = $tarifs[$region]['id_tarif'];
        
        $tarif = $db->GetRow("select * from tarifs_voip where id = '".$tarifId."' and region = '".$region."'");

        if (!$tarif)
            throw new Exception("Тариф не найден");


        //Создаем запись услуги
        $usageVoipId = $db->QueryInsert("usage_voip", array(
                    "client"        => $client,
                    "region"        => $region,
                    "E164"          => $number,
                    "no_of_lines"   => $lineCount,
                    "actual_from"   => "2029-01-01",
                    "actual_to"     => "2029-01-01",
                    "status"        => "connecting"
                    )
                );

        //Создаем запись тарифов
        $db->QueryInsert("log_tarif", array(
                    "service"             => "usage_voip",
                    "id_service"          => $usageVoipId,
                    "id_tarif"            => $tarifId,
                    "id_tarif_local_mob"  => $tarifs[$region]['id_tarif_local_mob'],
                    "id_tarif_russia"     => $tarifs[$region]['id_tarif_russia'],
                    "id_tarif_intern"     => $tarifs[$region]['id_tarif_intern'],
                    "id_tarif_sng"        => $tarifs[$region]['id_tarif_sng'],
                    "ts"                  => array("NOW()"),
                    "date_activation"     => array("NOW()"),
                    "dest_group"          => '0',
                    "minpayment_group"    => '0',
                    "minpayment_local_mob"=> '0',
                    "minpayment_russia"   => '0',
                    "minpayment_intern"   => '0',
                    "minpayment_sng"      => '0'
                    )
                );

        return array("tarif" => $tarif, "usage_id" => $usageVoipId);

    }

    private static function getDefaultTarifs()
    {
        global $db;
        $res = array('99'=>'156','97'=>'60','98'=>'112','96'=>'412','82'=>'0','95'=>'195','94'=>'178','87'=>'240','88'=>'278','89'=>314,'93'=>'302');

        foreach ($res as $region_id=>$v) {
            $def = array(
                    'id_tarif_local_mob'=>0,
                    'id_tarif_russia'=>0,
                    'id_tarif_intern'=>0,
                    'id_tarif_sng'=>0,
                    'id_tarif'=>$v
                    );

            $tarifs = $db->AllRecords($q = "
                    select
                        id, dest
                    from
                        tarifs_voip
                    where
                        status='public' and
                        region='".$region_id."' and
                        currency='RUB'
                    " . (($region_id == '99') ? "AND name LIKE('%Базовый%')" : '')
                    );
            foreach ($tarifs as $r) {
                switch ($r['dest']) {
                    case '1':
                        $def['id_tarif_russia'] = $r['id'];break;
                    case '2':
                        $def['id_tarif_intern'] = $r['id'];break;
                    case '3':
                        $def['id_tarif_sng'] = $r['id'];break;
                    case '5':
                        $def['id_tarif_local_mob'] = $r['id'];break;
                }
            }
            $res[$region_id] = $def;
        }
        return $res;
    }
}
