<?php 

use app\models\important_events\ImportantEventsNames;

class LkNotification {

    /**
     * 
     * @var int $clientId Идентификатор клиента
     */
    private $Client = null;

    /**
     * @var string $type Тип уведомления
     *
     * @var string $type ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT
     * @var string $type ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE
     * @var string $type ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE
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
        $this->Client = \app\models\ClientAccount::findOne([is_numeric($clientId) ? 'id' : 'client' => $clientId]);
        $this->Contact = \app\models\ClientContact::findOne($contactId);
        $this->design = new \MySmarty;

        $this->type = $type;
        $this->value = $value;
        $this->balance = round($balance, 2);
    }

    public function send()
    {
        switch( $this->Contact->type) {
            case 'email':
                return $this->sendMail();
            case 'phone':
            case 'sms':
                return $this->sendSMS();
        }

        return false;
    }

    private function getMessage()
    {
        $contactType = $this->Contact->type;

        if ($contactType === 'sms') {
            $contactType = 'phone';
        }

        $lang = $this->Client->country->lang;
        $path = $this->tpl_dir . $lang . DIR_SEP . $contactType . DIR_SEP;

        $assigns = [
            'value' => $this->value,
            'balance' => $this->balance,
            'account' => $this->Client->id,
            'organization' => $this->Client->organization,
            'currency' => Yii::t('biller', $this->Client->currency, [], $lang),
            'lk_domain' => Yii::t('settings', 'lk_domain', [], $lang),
        ];

        $this->design->assign($assigns);
        $message = $this->design->fetch($path . $this->type . '.tpl');

        if ($contactType === 'email') {
            if (in_array($this->type, [ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT, ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE, ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE])) {
                // реклама услуги ""Уведомление о критическом остатке"
                $message .= $this->design->fetch($path . '__sms_notification.tpl');
            }
            $message .= $this->design->fetch($path . '__footer.tpl');
        }

        return $message;
    }

    private function getSubject()
    {
        switch ($this->type) {
            case ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE:
            case ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE:
            case ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT:
            case ImportantEventsNames::IMPORTANT_EVENT_ADD_PAY_NOTIF:
                return Yii::t('settings', 'email_subject_' . $this->type, ['organization' => $this->Client->organization], $this->Client->country->lang);
                break;
            default:
                break;
        }
        return '';
    }

    private function sendMail()
    {
        global $db;

        $subject = $this->getSubject();
        $msg = $this->getMessage();

        if (defined('MONITORING_EMAIL')) {
            $params = [
                'data' => MONITORING_EMAIL,
                'subject' => $this->Contact->data . ' - ' . $subject,
                'message' => $msg,
                'type' => 'email',
                'contact_id' => $this->Contact->id,
                'lang' => $this->Client->country->lang,
            ];

            $res = $db->QueryInsert('lk_notice', $params);
        }

        $params = [
            'data' => $this->Contact->data,
            'subject' => $subject,
            'message' => $msg,
            'type' => 'email',
            'contact_id' => $this->Contact->id,
            'lang' => $this->Client->country->lang,
        ];

        $res = $db->QueryInsert('lk_notice', $params);

        return (bool) $res;
    }

    private function sendSMS()
    {
        global $db;

        $phoneNumber = preg_replace('/[^\d]+/', '', $this->Contact->data);

        if (defined('MONITORING_EMAIL')) {
            $params = [
                'data' => MONITORING_EMAIL,
                'message' => 'SMS: '. $this->getMessage(),
                'subject' => 'SMS - '.$phoneNumber,
                'type' => 'email',
                'contact_id' => $this->Contact->id,
                'lang' => $this->Client->country->lang,
            ];

            $res = $db->QueryInsert('lk_notice', $params);
        }

        $params = [
            'data' => $phoneNumber,
            'message' => $this->getMessage(),
            'type' => 'phone',
            'contact_id' => $this->Contact->id,
            'lang' => $this->Client->country->lang,
        ];

        $res = $db->QueryInsert('lk_notice', $params);

        return (bool) $res;
    }
}

