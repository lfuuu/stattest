<?php
namespace app\dao;

use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\BillDocument;
use app\models\ClientAccount;

/**
 * Class BillDocumentDao
 *
 * @method static BillDocumentDao me($args = null)
 */
class BillDocumentDao extends Singleton
{
    /**
     * Получение доступных документов по номеру счета
     *
     * @param string $billNo
     * @return array|bool
     */
    public function getByBillNo($billNo)
    {
        $docs = BillDocument::findOne($billNo);

        if (!$docs) {
            return $this->updateByBillNo($billNo, null, true);
        }

        return $docs->toArray();
    }

    /**
     * Пересчитать сохраненные документы
     *
     * @param string $billNo
     * @param null $L
     * @param bool $returnData
     * @return array|bool
     */
    public function updateByBillNo($billNo, $L = null, $returnData = false)
    {
        $bill = new \Bill($billNo);

        $taxRate = $bill->Client()->getTaxRate();

        $accountId = $bill->Get('client_id');

        if (!$L) {
            $L = $bill->GetLines();
        }

        $billTs = get_inv_date_period($bill->GetTs());

        $p1 = \m_newaccounts::do_print_prepare_filter('invoice', 1, $L, $billTs);
        $a1 = \m_newaccounts::do_print_prepare_filter('akt', 1, $L, $billTs);

        $p2 = \m_newaccounts::do_print_prepare_filter('invoice', 2, $L, $billTs);
        $a2 = \m_newaccounts::do_print_prepare_filter('akt', 2, $L, $billTs);

        $p3 = \m_newaccounts::do_print_prepare_filter('invoice', 3, $L, $billTs, true, true);
        $a3 = \m_newaccounts::do_print_prepare_filter('akt', 3, $L, $billTs);

        $p4 = \m_newaccounts::do_print_prepare_filter('lading', 1, $L, $billTs);
        $p5 = \m_newaccounts::do_print_prepare_filter('invoice', 4, $L, $billTs);

        $gds = \m_newaccounts::do_print_prepare_filter('gds', 3, $L, $billTs);

        $bill_akts = array(
            1 => $a1,
            2 => $a2,
            3 => $a3
        );

        $bill_invoices = array(
            1 => $p1,
            2 => $p2,
            3 => $p3,
            4 => $p4,
            5 => ($p5 == -1 || $p5 == 0) ? $p5 : $p5,
            6 => 0,
            7 => $gds
        );

        $bill_invoice_akts = array(
            1 => $p1,
            2 => $p2
        );

        $doctypes = [
            'a1' => 0, 'a2' => 0, 'a3' => 0,
            'i1' => 0, 'i2' => 0, 'i3' => 0, 'i4' => 0, 'i5' => 0, 'i6' => 0, 'i7' => 0,
            'ia1' => 0, 'ia2' => 0
        ];
        for ($i = 1; $i <= 3; $i++) {
            $doctypes['a' . $i] = (int)$this->_isSF($accountId, $billNo, 'akt', $bill_akts[$i]);
        }

        for ($i = 1; $i <= 7; $i++) {
            $doctypes['i' . $i] = (int)$this->_isSF($accountId, $billNo, 'inv', $bill_invoices[$i]);
        }

        for ($i = 1; $i <= 2; $i++) {
            $v = $this->_isSF($accountId, $billNo, 'upd', $bill_invoice_akts[$i]);
            $doctypes['ia' . $i] = $v === null ? 0 : (int)!$v;
        }

        $docs = BillDocument::findOne($billNo);
        if (!$docs) {
            $docs = new BillDocument();
            $docs->bill_no = $billNo;
        }

        $data['bill_no'] = $billNo;
        $docs->ts = date(DateTimeZoneHelper::DATETIME_FORMAT);
        $docs->setAttributes($doctypes, false);
        $docs->save();

        return ($returnData) ? $docs->toArray() : true;
    }

    /**
     * Доступна ли счет/фактура
     *
     * @param integer $accountId
     * @param string  $billNo
     * @param string  $type
     * @param array   $L
     * @return bool|null
     */
    private function _isSF($accountId, $billNo, $type, $L)
    {
        static $cache = [];

        if (!$L) {
            return null;
        }

        $l = reset($L);
        $ts = $l['ts_from'];

        if (!isset($cache[$billNo]) || !isset($cache[$billNo][$ts])) {

            /** @var ClientAccount $account */
            $account = ClientAccount::findOne(['id' => $accountId])
                ->loadVersionOnDate($l['date_from']);

            $cache[$billNo][$ts] = $account->getTaxRate();
        }

        $taxRate = $cache[$billNo][$ts];

        if (!$taxRate) {
            if ($type != "akt") { // в упрощенке только акты
                return null;
            }

            return true; // если мы здесь, значит в документе должен быть доступен
        }

        // далее отработка ЛС с основной системой налогооблажения (ОСН)
        $period1 = strtotime("2014-07-01"); // переход на УПД
        $period2 = strtotime("2017-01-01"); // возврат на с/ф и акт


        if ($ts >= $period2) {
            return true;
        } else if ($ts >= $period1) {
            return false;
        } else {
            return true;
        }

    }

}
