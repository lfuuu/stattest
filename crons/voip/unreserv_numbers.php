<?php 
define('PATH_TO_ROOT','../../');
include PATH_TO_ROOT."conf.php";

$dates  = array(
            'notify'=>array(
                'st_date'=>date('Y-m-d H:i:s', mktime(0, 0, 0, date("m")  , date("d")-27, date("Y"))),
                'en_date'=>date('Y-m-d H:i:s', mktime(23, 59, 59, date("m")  , date("d")-27, date("Y")))
                ),
            'unreserve'=>array(
                'st_date'=>false,
                'en_date'=>date('Y-m-d H:i:s', mktime(23, 59, 59, date("m")  , date("d")-31, date("Y")))
                )
            );

try {
    notifyManagers(getVoipUsageIDs($dates['notify']));
}catch(Exception $e)
{
    echo "\nError: ".$e->GetMessage();
    mail(ADMIN_EMAIL, "unreserv voip numbers", $e->GetMessage());
}
try {
    deleteReserv(getVoipUsageIDs($dates['unreserve']));
}catch(Exception $e)
{
    echo "\nError: ".$e->GetMessage();
    mail(ADMIN_EMAIL, "unreserv voip numbers", $e->GetMessage());
}

exit;

//----------------------------------------------------------------------------------
function getVoipUsageIDs($dates = array())
{
    global $db;

    $res = array();

    $where = '';
    if ($dates['st_date']) $where .= " AND ts >= '".$dates['st_date']."' ";
    if ($dates['en_date']) $where .= " AND ts <= '".$dates['en_date']."' ";

    foreach($db->AllRecords($q = "
        SELECT 
            client_id, 
            client, 
            manager, 
            company, 
            GROUP_CONCAT(u_id) AS u_ids, 
            GROUP_CONCAT(E164) as E164s 
        FROM (
            SELECT 
                max(lt.ts) as ts, 
                uv.id AS u_id, 
                uv.client, 
                c.manager, 
                c.id as client_id,
                c.company, 
                uv.E164
            FROM 
                log_tarif lt
            LEFT JOIN usage_voip uv ON uv.id=lt.id_service
            LEFT JOIN clients c ON c.client=uv.client
            WHERE
                service = 'usage_voip' AND
                uv.status = 'connecting' AND 
                uv.actual_from = '2029-01-01' AND
                uv.actual_to = '2029-01-01'
            GROUP BY uv.id
            HAVING
                1 ".$where."
        ) a 
        GROUP BY client_id
        ") as $c) 
        {

            $res[$c["client_id"]] = array(
                    'client'=>$c['client'],
                    'manager'=>$c['manager'],
                    'company'=>$c['company'], 
                    'u_ids'=> explode(",", $c['u_ids']),
                    'E164s'=> explode(",", $c['E164s'])
                    );

        }

    return $res;
}

//----------------------------------------------------------------------------------
function deleteReserv($data = array())
{
    global $db;
    if (count($data) == 0) return;

    $u_ids = array();
    foreach ($data as $d) 
    {
        foreach ($d["u_ids"] as $uId)
        {
            $u_ids[] = $uId;
        }
    }

    $db->Query('DELETE FROM log_tarif WHERE id_service IN ('.implode(',',$u_ids).')');
    $db->Query('DELETE FROM usage_voip WHERE id IN ('.implode(',',$u_ids).')');

    return;
}
//----------------------------------------------------------------------------------
function notifyManagers($data = array())
{
    global $db;
    if (count($data) == 0) return;

    foreach($data as $clientId => $r) {
        $message = "Снятие номера с резерва произойдет через 3 дня\n";
        $message .= 'Клиент: ' . $r['company'] . ' (id: '.$clientId.', '.$r['client'].')'."\n";
        $message .= 'Номер'.(count($r["E164s"]) > 1 ? 'а' : '').': ' . implode(", ", $r['E164s']) . "\n";

        echo "\n\n".$message;

        if (!strlen($r['manager'])) $r['manager'] = 'ava';

        ApiLk::createTT($message, $r['client'], $r['manager']);
    }
}
//----------------------------------------------------------------------------------

?>
