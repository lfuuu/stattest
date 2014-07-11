<?php 

class LkNotification {

    /**
     * 
     * @var int $clientId Идентификатор клиента
     */
    private $Client = null;

    /**
     * @var string $type Тип уведомления (min_balance, daily_excess, add_pay_notif)
     */
    private $type = null;

    /**
     * 
     * @var int $contactId Идентификатор контакта клиента
     */
    private $Contact = null;

    /**
     *
     * @var int $value Значение для уведомления
     */
    private $value = null;

    /**
     * 
     * @var string $tpl_dir Папка с шаблонами сообщений
     */
    private $tpl_dir = 'letters/notification/';

    public function __construct($clientId, $contactId, $type, $value)
    {
        $this->Client = ClientCard::find_by_id($clientId);
        $this->Contact = ClientContact::find_by_id($contactId);

        $this->type = $type;
        $this->value = $value;
    }

    function send()
    {
        switch( $this->Contact->type) {
            case 'email': return $this->sendMail();

            case 'phone': 
            case 'sms': return $this->sendSMS();
        }

        return false;
    }

    function getMessage()
    {
        global $design;

        $contactType = $this->Contact->type;

        if ($contactType == "sms") {
            $contactType = "phone";
        }

        $design->assign(array('value'=>$this->value, 'balance' => $this->Client->balance, "account" => $this->Client->id));
        $message = $design->fetch($q = $this->tpl_dir . $contactType . '_' . $this->type . '.tpl');


        if ($contactType == "email") {
            if (in_array($this->type, array("day_limit", "min_balance", "zero_balance"))) {
                $message .= $design->fetch($q = $this->tpl_dir . $contactType . '__sms_notification.tpl'); // реклама услуги ""Уведомление о критическом остатке"

            }
            $message .= $design->fetch($this->tpl_dir . $contactType . '__footer.tpl'); 
        }


        return $message;
    }

    function getSubject()
    {
        $res = 'Уведомление';
        switch ($this->type) {
            case 'min_balance':
                $res .= ' о критическом остатке на лицевом счете МСН Телеком';
            break;
            case 'zero_balance':
                $res .= ' о финансовой блокировке усулг связи МСН Телеком';
            break;
            case 'day_limit':
                $res .= ' о превышении суточного лимита';
            break;
            case 'add_pay_notif':
                $res .= ' о зачислении средств на лицевой счета МСН Телеком';
            break;
        }
        return $res;
    }

    function sendMail()
    {
        global $db;
        $params = array(
                    'data'=>'adima123@yandex.ru', //($this->Contact->data, /** for test **/
                    'subject'=>$this->Contact->data." - "./** for test **/Encoding::toKoi8r($this->getSubject()),
                    'message'=>Encoding::toKoi8r($this->getMessage()),
                    'type'=>'email',
                    'contact_id'=>$this->Contact->id
                );

        $res = $db->QueryInsert('lk_notice', $params);
        if ($res) 
            return true;
        else
            return false;
    }

    function sendSMS()
    {
        global $db;

        $phoneNumber = preg_replace("/[^\d]+/", "", $this->Contact->data);

        $params = array(
                    'data'=> '79264290771', //$phoneNumber, /** for test **/
                    'message'=>$phoneNumber./** for test */Encoding::toKoi8r($this->getMessage()),
                    'type'=>'phone',
                    'contact_id'=>$this->Contact->id
                );
        
        $res = $db->QueryInsert('lk_notice', $params);
        if ($res) 
            return true;
        else
            return false;
    }
}

