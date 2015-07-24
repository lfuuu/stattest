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
     * @var int $balance Баланс лицевого счета
     */
    private $balance = null;

    /**
     * 
     * @var string $tpl_dir Папка с шаблонами сообщений
     */
    private $tpl_dir = 'letters/notification/';

    public function __construct($clientId, $contactId, $type, $value, $balance)
    {
        $this->Client = \app\models\ClientAccount::findOne([is_numeric($clientId) ? 'id' : 'client' => $clientId]);;
        $this->Contact = \app\models\ClientContact::findOne($contactId);
        $this->design = new \MySmarty();

        $this->type = $type;
        $this->value = $value;
        $this->balance = round($balance, 2);
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
        $contactType = $this->Contact->type;

        if ($contactType == "sms") {
            $contactType = "phone";
        }

        $assigns = array('value'=>$this->value, 'balance' => $this->balance, "account" => $this->Client->id);

        $this->design->assign($assigns);
        $message = $this->design->fetch($q = $this->tpl_dir . $contactType . '_' . $this->type . '.tpl');


        if ($contactType == "email") {
            if (in_array($this->type, array("day_limit", "min_balance", "zero_balance"))) {
                $message .= $this->design->fetch($q = $this->tpl_dir . $contactType . '__sms_notification.tpl'); // реклама услуги ""Уведомление о критическом остатке"

            }
            $message .= $this->design->fetch($this->tpl_dir . $contactType . '__footer.tpl'); 
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

        $subject = $this->getSubject();
        $msg = $this->getMessage();

        if (defined("TEST_NOTICE") && defined("ADMIN_EMAIL"))
        {
            $params = array(
                    'data'=>ADMIN_EMAIL,
                    'subject'=>$this->Contact->data." - ".$subject,
                    'message'=>$msg,
                    'type'=>'email',
                    'contact_id'=>$this->Contact->id
                    );

            $res = $db->QueryInsert('lk_notice', $params);
        }

        $params = array(
                    'data'=> $this->Contact->data,
                    'subject' => $subject,
                    'message' => $msg,
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

        if (defined("TEST_NOTICE") && defined("ADMIN_PHONE"))
        {
            $params = array(
                    'data'=> ADMIN_PHONE,
                    'message'=>$this->getMessage(),
                    'type'=>'phone',
                    'contact_id'=>$this->Contact->id
                    );

            $res = $db->QueryInsert('lk_notice', $params);
        }

        $params = array(
                    'data'=> $phoneNumber,
                    'message'=> $this->getMessage(),
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

