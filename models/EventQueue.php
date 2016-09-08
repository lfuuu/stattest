<?php
namespace app\models;

use app\classes\behaviors\uu\SyncAccountTariffLight;
use yii\db\ActiveRecord;

/**
 * @property int id
 * @property string date timestamp
 * @property string event
 * @property string param
 * @property string status 	enum('plan','ok','error','stop')
 * @property int iteration
 * @property string next_start timestamp
 * @property string log_error
 * @property string code
 */
class EventQueue extends ActiveRecord
{
    // см. в stat/crons/events/handler.php:64
    public static $events = [
        'add_payment' => 'add_payment',
        'yandex_payment' => 'yandex_payment',
        'newbills__update' => 'newbills__update',
        'check__call_chat' => 'check__call_chat',
        'call_chat__add' => 'call_chat__add',
        'client_set_status' => 'client_set_status',
        'usage_virtpbx__insert' => 'usage_virtpbx__insert',
        'actualize_number' => 'actualize_number',
        'check__usages' => 'check__usages',
        'check__voip_old_numbers' => 'check__voip_old_numbers',
        'check__voip_numbers' => 'check__voip_numbers',
        'check__virtpbx3' => 'check__virtpbx3',
        'ats3__sync' => 'ats3__sync',
        'newbills__insert' => 'newbills__insert',
        'add_account' => 'add_account',
        'product_phone_add' => 'product_phone_add',
        'usage_virtpbx__update' => 'usage_virtpbx__update',
        'ats3__disabled_number' => 'ats3__disabled_number',
        'actualize_client' => 'actualize_client',
        'ats3__blocked' => 'ats3__blocked',
        'midnight' => 'midnight',
        'midnight__monthly_fee_msg' => 'midnight__monthly_fee_msg',
        'midnight__clean_pre_payed_bills' => 'midnight__clean_pre_payed_bills',
        'midnight__clean_event_queue' => 'midnight__clean_event_queue',
        'newbills__delete' => 'newbills__delete',
        'product_phone_remove' => 'product_phone_remove',
        'ats3__unblocked' => 'ats3__unblocked',
        'doc_date_changed' => 'doc_date_changed',
        'ats2_numbers_check' => 'ats2_numbers_check',
        'call_chat__del' => 'call_chat__del',
        'cyberplat_payment' => 'cyberplat_payment',
        'update_products' => 'update_products',
        'midnight__lk_bills4all' => 'midnight__lk_bills4all',
        'call_chat__update' => 'call_chat__update',
        'lk_settings_to_mailer' => 'lk_settings_to_mailer',
        'usage_virtpbx__delete' => 'usage_virtpbx__delete',
        SyncAccountTariffLight::EVENT_ADD_TO_ACCOUNT_TARIFF_LIGHT => SyncAccountTariffLight::EVENT_ADD_TO_ACCOUNT_TARIFF_LIGHT,
        SyncAccountTariffLight::EVENT_DELETE_FROM_ACCOUNT_TARIFF_LIGHT => SyncAccountTariffLight::EVENT_DELETE_FROM_ACCOUNT_TARIFF_LIGHT,
    ];

    public static function tableName()
    {
        return 'event_queue';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Время запуска',
            'next_start' => 'Время следующего запуска',
            'event' => 'Событие',
            'param' => 'Параметры',
            'status' => 'Статус',
            'iteration' => 'Кол-во попыток',
            'log_error' => 'Лог ошибок',
            'code' => 'Код',
        ];
    }

}