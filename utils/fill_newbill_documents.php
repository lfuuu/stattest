<?php

//--------------------------------------------------------------------------------------------------
define("PATH_TO_ROOT",'../');
include PATH_TO_ROOT."conf.php";
include PATH_TO_ROOT."include/bill.php";
include PATH_TO_ROOT."modules/newaccounts/module.php";
//--------------------------------------------------------------------------------------------------

global $db;

$base_memory_usage = memory_get_usage();
print "Starting: " . date('Y-m-d H:i') . "\n";

$new_cnt = $curr = 0;
$all_cnt = $db->GetValue('select count(*) from newbills');

$bills = $db->AllRecords('select bill_no from newbills');
foreach($bills as $bill_no) {
    $curr++;
    if (($curr % 100) == 0) 
        print 'Processed: '.number_format(100*$curr/$all_cnt, 2) . "% Memory diff: ".(memory_get_usage()-$base_memory_usage)."\r"; 
    $Bill = new Bill($bill_no['bill_no']);
    if (!$Bill->checkBill2Doctypes()) {
        $Bill->updateBill2Doctypes(null, false);
        $new_cnt++;
    }
    unset($Bill);
}
print "\nAll - " . $all_cnt . '; New - ' . $new_cnt . "\n";

print "Done: " . date('Y-m-d H:i') . "\n";
//597347
?>
