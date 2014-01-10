<?php

//--------------------------------------------------------------------------------------------------
define("PATH_TO_ROOT",'../');
include PATH_TO_ROOT."conf.php";
include PATH_TO_ROOT."include/MyDBG.php";
include PATH_TO_ROOT."include/bill.php";
include PATH_TO_ROOT."modules/newaccounts/module.php";
//--------------------------------------------------------------------------------------------------

$bills = $db->AllRecords('select bill_no from newbills');
$new_cnt = 0;
$all_cnt = count($bills);
foreach ($bills as $bill_no) {
    $bill = new Bill($bill_no['bill_no']);
    if (($doctypes = $bill->getBill2Doctypes()) == false) {
        $L = $bill->GetLines();
        $period_date = get_inv_date_period($bill->GetTs());
        
        $p1 = m_newaccounts::do_print_prepare_filter('invoice',1,$L,$period_date);
        $a1 = m_newaccounts::do_print_prepare_filter('akt',1,$L,$period_date);
        
        $p2 = m_newaccounts::do_print_prepare_filter('invoice',2,$L,$period_date);
        $a2 = m_newaccounts::do_print_prepare_filter('akt',2,$L,$period_date);
        
        $p3 = m_newaccounts::do_print_prepare_filter('invoice',3,$L,$period_date,true,true);
        $a3 = m_newaccounts::do_print_prepare_filter('akt',3,$L,$period_date);
        
        $p4 = m_newaccounts::do_print_prepare_filter('lading',1,$L,$period_date);
        $p5 = m_newaccounts::do_print_prepare_filter('invoice',4,$L,$period_date);
        
        $p6 = m_newaccounts::do_print_prepare_filter('invoice',5,$L,$period_date);
        
        $gds = m_newaccounts::do_print_prepare_filter('gds',3,$L,$period_date);
        
        $bill_akts = array(
                        1=>count($a1),
                        2=>count($a2),
                        3=>count($a3)
        );
        
        $bill_invoices = array(
                        1=>count($p1),
                        2=>count($p2),
                        3=>count($p3),
                        4=>count($p4),
                        5=>($p5==-1 || $p5 == 0)?$p5:count($p5),
                        6=>count($p6),
                        7=>count($gds)
        );
        
        $doctypes = array();
        for ($i=1;$i<=3;$i++) $doctypes['a'.$i] = $bill_akts[$i];
        for ($i=1;$i<=7;$i++) $doctypes['i'.$i] = $bill_invoices[$i];
        
        $bill->setBill2Doctypes($doctypes);
        
        $new_cnt++;
    }
}

print 'All - '.$all_cnt.'; New - '.$new_cnt.'<br>';
print 'Done';

?>
