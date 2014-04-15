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
    mail("adima123@yandex.ru", "unreserv voip numbers", $e->GetMessage());
}
try {
    deleteReserv(getVoipUsageIDs($dates['unreserve']));
}catch(Exception $e)
{
    echo "\nError: ".$e->GetMessage();
    mail("adima123@yandex.ru", "unreserv voip numbers", $e->GetMessage());
}

exit;

//----------------------------------------------------------------------------------
function getVoipUsageIDs($dates = array())
{
    global $db;

    $res = array();

    $where = '';
    if ($dates['st_date']) $where .= " AND lt.ts >= '".$dates['st_date']."' ";
    if ($dates['en_date']) $where .= " AND lt.ts <= '".$dates['en_date']."' ";

    foreach($db->AllRecords($q = "
        SELECT 
            max(lt.ts) as ts, uv.id AS u_id, uv.client, c.manager, c.company, uv.E164
        FROM 
            log_tarif lt
        LEFT JOIN usage_voip uv ON uv.id=lt.id_service
        LEFT JOIN clients c ON c.client=uv.client
        WHERE
            service = 'usage_voip' AND
            uv.status = 'connecting' AND 
            uv.actual_from = '2029-01-01' AND
            uv.actual_to = '2029-01-01'
            ".$where."
        GROUP BY uv.id
        ") as $c) {
            $res[] = array('u_id'=>$c['u_id'], 'client'=>$c['client'],'manager'=>$c['manager'],'company'=>$c['company'], 'E164'=>$c['E164']);
    }

    return $res;
}
//----------------------------------------------------------------------------------
function deleteReserv($data = array())
{
    global $db;
    if (count($data) == 0) return;

    $u_ids = array();
    foreach ($data as $d) $u_ids[] = $d['u_id'];

    $db->Query('DELETE FROM log_tarif WHERE id_service IN ('.implode(',',$u_ids).')');
    $db->Query('DELETE FROM usage_voip WHERE id IN ('.implode(',',$u_ids).')');

    return;
}
//----------------------------------------------------------------------------------
function notifyManagers($data = array())
{
    global $db;
    if (count($data) == 0) return;

    foreach($data as $r) {
        $message = "Снятие номера с резерва произойдет через 3 дня\n";
        $message .= 'Клиент: ' . $r['company'] . ' (id: '.$r['client'].')'."\n";
        $message .= 'Номер: ' . $r['E164'] . "\n";

        if (!strlen($r['manager'])) $r['manager'] = 'ava';

        ApiLk::createTT($message, $r['client'], $r['manager']);
    }
}
//----------------------------------------------------------------------------------

?>