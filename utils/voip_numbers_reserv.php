<?php 
define("PATH_TO_ROOT",'../');
include PATH_TO_ROOT."conf.php";
include PATH_TO_ROOT."include/MyDBG.php";


try {

    $tarifs = getDefaultTarifs();

    $reserved_numbers = $db->AllRecords("
                select 
                    number, client_id, region
                from 
                    `voip_numbers`
                where 
                    `usage_id` is null and 
                   client_id is not null and 
                   client_id <> 764 
            ",null,MYSQL_ASSOC);
    $i=0;
    $err_numbers = array();
    foreach ($reserved_numbers as $r) {

        $u = $db->GetRow("select * from usage_voip where    (cast(now() as date) between actual_from and actual_to or  (actual_from = '2029-01-01' and actual_to = '2029-01-01')) and E164 = '".$r["number"]."'");

        echo "\nNumber: ".$r["number"]." ";

        if ($u) {echo "(-)"; continue;}

        echo "(+)";
        

        $client = $db->GetRow("select client from clients where id='".$r['client_id']."'");
        if ($client) {
            //Создаем запись услуги
            $usageVoipId = $db->QueryInsert("usage_voip", array(
                            "client"        => $client["client"],
                            "region"        => $r['region'],
                            "E164"          => $r['number'],
                            "no_of_lines"   => '1',
                            "actual_from"   => "2029-01-01",
                            "actual_to"     => "2029-01-01",
                            "status"        => "connecting"
                            )
                        );
            //Создаем запись тарифов
            $db->QueryInsert("log_tarif", array(
                    "service"             => "usage_voip",
                    "id_service"          => $usageVoipId,
                    "id_tarif"            => $tarifs[$r['region']]['id_tarif'],
                    "id_tarif_local_mob"  => $tarifs[$r['region']]['id_tarif_local_mob'],
                    "id_tarif_russia"     => $tarifs[$r['region']]['id_tarif_russia'],
                    "id_tarif_intern"     => $tarifs[$r['region']]['id_tarif_intern'],
                    "id_tarif_sng"        => $tarifs[$r['region']]['id_tarif_sng'],
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
            //Освобождаем номер
            $db->Query($q = "
                    update
                        `voip_numbers`
                    set
                        `client_id`=NULL
                    where usage_id is null and
                        `number` = '".$r['number']."'
                ");
                
            $i++;
        } else $err_numbers[] = $r['number'];
    }
    echo 'Add: '.$i."\n";
    if (count($err_numbers) > 0) {
        echo "client not defined:\n";
        foreach ($err_numbers as $n)
            echo $n."\n";
    }
}catch(Exception $e)
{
    echo "\nError: ".$e->GetMessage();
    mail("adima123@yandex.ru", "unreserv voip numbers", $e->GetMessage());
}


function getDefaultTarifs()
{
    global $db;
    $res = array('99'=>'156','97'=>'60','98'=>'112','96'=>'0','82'=>'0','95'=>'195','94'=>'178','87'=>'240','88'=>'278','93'=>'0');

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
                                    currency='RUR'
                                " . (($region_id == '99') ? "AND name LIKE('%".Encoding::toKOI8R('Базовый')."%')" : '')
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
?>>
