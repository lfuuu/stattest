<?php

//--------------------------------------------------------------------------------------------------
define("PATH_TO_ROOT",'../');
include PATH_TO_ROOT."conf.php";
//--------------------------------------------------------------------------------------------------

global $db;

print "Starting: " . date('Y-m-d H:i') . "\n";
$vpbx = $db->AllRecords('select * from usage_virtpbx');
foreach ($vpbx as $r) {
    $db->Query('insert into log_tarif (service,id_service,id_tarif,id_user,ts,comment,date_activation,actual_from, actual_to) VALUES '.
            '("usage_virtpbx",'.$r['id'].','.intval($r['tarif_id']).',48,NOW(),"","'.addslashes($r['actual_from']).'","'.addslashes($r['actual_from']).'","'.addslashes($r['actual_to']).'")');
    
}

virtPbx::check();

print "Done: " . date('Y-m-d H:i') . "\n";

?>