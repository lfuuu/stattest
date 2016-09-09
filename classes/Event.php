<?php
namespace app\classes;

use app\models\EventQueue;

class Event
{
    const ACTUALIZE_CLIENT = 'actualize_client';
    const ACTUALIZE_NUMBER = 'actualize_number';
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
    const CHECK__CALL_CHAT = 'check__call_chat';
    const CHECK__USAGES = 'check__usages';
    const CHECK__VIRTPBX3 = 'check__virtpbx3';
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
    const UU_TARIFICATE = 'uu_tarificate';
    const UU_ACCOUNT_TARIFF_VOIP = 'uu_account_tariff_voip';
    const UU_ACCOUNT_TARIFF_VPBX = 'uu_account_tariff_vpbx';
    const YANDEX_PAYMENT = 'yandex_payment';

    const names = [
        self::ACTUALIZE_CLIENT => 'Актуализировать клиента',
        self::ACTUALIZE_NUMBER => 'актуализировать номер',
        self::ADD_ACCOUNT => 'Добавлен ЛС',
        self::ADD_PAYMENT => 'Платеж добавлен',
        self::YANDEX_PAYMENT => 'Платеж из Яндекс.Деньги',
        self::CYBERPLAT_PAYMENT => 'Платеж из киберплата',
        self::ATS2_NUMBERS_CHECK => self::ATS2_NUMBERS_CHECK,
        self::ATS3__BLOCKED => 'Номер заблокирован',
        self::ATS3__DISABLED_NUMBER => 'Номер временно отключен',
        self::ATS3__SYNC => 'Синхронизировать номер',
        self::ATS3__UNBLOCKED => 'Номер разблокирован',
        self::CALL_CHAT__ADD => 'Услуга звонок-чат добавлена',
        self::CALL_CHAT__DEL => 'Услуга звонок-чат удалена',
        self::CALL_CHAT__UPDATE => 'Услуга звонок-чат измененна',
        self::CHECK__CALL_CHAT => 'Проверить услугу звонок-чат',
        self::CHECK__USAGES => 'Проверить "старые" услуги',
        self::CHECK__VIRTPBX3 => 'Проверить услуги ВАТС',
        self::CHECK__VOIP_NUMBERS => self::CHECK__VOIP_NUMBERS,
        self::CHECK__VOIP_OLD_NUMBERS => self::CHECK__VOIP_OLD_NUMBERS,
        self::CLIENT_SET_STATUS => 'Изменился бизнес процесс ЛС',
        self::DOC_DATE_CHANGED => 'Изменился дата отгрузки счета',
        self::LK_SETTINGS_TO_MAILER => 'Передать настройки в mailer',
        self::MIDNIGHT => 'Полночь',
        self::MIDNIGHT__CLEAN_EVENT_QUEUE => 'Очистка очереди событий',
        self::MIDNIGHT__CLEAN_PRE_PAYED_BILLS => self::MIDNIGHT__CLEAN_PRE_PAYED_BILLS,
        self::MIDNIGHT__LK_BILLS4ALL => 'Принудительная публикация счетов',
        self::MIDNIGHT__MONTHLY_FEE_MSG => self::MIDNIGHT__MONTHLY_FEE_MSG,
        self::NEWBILLS__INSERT => 'Счет добавлен',
        self::NEWBILLS__UPDATE => 'Счет изменен',
        self::NEWBILLS__DELETE => 'Счет удален',
        self::PRODUCT_PHONE_ADD => 'Добавление продукта "телефония"',
        self::PRODUCT_PHONE_REMOVE => 'Удаление продукта "телефония"',
        self::UPDATE_PRODUCTS => 'Обновить продукты',
        self::USAGE_VIRTPBX__INSERT => 'Услуга ВАТС добавлена',
        self::USAGE_VIRTPBX__UPDATE => 'Услуга ВАТС изменена',
        self::USAGE_VIRTPBX__DELETE => 'Услуга ВАТС удалена',
        self::UU_TARIFICATE => 'uu-тарификатор по клиенту',
        self::UU_ACCOUNT_TARIFF_VOIP => 'uu-услуга телефонии',
        self::UU_ACCOUNT_TARIFF_VPBX => 'uu-услуга ВАТС',
    ];

    /**
     * Функция добавления события в очередь обработки
     *
     * @param string $event Название собятия
     * @param string|array $param Данные для обработки собятия
     * @param bool $isForceAdd Принудительное добавления собятия. (Если событие уже есть в очереди, то оно не добавляется)
     */
    public static function go($event, $param = "", $isForceAdd = false)
    {
        if (is_array($param)) {
            $param = json_encode($param);
        }

        $code = md5($event . "|||" . $param);

        $row = null;
        if (!$isForceAdd) {
            $row =
                EventQueue::find()
                    ->andWhere(['code' => $code])
                    ->andWhere("status not in ('ok', 'stop')")
                    ->limit(1)
                    ->one();
        }

        if (!$row) {
            $row = new EventQueue();
            $row->event = $event;
            $row->param = $param;
            $row->code = $code;
            $row->log_error = '';
        } else {
            $row->iteration = 0;
            $row->status = 'plan';
        }
        $row->save();
    }
}