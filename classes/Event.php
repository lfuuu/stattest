<?php

namespace app\classes;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\EventQueueIndicator;

class Event
{
    const ACTUALIZE_CLIENT = 'actualize_client';
    const ACTUALIZE_NUMBER = 'actualize_number';
    const ADD_SUPER_CLIENT = 'add_super_client';
    const CHECK_CREATE_CORE_OWNER = 'check_create_core_owner';
    const SYNC_CORE_ADMIN = 'sync_core_admin';
    const ADD_ACCOUNT = 'add_account';
    const ADD_PAYMENT = 'add_payment';
    const ATS2_NUMBERS_CHECK = 'ats2_numbers_check';
    const ATS3__BLOCKED = 'ats3__blocked';
    const ATS3__DISABLED_NUMBER = 'ats3__disabled_number';
    const ATS3__SYNC = 'ats3__sync';
    const ATS3__UNBLOCKED = 'ats3__unblocked';
    const CALL_CHAT__ADD = 'call_chat__add';
    const CALL_CHAT__DEL = 'call_chat__del';
    const CALL_CHAT__UPDATE = 'call_chat__update';
    const CORE_CREATE_OWNER = 'core_create_owner';
    const CHECK__CALL_CHAT = 'check__call_chat';
    const CHECK__USAGES = 'check__usages';
    const CHECK__VIRTPBX3 = 'check__virtpbx3';
    const SYNC__VIRTPBX3 = 'sync__virtpbx3';
    const CHECK__VOIP_NUMBERS = 'check__voip_numbers';
    const CHECK__VOIP_OLD_NUMBERS = 'check__voip_old_numbers';
    const CLIENT_SET_STATUS = 'client_set_status';
    const CYBERPLAT_PAYMENT = 'cyberplat_payment';
    const DOC_DATE_CHANGED = 'doc_date_changed';
    const LK_SETTINGS_TO_MAILER = 'lk_settings_to_mailer';
    const MIDNIGHT = 'midnight';
    const MIDNIGHT__CLEAN_EVENT_QUEUE = 'midnight__clean_event_queue';
    const MIDNIGHT__CLEAN_PRE_PAYED_BILLS = 'midnight__clean_pre_payed_bills';
    const MIDNIGHT__LK_BILLS4ALL = 'midnight__lk_bills4all';
    const MIDNIGHT__MONTHLY_FEE_MSG = 'midnight__monthly_fee_msg';
    const NEWBILLS__DELETE = 'newbills__delete';
    const NEWBILLS__INSERT = 'newbills__insert';
    const NEWBILLS__UPDATE = 'newbills__update';
    const PRODUCT_PHONE_ADD = 'product_phone_add';
    const PRODUCT_PHONE_REMOVE = 'product_phone_remove';
    const UPDATE_PRODUCTS = 'update_products';
    const USAGE_VIRTPBX__DELETE = 'usage_virtpbx__delete';
    const USAGE_VIRTPBX__INSERT = 'usage_virtpbx__insert';
    const USAGE_VIRTPBX__UPDATE = 'usage_virtpbx__update';
    const USAGE_VOIP__DELETE = 'usage_voip__delete';
    const USAGE_VOIP__INSERT = 'usage_voip__insert';
    const USAGE_VOIP__UPDATE = 'usage_voip__update';
    const YANDEX_PAYMENT = 'yandex_payment';
    const UPDATE_BALANCE = 'update_balance';
    const ACCOUNT_BLOCKED = 'account_blocked';
    const ACCOUNT_UNBLOCKED = 'account_unblocked';
    const PARTNER_REWARD = 'partner_reward';
    const VPBX_BLOCKED = 'vpbx_blocked';
    const VPBX_UNBLOCKED = 'vpbx_unblocked';

    const API_IS_SWITCHED_OFF = 'API is switched off';

