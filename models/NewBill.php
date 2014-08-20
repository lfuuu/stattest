<?php

class NewBill extends ActiveRecord\Model
{
    static $table_name = "newbills";
    static $private_key = 'bill_no';

    static $belongs_to = array(
        array('client', 'class_name' => 'ClientCard')
    );

    static $has_one = array(
        array('trouble', 'class_name' => 'Trouble', 'foreign_key' => 'bill_no')
        );

    static $has_many = array(
        array("lines", "class_name" => "BillLines", "foreign_key" => "bill_no")
        );

    public function getLastUnpayedBill($clientId)
    {
        //unpayed
        $b = NewBill::find('first', array(
                    "conditions" => array("client_id = ? and is_payed in (0,2)", $clientId), // 0 - not paid, 1 - fully paid, 2 - partly paid
                    "limit" => 1,
                    "order" => "bill_date"
                    )
                );

        if ($b)
            return $b;

        //last bill
        $b = NewBill::find('first', array(
                    "conditions" => array("client_id = ? and is_payed = 1", $clientId),
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

    public function setLkShowForAll()
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
    public function createBillOnPay($clientId, $paySum)
    {
        $currency = "RUR";
        $bill = new Bill(null,$clientId,time(),0,$currency, true, true);
        $bill->AddLine($currency, Encoding::toKoi8r("Авансовый платеж за услуги связи"),1, $paySum/1.18, "zadatok");
        $bill->Save();

        return NewBill::find_by_bill_no($bill->GetNo());
    }
}
