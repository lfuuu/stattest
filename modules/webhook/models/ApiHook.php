<?php

namespace app\modules\webhook\models;

use app\models\ClientAccount;
use app\models\ClientContact;
use yii\base\Model;

/**
 * @property ClientAccount $clientAccount
 */
class ApiHook extends Model
{
    const EVENT_TYPE_IN_CALLING_START = 'onInCallingStart'; // начало входящего звонка на абонента ВАТС
    const EVENT_TYPE_IN_CALLING_END = 'onInCallingEnd'; // конец входящего звонка на абонента ВАТС
    const EVENT_TYPE_IN_CALLING_MISSED = 'onInCallingMissed'; // упущен входящий звонок на абонента ВАТС

    const EVENT_TYPE_OUT_CALLING_START = 'onOutCallingStart'; // начало исходящего звонка от абонента ВАТС
    const EVENT_TYPE_OUT_CALLING_END = 'onOutCallingEnd'; // конец исходящего звонка от абонента ВАТС

    const EVENT_TYPE_INBOUND_CALL_START = 'InboundCallStart'; // начало входящего звонка
    const EVENT_TYPE_INBOUND_CALL_END = 'InboundCallEnd'; // конец входящего звонка

    const TIMEOUT = 60000; // время (в миллисекундах) автоскрывания уведомления

    public
        $event_type, // тип события
        $abon, // внутренний номер абонента ВАТС, который принимает/совершает звонок. Только если через ВАТС
        $did, // номер вызывающего/вызываемого абонента
        $secret, // секретный token, подтверждающий, что запрос пришел от валидного сервера
        $account_id; // ID аккаунта MCN Telecom. Это не клиент!

    private $_eventTypeToMessage = [
        self::EVENT_TYPE_IN_CALLING_START => 'Входящий звонок',
        self::EVENT_TYPE_IN_CALLING_END => 'Входящий звонок окончен',
        self::EVENT_TYPE_IN_CALLING_MISSED => 'Входящий звонок пропущен',

        self::EVENT_TYPE_OUT_CALLING_START => 'Исходящий звонок',
        self::EVENT_TYPE_OUT_CALLING_END => 'Исходящий звонок закончен',

        self::EVENT_TYPE_INBOUND_CALL_START => 'Входящий звонок',
        self::EVENT_TYPE_INBOUND_CALL_END => 'Входящий звонок окончен',
    ];

    private $_eventTypeToStyle = [
        self::EVENT_TYPE_IN_CALLING_START => 'success',
        self::EVENT_TYPE_IN_CALLING_MISSED => 'danger',

        self::EVENT_TYPE_OUT_CALLING_START => 'info',

        self::EVENT_TYPE_INBOUND_CALL_START => 'success',
    ];

    private $_eventTypeToTimeout = [
        self::EVENT_TYPE_IN_CALLING_START => self::TIMEOUT,
        self::EVENT_TYPE_IN_CALLING_MISSED => 0, // никогда не скрывать

        self::EVENT_TYPE_OUT_CALLING_START => self::TIMEOUT,

        self::EVENT_TYPE_INBOUND_CALL_START => self::TIMEOUT,
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['abon', 'account_id'], 'integer'],
            [['event_type', 'did', 'secret'], 'string'],
            [['event_type', 'did', 'secret', 'account_id'], 'required'],
        ];
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        if ($this->did && $this->did[0] != '+') {
            list($phoneRemain, $this->did) = ClientContact::dao()->getE164($this->did);
        }

        if (!$this->did) {
            return null;
        }

        /** @var ClientContact $clientContact */
        $clientContact = ClientContact::find()
            ->where([
                'type' => ClientContact::$phoneTypes,
                'data' => $this->did,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        return $clientContact ? $clientContact->client : null;
    }

    /**
     * Вернуть тип по-русски
     *
     * @return string
     */
    public function getEventTypeMessage()
    {
        return isset($this->_eventTypeToMessage[$this->event_type]) ?
            $this->_eventTypeToMessage[$this->event_type] :
            '';
    }

    /**
     * Вернуть стиль в зависимости от типа
     *
     * @return string
     */
    public function getEventTypeStyle()
    {
        return isset($this->_eventTypeToStyle[$this->event_type]) ?
            $this->_eventTypeToStyle[$this->event_type] :
            '';
    }

    /**
     * Вернуть автокрывание в миллисекундах в зависимости от типа
     *
     * @return int
     */
    public function getEventTypeTimeout()
    {
        return isset($this->_eventTypeToTimeout[$this->event_type]) ?
            $this->_eventTypeToTimeout[$this->event_type] :
            0;
    }

    /**
     * @return bool
     */
    public function getIsNotify()
    {
        return (bool)$this->getEventTypeStyle();
    }
}