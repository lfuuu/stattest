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
        $this->type = $type;
        $this->Contact = ClientContact::find_by_id($contactId);
        $this->value = $value;
    }

    function send()
    {
        if ($this->Contact->type == 'email') {
            //Send email
            return $this->sendMail();
        } else if($this->Contact->type == 'phone') {
            //Send SMS
            return $this->sendSMS();
        }

        return false;
    }

    function getMessage()
    {
        global $design;
        $design->assign(array('value'=>$this->value));
        $message = $design->fetch($this->tpl_dir . $this->Contact->type . '_' . $this->type . '.tpl');
        return $message;
    }

    function getSubject()
    {
        $res = 'Уведомление';
        switch ($this->type) {
            case 'min_balance':
                $res .= ' о снижении баланса';
            break;
            case 'daily_excess':
                $res .= ' о превышении суточного лимита';
            break;
            case 'add_pay_notif':
                $res .= ' о пополнении баланса';
            break;
        }
        return $res;
    }

    function sendMail()
    {
        global $db;
        $params = array(
                    'data'=>$this->Contact->data,
                    'subject'=>Encoding::toKoi8r($this->getSubject()),
                    'message'=>Encoding::toKoi8r($this->getMessage()),
                    'type'=>'email'
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
        $params = array(
                    'data'=>$this->Contact->data,
                    'message'=>Encoding::toKoi8r($this->getMessage()),
                    'type'=>'phone'
                );
        
        $res = $db->QueryInsert('lk_notice', $params);
        if ($res) 
            return true;
        else
            return false;
    }
}

