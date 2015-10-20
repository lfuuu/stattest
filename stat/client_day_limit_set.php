<?php

define('NO_WEB',1);
define('NUM',20);
define('PATH_TO_ROOT','./');
include PATH_TO_ROOT."conf_yii.php";

for($i=1,$work_days=0,$time = time();$i<=30;$i++)
{
  $time = $time - 86400;
  if (date('w',$time) >= 1 && date('w',$time) <= 5) $work_days++;
}


$counters = $pg_db->AllRecords($q ="
				select  number_service_id as usage_id, round(-sum(c.cost)) amount
				from calls_raw.calls_raw c
				where
					c.connect_time >= '".date("Y-m-d", strtotime("-1 month"))."' and 
					c.connect_time <  '".date("Y-m-d")."'
					and number_service_id is not null
				group by c.number_service_id

                ",'usage_id');

$clients = array();
$res = $db->AllRecords('
        SELECT
            DISTINCT u.id AS usage_id,
            c.id AS client_id, c.client, c.currency, c.voip_is_day_calc, c.voip_credit_limit_day
        FROM usage_voip u
        LEFT JOIN clients c ON c.client=u.client
        WHERE 
                u.actual_from < CAST(NOW() AS DATE) 
            AND u.actual_to > CAST(NOW() AS DATE) 
            AND voip_is_day_calc > 0
        ');
foreach($res as $r)
{
  if (!isset($clients[$r['client_id']]))
  {
    $clients[$r['client_id']] =
      array(
        'id'=>$r['client_id'],
        'client'=>$r['client'],
        'currency'=>$r['currency'],
        'voip_is_day_calc'=>$r['voip_is_day_calc'],
        'voip_credit_limit_day'=>$r['voip_credit_limit_day'],
        'sum'=>0
      );
  }

  if (!isset($counters[$r['usage_id']])) continue;

  $clients[$r['client_id']]['sum'] += $counters[$r['usage_id']]['amount'];
}
foreach($clients as $k=>$c)
{
  $clients[$k]['new_limit'] = intval($clients[$k]['sum']/$work_days*3);

  //TODO: 1000 это сумма в рублях. Для не рублевых клиентов сделать конвертацию по курсу
  $clients[$k]['new_limit'] = ($clients[$k]['new_limit'] > 1000 ? $clients[$k]['new_limit'] : 1000);
}

$updated = 0;
foreach($clients as $c)
{
  if ($c['new_limit'] > $c['voip_credit_limit_day'])
  {
    echo "{$c['id']} {$c['voip_credit_limit_day']} - {$c['new_limit']} \n";
    $db->Query('  update clients set voip_credit_limit_day='.$c['new_limit'].' where id='.$c['id']);
    $updated++;
  }
}
//echo "<pre>";
echo date('Y-m-d H:i:s', time())." updated: $updated\n";
//print_r($clients);