    public static $names = [
        self::ACTUALIZE_CLIENT => 'Актуализировать клиента',
        self::ACTUALIZE_NUMBER => 'Актуализировать номер',
        self::ADD_SUPER_CLIENT => 'Добавлен (супер)клиент',
        self::CHECK_CREATE_CORE_OWNER => 'Проверяеть необходимость создания администратора в ЛК',
        self::SYNC_CORE_ADMIN => 'Создание администратора в ЛК',
        self::ADD_ACCOUNT => 'Добавлен ЛС',
        self::ADD_PAYMENT => 'Платеж добавлен',
        self::YANDEX_PAYMENT => 'Платеж из Яндекс.Деньги',
        self::CYBERPLAT_PAYMENT => 'Платеж из киберплата',
        self::ATS3__BLOCKED => 'Номер заблокирован',
        self::ATS3__DISABLED_NUMBER => 'Номер временно отключен',
        self::ATS3__SYNC => 'Синхронизировать номер',
        self::ATS3__UNBLOCKED => 'Номер разблокирован',
        self::CALL_CHAT__ADD => 'Услуга call chat добавлена',
        self::CALL_CHAT__DEL => 'Услуга call chat удалена',
        self::CALL_CHAT__UPDATE => 'Услуга call chat изменена',
        self::CORE_CREATE_OWNER => 'Создание админа в ЛК',
        self::CHECK__CALL_CHAT => 'Проверить услугу call chat',
        self::CHECK__USAGES => 'Проверить "старые" услуги',
        self::CHECK__VIRTPBX3 => 'Проверить услуги ВАТС',
        self::SYNC__VIRTPBX3 => 'Синхронизация услуги ВАТС',
        self::CHECK__VOIP_NUMBERS => 'Актуализировать все номера',
        self::CHECK__VOIP_OLD_NUMBERS => 'Синхронизировать все "старые" услуги номеров',
        self::CLIENT_SET_STATUS => 'Изменился бизнес процесс ЛС',
        self::DOC_DATE_CHANGED => 'Изменился дата отгрузки счета',
        self::LK_SETTINGS_TO_MAILER => 'Передать настройки в mailer',
        self::MIDNIGHT => 'Полночь',
        self::MIDNIGHT__CLEAN_EVENT_QUEUE => 'Очистка очереди событий',
        self::MIDNIGHT__CLEAN_PRE_PAYED_BILLS => 'Удаление пустых счетов на предоплату из ЛК',
        self::MIDNIGHT__LK_BILLS4ALL => 'Принудительная публикация счетов',
        self::MIDNIGHT__MONTHLY_FEE_MSG => 'Предупреждаем о списании абонентки авансовым клиентам',
        self::NEWBILLS__INSERT => 'Счет добавлен',
        self::NEWBILLS__UPDATE => 'Счет изменен',
        self::NEWBILLS__DELETE => 'Счет удален',
        self::PRODUCT_PHONE_ADD => 'Добавление продукта "телефония"',
        self::PRODUCT_PHONE_REMOVE => 'Удаление продукта "телефония"',
        self::UPDATE_PRODUCTS => 'Обновить продукты',
        self::USAGE_VIRTPBX__INSERT => 'Услуга ВАТС добавлена',
        self::USAGE_VIRTPBX__UPDATE => 'Услуга ВАТС изменена',
        self::USAGE_VIRTPBX__DELETE => 'Услуга ВАТС удалена',
        self::USAGE_VOIP__INSERT => 'Услуга телефонии добавлена',
        self::USAGE_VOIP__UPDATE => 'Услуга телефонии изменена',
        self::USAGE_VOIP__DELETE => 'Услуга телефонии удалена',
        self::UPDATE_BALANCE => 'Обновление баланса',
        self::ACCOUNT_BLOCKED => 'ЛС заблокирован',
        self::ACCOUNT_UNBLOCKED => 'ЛС разблокирован',
        self::PARTNER_REWARD => 'Подсчет вознаграждения партнера',
        self::VPBX_BLOCKED => 'Блокировка ВАТС',
        self::VPBX_UNBLOCKED => 'Разблокировка ВАТС',

        \app\modules\atol\Module::EVENT_SEND => 'АТОЛ. Отправить',
        \app\modules\atol\Module::EVENT_REFRESH => 'АТОЛ. Обновить',

        \app\modules\uu\Module::EVENT_VOIP_CALLS => 'УУ. Телефония',
        \app\modules\uu\Module::EVENT_VOIP_INTERNET => 'УУ. Моб. интернет',
        \app\modules\uu\Module::EVENT_VPBX => 'УУ. ВАТС',
        \app\modules\uu\Module::EVENT_CALL_CHAT => 'УУ. Call chat',
        \app\modules\uu\Module::EVENT_RESOURCE_VOIP => 'УУ. Ресурс телефонии',
        \app\modules\uu\Module::EVENT_RESOURCE_VPBX => 'УУ. Ресурс ВАТС',
        \app\modules\uu\Module::EVENT_RECALC_ACCOUNT => 'УУ. Билинговать клиента',
        \app\modules\uu\Module::EVENT_RECALC_BALANCE => 'УУ. Обновление баланса',
        \app\modules\uu\Module::EVENT_VM_SYNC => 'УУ. VM manager',
        \app\modules\uu\Module::EVENT_ADD_LIGHT => 'УУ. Добавить пакет телефонии',
        \app\modules\uu\Module::EVENT_DELETE_LIGHT => 'УУ. Удалить пакет телефонии',

        \app\modules\nnp\Module::EVENT_IMPORT => 'ННП. Импорт страны',
        \app\modules\nnp\Module::EVENT_LINKER => 'ННП. Линковка исходных к ID',
        \app\modules\nnp\Module::EVENT_FILTER_TO_PREFIX => 'ННП. Фильтр -> префикс',

        \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_BALANCE => 'МТТ. Получить баланс',
        \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_DATA => 'МТТ. Получить инфо',
        \app\modules\mtt\Module::EVENT_CALLBACK_BALANCE_ADJUSTMENT => 'МТТ. Установить баланс',
    ];

