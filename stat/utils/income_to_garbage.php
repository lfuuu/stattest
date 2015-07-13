<?php

//--------------------------------------------------------------------------------------------------
define("PATH_TO_ROOT",'../');
include PATH_TO_ROOT."conf_yii.php";

use app\classes\Event;

//--------------------------------------------------------------------------------------------------
global $db;

$client_module = new m_clients();
print "Starting: " . date('Y-m-d H:i') . "\n";

$cnt = 0;
$ids = $db->AllRecords("select id from clients where status='income'");
foreach ($ids as $r) {
    $cnt++;
    $id = $r['id'];
    $status = 'trash';
    $comment = 'Автоматическая чистка';
    $cs = new ClientCS($id);
    $cs->Add($status,$comment);
    Event::go("client_set_status", $id);
    Event::go('ats2_numbers_check');
}

print "\nAll - " . $cnt . ';' . "\n";

print "Done: " . date('Y-m-d H:i') . "\n";

?>