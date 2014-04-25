<?php

//--------------------------------------------------------------------------------------------------
define("PATH_TO_ROOT",'../');
include PATH_TO_ROOT."conf.php";
include PATH_TO_ROOT."include/bill.php";
include PATH_TO_ROOT."modules/newaccounts/module.php";
//--------------------------------------------------------------------------------------------------

global $db;

print "Starting: " . date('Y-m-d H:i') . "\n";
$bills = $db->AllRecords('select bill_no from newbills');
$new_cnt = $curr = 0;
$all_cnt = count($bills);
foreach ($bills as $bill_no) {
    $curr++;
    if (($curr % 100) == 0) print 'Processed: '.number_format(100*$curr/$all_cnt, 2) . "%\r"; 
    $Bill = new Bill($bill_no['bill_no']);
    if (!$Bill->checkBill2Doctypes()) {
        $Bill->updateBill2Doctypes(null, false);
        $new_cnt++;
    }
    unset($Bill);
}

print "\nAll - " . $all_cnt . '; New - ' . $new_cnt . "\n";

print "Done: " . date('Y-m-d H:i') . "\n";

?>