    /**
     * Функция добавления события в очередь обработки
     *
     * @param string $event Название события
     * @param string|array $param Данные для обработки события
     * @param bool $isForceAdd Принудительное добавления события. (Если событие уже есть в очереди, то оно не добавляется)
     * @return EventQueue
     * @throws \app\exceptions\ModelValidationException
     */
    public static function go($event, $param = "", $isForceAdd = false)
    {
        if (is_array($param) || is_object($param)) {
            $param = json_encode($param);
        }

        $code = md5($event . "|||" . $param);

        $eventQueue = null;
        if (!$isForceAdd) {
            /** @var EventQueue $eventQueue */
            $eventQueue = EventQueue::find()
                ->andWhere(['code' => $code])
                ->andWhere("status not in ('ok', 'stop')")
                ->limit(1)
                ->one();
        }

        if (!$eventQueue) {
            $eventQueue = new EventQueue();
            $eventQueue->event = $event;
            $eventQueue->param = $param;
            $eventQueue->code = $code;
            $eventQueue->log_error = '';
            $eventQueue->insert_time = date(DateTimeZoneHelper::DATETIME_FORMAT);
        } else {
            $eventQueue->iteration = 0;
            $eventQueue->status = EventQueue::STATUS_PLAN;
        }

        if (!$eventQueue->save()) {
            throw new ModelValidationException($eventQueue);
        }

        return $eventQueue;
    }

    /**
     * Создание задачи с привязкой индикатора
     *
     * @param string $event
     * @param mixed $eventParam
     * @param string $object
     * @param integer $objectId
     * @param string|null $section
     * @return EventQueue
     * @throws ModelValidationException
     */
    public static function goWithIndicator($event, $eventParam, $object, $objectId = 0, $section = null)
    {
        $indicator = null;

        if ($object && $objectId) {
            /** @var EventQueueIndicator $indicator */
            $indicator = EventQueueIndicator::findOne([
                'object' => $object,
                'object_id' => $objectId,
                'section' => $section
            ]);

            // удаляем задачу из очереди, если она не выполнена
            if ($indicator &&
                $indicator->event &&
                in_array($indicator->event->status, [EventQueue::STATUS_PLAN, EventQueue::STATUS_ERROR])
            ) {
                $indicator->event->delete();
            }
        }

        $eventQueue = self::go($event, $eventParam);

        if (!$indicator) {
            $indicator = new EventQueueIndicator;
            $indicator->object = $object;
            $indicator->object_id = $objectId;
            $indicator->section = $section;
        }

        $indicator->event_queue_id = $eventQueue->id;

        if (!$indicator->save()) {
            throw new ModelValidationException($eventQueue);
        }

        return $eventQueue;
    }
}
