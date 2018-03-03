<?php

namespace app\modules\webhook\models;

use app\models\ClientContact;
use yii\base\Model;

class ApiHook extends Model
{
    const EVENT_TYPE_IN_CALLING_START = 'onInCallingStart'; // начало входящего звонка на абонента ВАТС
    const EVENT_TYPE_IN_CALLING_END = 'onInCallingEnd'; // конец входящего звонка на абонента ВАТС
    const EVENT_TYPE_IN_CALLING_MISSED = 'onInCallingMissed'; // упущен входящий звонок на абонента ВАТС

    const EVENT_TYPE_OUT_CALLING_START = 'onOutCallingStart'; // начало исходящего звонка от абонента ВАТС
    const EVENT_TYPE_OUT_CALLING_END = 'onOutCallingEnd'; // конец исходящего звонка от абонента ВАТС

    const EVENT_TYPE_INBOUND_CALL_START = 'InboundCallStart'; // начало входящего звонка
    const EVENT_TYPE_INBOUND_CALL_END = 'InboundCallEnd'; // конец входящего звонка

    const EVENT_TYPE_IN_CALLING_ANSWERED = 'onInCallingAnswered'; // начало входящего разговора

    const TIMEOUT = 120000; // время (в миллисекундах) автоскрывания уведомления

    public
        $event_type, // тип события
        $abon, // внутренний номер абонента ВАТС, который принимает/совершает звонок. Только если через ВАТС
        $did, // номер вызывающего/вызываемого абонента
        $secret, // секретный token, подтверждающий, что запрос пришел от валидного сервера
        $account_id; // ID аккаунта MCN Telecom. Это не клиент!

    public $dids = [];

    private $_eventTypeToMessage = [
        self::EVENT_TYPE_IN_CALLING_START => 'Входящий звонок',
        self::EVENT_TYPE_IN_CALLING_END => 'Входящий звонок окончен',
        self::EVENT_TYPE_IN_CALLING_MISSED => 'Входящий звонок пропущен',

        self::EVENT_TYPE_OUT_CALLING_START => 'Исходящий звонок',
        self::EVENT_TYPE_OUT_CALLING_END => 'Исходящий звонок закончен',

        self::EVENT_TYPE_INBOUND_CALL_START => 'Входящий звонок',
        self::EVENT_TYPE_INBOUND_CALL_END => 'Входящий звонок окончен',

        self::EVENT_TYPE_IN_CALLING_ANSWERED => 'Отвеченный звонок',
    ];

    private $_eventTypeToStyle = [
        self::EVENT_TYPE_IN_CALLING_START => 'success',
        self::EVENT_TYPE_IN_CALLING_MISSED => 'danger',

        self::EVENT_TYPE_OUT_CALLING_START => 'info',

        self::EVENT_TYPE_INBOUND_CALL_START => 'success',

        self::EVENT_TYPE_IN_CALLING_ANSWERED => 'warning',
    ];

    private $_eventTypeToTimeout = [
        self::EVENT_TYPE_IN_CALLING_START => self::TIMEOUT,
        self::EVENT_TYPE_IN_CALLING_MISSED => 0, // никогда не скрывать

        self::EVENT_TYPE_OUT_CALLING_START => self::TIMEOUT,

        self::EVENT_TYPE_INBOUND_CALL_START => self::TIMEOUT,
        self::EVENT_TYPE_IN_CALLING_ANSWERED => 0, // никогда не скрывать
    ];

    private $eventTypesForCloseEvent = [
        ApiHook::EVENT_TYPE_IN_CALLING_ANSWERED => [
            ApiHook::EVENT_TYPE_IN_CALLING_START
        ]
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
     * @return ClientContact[]
     */
    public function getClientContacts()
    {
        if (!$this->did) {
            return [];
        }

        if ($this->did[0] == '+') {
            $this->dids = [$this->did];
        } else {
            list($phoneRemain, $this->dids) = ClientContact::dao()->getE164($this->did);
        }

        return ClientContact::find()
            ->where([
                'type' => ClientContact::$phoneTypes,
                'data' => $this->dids,
            ])
            ->orderBy(['client_id' => SORT_DESC])
            ->all();
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

    /**
     * Получение списка типов сообщений для закрытия при появлении сообщения текущего типа
     *
     * @return array
     */
    public function getEventTypesForClose()
    {
        if (isset($this->eventTypesForCloseEvent[$this->event_type])) {
            return $this->eventTypesForCloseEvent[$this->event_type];
        }

        return [];
    }

}