<?php
namespace app\models;

use app\classes\behaviors\HistoryVersion;
use app\classes\behaviors\HistoryChanges;
use yii\db\ActiveRecord;

class Client extends ActiveRecord
{
    public static function tableName()
    {
        return 'clients';
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Пароль',
            'comment' => 'Комментарий',
            //'status' => '   ',
            'usd_rate_percent' => 'USD уровень в процентах',
            'address_post' => 'Почтовый адрес',
            'address_post_real' => 'Действительный почтовый адрес',
            //'support' => '',
            //'login' => '',
            'bik' => 'БИК',
            'bank_properties' => 'Банковские реквизиты',
            'currency' => 'Валюта',
            //'currency_bill' => '',
            'stamp' => 'Печатать штамп',
            'nal' => 'Нал',
            //'telemarketing' => '',
            'sale_channel' => 'Канал продаж',
            //'uid' => '',
            //'site_req_no' => '',
            //'hid_rtsaldo_date' => '',
            //'hid_rtsaldo_RUB' => '',
            //'hid_rtsaldo_USD' => '',
            //'credit_USD' => '',
            //'credit_RUB' => '',
            //'credit' => '',
            'user_impersonate' => 'Наследовать права пользователя',
            'address_connect' => 'Предполагаемый адрес подключения',
            'phone_connect' => 'Предполагаемый телефон подключения',
            'id_all4net' => 'ID в All4Net',
            'dealer_comment' => 'Комментарий для дилера',
            'form_type' => 'Формирование с/ф',
            'metro_id' => 'Станция метро',
            'payment_comment' => 'Комментарии к платежу',
            'credit' => 'Разрешить кредит',
            'credit_size' => 'Размер кредита',
            //'previous_reincarnation' => '',
            //'cli_1c' => '',
            //'con_1c' => '',
            'corr_acc' => 'К/С',
            'pay_acc' => 'Р/С',
            'bank_name' => 'Название банка',
            'bank_city' => 'Город банка',
            //'sync_1c' => '',
            'price_type' => 'Тип цены',
            'voip_credit_limit' => 'Телефония, лимит использования (месяц)',
            'voip_disabled' => 'Выключить телефонию (МГ, МН, Местные мобильные)',
            'voip_credit_limit_day' => 'Телефония, лимит использования (день)',
            'balance' => 'Баланс',
            //'balance_usd' => '',
            'voip_is_day_calc' => 'Включить пересчет дневного лимита',
            'region' => 'Регион',
            //'last_account_date' => '',
            //'last_payed_voip_month' => '',
            'mail_print' => 'Печать конвертов',
            'mail_who' => '"Кому" письмо',
            'head_company' => 'Головная компания',
            'head_company_address_jur' => 'Юр. адрес головной компании',
            //'created' => '',
            'bill_rename1' => 'Номенклатура',
            //'nds_calc_method' => '',
            //'admin_contact_id' => '',
            //'admin_is_active' => '',
            'is_agent' => 'Агент',
            //'is_bill_only_contract' => '',
            //'is_bill_with_refund' => '',
            'is_with_consignee' => 'Использовать грузополучателя',
            'consignee' => 'Грузополучатель',
            'is_upd_without_sign' => 'Печать УПД без подписей',
            //'is_active' => '',
            'is_blocked' => 'Блокировка',
            //'is_closed' => '',
            'timezone_name' => 'Часовой пояс',
        ];
    }

    public function behaviors()
    {
        return [
            HistoryVersion::className(),
            HistoryChanges::className(),
        ];
    }

    public function getContract()
    {
        return $this->hasOne(ClientContract::className(),['id' => 'contract_id']);
    }

    public function getRegionName()
    {
        $r = $this->hasOne(Region::className(), ['id' => 'region'])->one();
        return ($r) ? $r->name : $this->region;
    }

    public function getAllDocuments(){
        $this->hasMany(ClientDocument::className(), ['client_id' => 'id'])->all();
        return $this->hasMany(ClientDocument::className(), ['client_id' => 'id']);
    }

    public function getAllContacts(){
        return $this->hasMany(ClientContact::className(), ['client_id' => 'id']);
    }
}
