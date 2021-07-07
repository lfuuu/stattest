<?php

namespace app\dao;

use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\BillDocument;
use app\models\ClientAccount;
use app\models\Country;
use app\models\Organization;

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
     * @param array $billLines
     * @param bool $returnData
     * @return array|bool
     */
    public function updateByBillNo($billNo, $billLines = null, $returnData = false)
    {
        $bill = new \Bill($billNo);

        $accountId = $bill->Get('client_id');

        if (!$billLines) {
            $billLines = $bill->GetLines();
        }

        $billTs = get_inv_date_period($bill->GetTs());

        $p1 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_INVOICE, 1, $billLines, $billTs);
        $a1 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_AKT, 1, $billLines, $billTs);

        $p2 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_INVOICE, 2, $billLines, $billTs);
        $a2 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_AKT, 2, $billLines, $billTs);

        $p3 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_INVOICE, 3, $billLines, $billTs, true, true);
        $a3 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_AKT, 3, $billLines, $billTs);

        $p4 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_LADING, 1, $billLines, $billTs);
        $p5 = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_INVOICE, 4, $billLines, $billTs);

        $gds = \m_newaccounts::do_print_prepare_filter(BillDocument::TYPE_GDS, 3, $billLines, $billTs);

        $bill_akts = [
            1 => $a1,
            2 => $a2,
            3 => $a3
        ];

        $bill_invoices = [
            1 => $p1,
            2 => $p2,
            3 => $p3,
            4 => $p4,
            5 => ($p5 == -1 || $p5 == 0) ? $p5 : $p5,
            6 => 0,
            7 => $gds
        ];

        $bill_invoice_akts = [
            1 => $p1,
            2 => $p2
        ];

        $doctypes = [
            'a1' => 0, 'a2' => 0, 'a3' => 0,
            'i1' => 0, 'i2' => 0, 'i3' => 0, 'i4' => 0, 'i5' => 0, 'i6' => 0, 'i7' => 0,
            'ia1' => 0, 'ia2' => 0
        ];

        $organizationCountryId = Organization::find()
            ->where(['organization_id' => $bill->Get('organization_id')])
            ->orderBy(['actual_from' => SORT_DESC])
            ->select('country_id')
            ->scalar();

        // для не русских организаций только с/ф (invoice)
        if ($organizationCountryId && $organizationCountryId != Country::RUSSIA) {
            $doctypes['i1'] = count($p1);
            $doctypes['i2'] = count($p2);
            $doctypes['i3'] = count($p3);
        } else {

            for ($i = 1; $i <= 3; $i++) {
                $doctypes['a' . $i] = (int)$this->_isSF($accountId, BillDocument::TYPE_AKT, $this->_getDocumentDateByLines($bill_akts[$i], $billTs));
            }

            for ($i = 1; $i <= 7; $i++) {
                $doctypes['i' . $i] = (int)$this->_isSF($accountId, BillDocument::TYPE_INVOICE, $this->_getDocumentDateByLines($bill_invoices[$i], $billTs), $i);
            }

            for ($i = 1; $i <= 2; $i++) {
                $v = $this->_isSF($accountId, BillDocument::TYPE_UPD, $this->_getDocumentDateByLines($bill_invoice_akts[$i], $billTs));
                $doctypes['ia' . $i] = $v === null ? 0 : (int)!$v;
            }
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

    private function _getDocumentDateByLines($lines, $billTs)
    {
        if (!$lines) {
            return null;
        }

        $l = reset($lines);

        if ($l['date_from'] == '0000-00-00') {
            return $billTs;
        }

        return strtotime($l['date_from']) ?: $billTs;
    }

    /**
     * Доступна ли счет/фактура
     *
     * @param integer $accountId
     * @param string $type
     * @param integer $documentDate
     * @param int $objId
     * @return bool|null
     */
    public function _isSF($accountId, $type, $documentDate = null, $objId = null)
    {
        static $cache = [];

        if (!$documentDate) {
            return null;
        }

        if (!isset($cache[$accountId]) || !isset($cache[$accountId][$documentDate])) {
            /** @var ClientAccount $account */
            $account = ClientAccount::findOne(['id' => $accountId])
                ->loadVersionOnDate(date(DateTimeZoneHelper::DATE_FORMAT, $documentDate));

            $cache[$accountId][$documentDate] = $account->getTaxRate();
        }

        $taxRate = $cache[$accountId][$documentDate];

        if ($type == BillDocument::TYPE_INVOICE && $objId == BillDocument::ID_GOODS) { // документ на товар
            return $taxRate ? BillDocument::SUBID_GOODS_UPDT : BillDocument::SUBID_GOODS_LADING; // 1 - УПДТ, 2 - Товарная накладная
        }

        if (!$taxRate) {
            if ($type != BillDocument::TYPE_AKT) { // в упрощенке только акты
                return null;
            }

            return true; // если мы здесь, значит в документе должен быть доступен
        }

        // далее отработка ЛС с основной системой налогооблажения (ОСН)
        $period1 = strtotime("2014-07-01"); // переход на УПД
        $period2 = strtotime("2017-01-01"); // возврат на с/ф и акт


        if ($documentDate >= $period2) {
            return true;
        } else if ($documentDate >= $period1) {
            return false;
        } else {
            return true;
        }
    }
}
