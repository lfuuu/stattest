<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\BillDocument;

/**
 * @method static BillDocumentDao me($args = null)
 * @property
 */
class BillDocumentDao extends Singleton
{
    public function getByBillNo($billNo)
    {
        $docs = BillDocument::findOne($billNo);

        if (!$docs) {
            return $this->updateByBillNo($billNo, null, true);
        }

        return $docs->toArray();
    }

    public function updateByBillNo($billNo, $L = null, $returnData = false)
    {
        $bill = new \Bill($billNo);

        if(!$L) {
            $L = $bill->GetLines();
        }

        $period_date = get_inv_date_period($bill->GetTs());

        $p1 = \m_newaccounts::do_print_prepare_filter('invoice',1,$L,$period_date);
        $a1 = \m_newaccounts::do_print_prepare_filter('akt',1,$L,$period_date);

        $p2 = \m_newaccounts::do_print_prepare_filter('invoice',2,$L,$period_date);
        $a2 = \m_newaccounts::do_print_prepare_filter('akt',2,$L,$period_date);

        $p3 = \m_newaccounts::do_print_prepare_filter('invoice',3,$L,$period_date,true,true);
        $a3 = \m_newaccounts::do_print_prepare_filter('akt',3,$L,$period_date);

        $p4 = \m_newaccounts::do_print_prepare_filter('lading',1,$L,$period_date);
        $p5 = \m_newaccounts::do_print_prepare_filter('invoice',4,$L,$period_date);

        $gds = \m_newaccounts::do_print_prepare_filter('gds',3,$L,$period_date);

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
            6=>0,
            7=>count($gds)
        );

        $bill_invoice_akts = array(
            1=>count($p1),
            2=>count($p2)
        );

        $doctypes = array();
        for ($i=1;$i<=3;$i++) $doctypes['a'.$i] = $bill_akts[$i];
        for ($i=1;$i<=7;$i++) $doctypes['i'.$i] = $bill_invoices[$i];
        for ($i=1;$i<=2;$i++) $doctypes['ia'.$i] = $bill_invoice_akts[$i];

        $docs = BillDocument::findOne($billNo);
        if (!$docs) {
            $docs = new BillDocument();
            $docs->bill_no = $billNo;
        }
        $data['bill_no'] = $billNo;
        $docs->ts = date('Y-m-d H:i:s');
        $docs->setAttributes($doctypes, false);
        $docs->save();

        return ($returnData) ? $docs->toArray() : true;
    }

}