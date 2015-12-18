<?php

use app\models\ClientAccount;

class NewBill extends ActiveRecord\Model
{
    static $table_name = "newbills";
    static $primary_key = 'bill_no';

    static $has_one = array(
        array('trouble', 'class_name' => 'Trouble', 'foreign_key' => 'bill_no')
        );

    static $has_many = array(
        array("lines", "class_name" => "BillLines", "foreign_key" => "bill_no")
        );

    public static function getLastUnpayedBill($clientId)
    {
        $fromDate = "2000-01-01";
        if($lastSaldo = Saldo::getLastSaldo($clientId))
        {
            $fromDate = $lastSaldo->date;
        }

        //unpayed
        $b = NewBill::find('first', array(
                    "conditions" => array("client_id = ? and is_payed in (0,2) and currency=? and bill_date > ?", $clientId, "RUB", $fromDate), // 0 - not paid, 1 - fully paid, 2 - partly paid
                    "limit" => 1,
                    "order" => "bill_date"
                    )
                );

        if ($b)
            return $b;

        //last bill
        $b = NewBill::find('first', array(
                    "conditions" => array("client_id = ? and is_payed = 1 and currency=? and bill_date > ?", $clientId, "RUB", $fromDate),
                    "limit" => 1,
                    "order" => "bill_date desc"
                    )
                );

        if ($b)
            return $b;

        return false;
    }

    public function is1C()
    {
        return strpos("/", $this->bill_no) !== false;
    }

    public static function setLkShowForAll()
    {
        foreach(self::find('all', array("conditions" => array('is_lk_show' => 0))) as $b)
        {
            $b->is_lk_show = 1;
            $b->save();
        }
    }

    /**
     * Создает счет на основе суммы платежа
     *
     * @param $clientId Id клиента
     * @param $paySum сумма платежа
     *
     * @return ActiveRecord//Model//NewBill
     */
    public static function createBillOnPay($clientId, $paySum, $createAutoLkLog = false)
    {
        $clientAccount = ClientAccount::findOne($clientId);
        $bill = new Bill(null, $clientAccount->id, time(), 0, $clientAccount->currency, true, true);
        $bill->AddLine('Авансовый платеж за услуги связи', 1, $paySum, 'zadatok');
        $bill->Save();
        $billNo = $bill->GetNo();
        if ($createAutoLkLog) 
        {
            LogBill::log($billNo, "Создание счета из личного кабинета", true);
        }

        return NewBill::find_by_bill_no($billNo);
    }
}
