<?php
namespace app\models;

use app\classes\behaviors\AccountPriceIncludeVat;
use app\classes\behaviors\ActualizeClientVoip;
use app\classes\behaviors\ClientAccountComments;
use app\classes\behaviors\ClientAccountSyncEvent;
use app\classes\behaviors\EffectiveVATRate;
use app\classes\behaviors\EventQueueAddEvent;
use app\classes\behaviors\SetOldStatus;
use app\classes\behaviors\SetTaxVoip;
use app\classes\BillContract;
use app\classes\DateTimeWithUserTimezone;
use app\classes\Event;
use app\classes\Html;
use app\classes\model\HistoryActiveRecord;
use app\classes\Utils;
use app\dao\ClientAccountDao;
use app\models\billing\Locks;
use app\models\voip\StatisticDay;
use app\queries\ClientAccountQuery;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class ClientAccount
 *
 * @property int $id
 * @property string $client
 * @property int $super_id
 * @property int $contract_id
 * @property int $country_id
 * @property string $status
 * @property string $currency
 * @property string $nal
 * @property int $balance
 * @property int $credit
 * @property int $voip_credit_limit_day
 * @property int $voip_limit_mn_day
 * @property int $voip_disabled
 * @property int $business_id
 * @property int $price_include_vat
 * @property int $is_active
 * @property int $is_blocked
 * @property int $region
 * @property string $address_post
 * @property string $site_name
 * @property string $timezone_name
 * @property string $sale_channel
 * @property string $password
 * @property string $lk_balance_view_mode
 * @property int $account_version
 * @property int $is_postpaid
 * @property bool $type_of_bill
 * @property string $pay_acc
 * @property string $bik
 * @property string $bank_name
 * @property string $bank_city
 * @property string $bank_properties
 * @property int $admin_contact_id
 * @property int $stamp
 * @property string $corr_acc
 * @property string $address_connect
 * @property int $is_with_consignee
 * @property string $consignee
 * @property int $effective_vat_rate
 * @property int $pay_bill_until_days
 * @property int $is_bill_pay_overdue
 * @property int $is_voip_with_tax
 * @property int $price_type
 * @property int $price_level
 *
 * @property Currency $currencyModel
 * @property ClientSuper $superClient
 * @property ClientContractComment $lastComment
 * @property Country $country
 * @property Region $accountRegion
 * @property DateTimeZone $timezone
 * @property LkClientSettings $lkClientSettings
 * @property LkNoticeSetting $lkNoticeSetting
 * @property ClientFlag $flag
 * @property ClientContact[] $contacts
 * @property ClientContract $contract
 * @property ClientContragent $contragent
 * @property Organization $organization
 * @property User $userAccountManager
 * @property LkWizardState $lkWizardState
 * @property ClientCounter $billingCounters
 * @property ClientCounter $billingCountersFastMass
 * @property string $company_full
 * @property string $address_jur
 * @property ClientContact[] $allContacts
 * @property integer $businessId
 */
class ClientAccount extends HistoryActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const STATUS_INCOME = 'income';
    const STATUS_NEGOTIATIONS = 'negotiations';
    const STATUS_TESTING = 'testing';
    const STATUS_CONNECTING = 'connecting';
    const STATUS_WORK = 'work';
    const STATUS_CLOSED = 'closed';
    const STATUS_TECH_DENY = 'tech_deny';
    const STATUS_TELEMARKETING = 'telemarketing';
    const STATUS_DENY = 'deny';
    const STATUS_DEBT = 'debt';
    const STATUS_DOUBLE = 'double';
    const STATUS_TRASH = 'trash';
    const STATUS_MOVE = 'move';
    const STATUS_ALREADY = 'already';
    const STATUS_DENIAL = 'denial';
    const STATUS_ONCE = 'once';
    const STATUS_RESERVED = 'reserved';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_OPERATOR = 'operator';
    const STATUS_DISTR = 'distr';
    const STATUS_BLOCKED = 'blocked';

    const DEFAULT_REGION = Region::MOSCOW;
    const DEFAULT_VOIP_CREDIT_LIMIT_DAY = 2000; // BIL-2081: http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=14221857
    const DEFAULT_VOIP_MN_LIMIT_DAY = 1000;
    const DEFAULT_VOIP_IS_DAY_CALC = 1;
    const DEFAULT_VOIP_IS_MN_DAY_CALC = 1;
    const DEFAULT_CREDIT = 0;
    const DEFAULT_PRICE_LEVEL = 1;

    const VERSION_BILLER_USAGE = 4;
    const VERSION_BILLER_UNIVERSAL = 5;

    const TYPE_OF_BILL_SIMPLE = false;
    const TYPE_OF_BILL_DETAILED = true;

    const WARNING_UNAVAILABLE_BILLING = 'unavailable.billing'; // Сервер статистики недоступен. Данные о балансе и счетчиках могут быть неверными
    const WARNING_UNAVAILABLE_LOCKS = 'unavailable.locks'; // Сервер статистики недоступен. Данные о блокировках недоступны
    const WARNING_SYNC_ERROR = 'balance.sync_error'; // Ошибка синхронизации баланса
    const WARNING_FINANCE = 'lock.is_finance_block'; // Финансовая блокировка
    const WARNING_OVERRAN = 'lock.is_overran'; // Превышение лимитов низкоуровневого биллинга. Возможно, взломали
    const WARNING_MN_OVERRAN = 'lock.is_mn_overran'; // Превышение лимитов низкоуровневого биллинга. Возможно, взломали (МН)
    const WARNING_LIMIT_DAY = 'lock.limit_day'; // Превышен дневной лимит
    const WARNING_CREDIT = 'lock.credit'; // Превышен лимит кредита
    const WARNING_BILL_PAY_OVERDUE = 'lock.bill_pay_overdue'; // Просрочка оплаты счета

    const PAY_BILL_UNTIL_DAYS = 30;


    public static $statuses = [
        'negotiations' => ['name' => 'в стадии переговоров', 'color' => '#C4DF9B'],
        'testing' => ['name' => 'тестируемый', 'color' => '#6DCFF6'],
        'connecting' => ['name' => 'подключаемый', 'color' => '#F49AC1'],
        'work' => ['name' => 'включенный', 'color' => ''],
        'closed' => ['name' => 'отключенный', 'color' => '#FFFFCC'],
        'tech_deny' => ['name' => 'тех. отказ', 'color' => '#996666'],
        'telemarketing' => ['name' => 'телемаркетинг', 'color' => '#A0FFA0'],
        'income' => ['name' => 'входящие', 'color' => '#CCFFFF'],
        'deny' => ['name' => 'отказ', 'color' => '#A0A0A0'],
        'debt' => ['name' => 'отключен за долги', 'color' => '#C00000'],
        'double' => ['name' => 'дубликат', 'color' => '#60a0e0'],
        'trash' => ['name' => 'мусор', 'color' => '#a5e934'],
        'move' => ['name' => 'переезд', 'color' => '#f590f3'],
        'suspended' => ['name' => 'приостановленные', 'color' => '#C4a3C0'],
        'denial' => ['name' => 'отказ/задаток', 'color' => '#00C0C0'],
        'once' => ['name' => 'Интернет Магазин', 'color' => 'silver'],
        'reserved' => ['name' => 'резервирование канала', 'color' => 'silver'],
        'blocked' => ['name' => 'временно заблокирован', 'color' => 'silver'],
        'distr' => ['name' => 'Поставщик', 'color' => 'yellow'],
        'operator' => ['name' => 'Оператор', 'color' => 'lightblue']
    ];
    public static $formTypes = [
        'manual' => 'ручное',
        'bill' => 'при выставлении счета',
        'payment' => 'при внесении платежа',
    ];
    public static $nalTypes = [
        'beznal' => 'безнал',
        'nal' => 'нал',
        'prov' => 'пров'
    ];
    public static $balanceViewMode = [
        'old' => 'Старый',
        'new' => 'Новый',
    ];
    public static $versions = [
        self::VERSION_BILLER_USAGE => 'Старый',
        self::VERSION_BILLER_UNIVERSAL => 'Универсальный',
    ];

    public static $shopIds = [14050, 18042];
    public $client_orig = '';
    /**
     * Virtual variables
     */
    public $payment_info;
    public
        $attributesAllowedForVersioning = [
        'address_post',
        'address_post_real',
        'head_company',
        'head_company_address_jur',
        'consignee',
        'bik',
        'bank_properties',
        'corr_acc',
        'pay_acc',
        'bank_name',
        'bank_city',
    ];

    // Свойства модели которые должны обновляться версионно
    /**
     * /Virtual variables
     */

    private $_lastComment = false;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'clients';
    }

    /**
     * @return ClientAccountQuery
     */
    public static function find()
    {
        return new ClientAccountQuery(get_called_class());
    }

    /**
     * Правила
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['country_id', 'required'],
            ['country_id', 'integer'],
            ['voip_credit_limit_day', 'default', 'value' => self::DEFAULT_VOIP_CREDIT_LIMIT_DAY],
            ['voip_is_day_calc', 'default', 'value' => self::DEFAULT_VOIP_IS_DAY_CALC],
            ['voip_is_mn_day_calc', 'default', 'value' => self::DEFAULT_VOIP_IS_MN_DAY_CALC],
            ['region', 'default', 'value' => self::DEFAULT_REGION],
            ['credit', 'default', 'value' => self::DEFAULT_CREDIT],
            ['account_version', 'default', 'value' => self::VERSION_BILLER_USAGE],
            ['pay_bill_until_days', 'default', 'value' => self::PAY_BILL_UNTIL_DAYS],
        ];

        return $rules;
    }

    /**
     * Поведение
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'AccountPriceIncludeVat' => AccountPriceIncludeVat::className(),
            'SetOldStatus' => SetOldStatus::className(),
            'ActualizeClientVoip' => ActualizeClientVoip::className(),
            'ClientAccountComments' => ClientAccountComments::className(),
            'ClientAccountEvent' => \app\classes\behaviors\important_events\ClientAccount::className(),
            'ClientAccountSyncEvent' => ClientAccountSyncEvent::className(),
            'EventQueueAddEvent' => [
                'class' => EventQueueAddEvent::className(),
                'insertEvent' => Event::ADD_ACCOUNT
            ],
            'EffectiveVATRate' => EffectiveVATRate::className(),
            'SetTaxVoip' => SetTaxVoip::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(), // Логирование изменений всегда в конце
        ];
    }

    /**
     * Навзание полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'comment' => 'Комментарий',
            'usd_rate_percent' => 'USD уровень в процентах',
            'address_post' => 'Почтовый адрес',
            'address_post_real' => 'Действительный почтовый адрес',
            'bik' => 'БИК',
            'bank_properties' => 'Банковские реквизиты',
            'currency' => 'Валюта',
            'stamp' => 'Печатать штамп',
            'nal' => 'Предполагаемый метод платежа',
            'sale_channel' => 'Канал продаж',
            'user_impersonate' => 'Наследовать права пользователя',
            'address_connect' => 'Предполагаемый адрес подключения',
            'phone_connect' => 'Предполагаемый телефон подключения',
            'id_all4net' => 'ID в All4Net',
            'dealer_comment' => 'Комментарий для дилера',
            'form_type' => 'Формирование с/ф',
            'credit' => 'Разрешить кредит',
            'credit_size' => 'Размер кредита',
            'corr_acc' => 'К/С',
            'pay_acc' => 'Р/С',
            'bank_name' => 'Название банка',
            'bank_city' => 'Город банка',
            'price_type' => 'Тип цены для интернет-магазина',
            'voip_disabled' => 'Выключить телефонию (МГ, МН, Местные мобильные)',
            'voip_credit_limit_day' => 'Телефония, лимит использования (день)',
            'voip_limit_mn_day' => 'Телефония (МН), лимит использования (день)',
            'balance' => 'Баланс',
            'voip_is_day_calc' => 'Пересчет дневного лимита',
            'voip_is_mn_day_calc' => 'Пересчет дневного (МН) лимита',
            'region' => 'Регион',
            'mail_who' => '"Кому" письмо',
            'head_company' => 'Головная компания',
            'head_company_address_jur' => 'Юр. адрес головной компании',
            'bill_rename1' => 'Номенклатура',
            'is_agent' => 'Агент',
            'is_with_consignee' => 'Использовать грузополучателя',
            'consignee' => 'Грузополучатель',
            'is_upd_without_sign' => 'Печать УПД без подписей',
            'is_blocked' => 'Блокировка',
            'timezone_name' => 'Часовой пояс',
            'manager' => 'Менеджер',
            'account_manager' => 'Ак. менеджер',
            'custom_properties' => 'Ввести данные вручную',
            'lk_balance_view_mode' => 'Тип отображения баланса в ЛК',
            'account_version' => 'Версия ЛС',
            'anti_fraud_disabled' => 'Отключен анти-фрод',
            'is_postpaid' => 'Постоплата',
            'type_of_bill' => 'Закрывающий документ (Полный)',
            'status' => 'Статус',
            'is_active' => 'Вкл.',
            'previous_reincarnation' => 'Предыдущий аккаунт',
            'password_type' => 'Тип пароля',
            'currency_bill' => 'Валюта счета',
            'balance_usd' => 'Баланс в $',
            'last_account_date' => 'Дата актуальности баланса',
            'created' => 'Дата создания',
            'mail_print' => 'Печатать письма',
            'nds_calc_method' => 'Тип расчета НДС',
            'timezone_offset' => 'Таймзона, часы',
            'effective_vat_rate' => 'Эффективная ставка НДС',
            'pay_bill_until_days' => 'Срок оплаты счетов (в днях)',
            'is_bill_pay_overdue' => 'Блокировка по неоплате счета',
            'price_level' => 'Уровень цен',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getSuperClient()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCurrencyModel()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency']);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return ($this->contract->contragent->legal_type != 'person') ? 'org' : 'person';
    }

    /**
     * @return int
     */
    public function getFirma()
    {
        return $this->contract->organization->firma;
    }

    /**
     * @return string
     */
    public function getManager()
    {
        return $this->contract->manager;
    }

    /**
     * @return mixed
     */
    public function getManager_name()
    {
        return User::find()->where(['user' => $this->contract->manager])->one()->name;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->contract->number;
    }

    /**
     * @return int
     */
    public function getBusiness_process_id()
    {
        return $this->contract->business_process_id;
    }

    /**
     * @return int
     */
    public function getBusiness_process_status_id()
    {
        return $this->contract->business_process_status_id;
    }

    /**
     * @return int
     */
    public function getBusinessId()
    {
        return $this->contract->business_id;
    }

    /**
     * @return string
     */
    public function getAccount_manager()
    {
        return $this->contract->account_manager;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->contract->contragent->name;
    }

    /**
     * @return string
     */
    public function getCompany_full()
    {
        return $this->contract->contragent->name_full;
    }

    /**
     * @return string
     */
    public function getAddress_jur()
    {
        return $this->contract->contragent->address_jur;
    }

    /**
     * @return string
     */
    public function getInn()
    {
        return $this->contract->contragent->inn;
    }

    /**
     * @return string
     */
    public function getKpp()
    {
        return $this->contract->contragent->kpp;
    }

    /**
     * @return string
     */
    public function getSigner_position()
    {
        return $this->contract->contragent->position;
    }

    /**
     * @return string
     */
    public function getSigner_positionV()
    {
        return $this->contract->contragent->positionV;
    }

    /**
     * @return string
     */
    public function getSigner_name()
    {
        return $this->contract->contragent->fio;
    }

    /**
     * @return string
     */
    public function getSigner_nameV()
    {
        return $this->contract->contragent->fioV;
    }

    /**
     * @return string
     */
    public function getOgrn()
    {
        return $this->contract->contragent->ogrn;
    }

    /**
     * @return string
     */
    public function getOkpo()
    {
        return $this->contract->contragent->okpo;
    }

    /**
     * @return string
     */
    public function getOpf()
    {
        return $this->contract->contragent->opf_id;
    }

    /**
     * @return string
     */
    public function getOkvd()
    {
        return $this->contract->contragent->okvd;
    }

    /**
     * @return string
     */
    public function getChannelName()
    {
        return $this->sale_channel ? SaleChannelOld::getList()[$this->sale_channel] : '';
    }

    /**
     * @return ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }

    /**
     * @return int|string
     */
    public function getRegionName()
    {
        return $this->accountRegion ? $this->accountRegion->name : $this->region;
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    /**
     * @return User
     */
    public function getUserManager()
    {
        return User::findOne(['user' => $this->contract->manager]);
    }

    /**
     * @param null $date
     * @return array|false
     */
    public function getLastContract($date = null)
    {
        return BillContract::getLastContract($this->contract_id, $date);
    }

    /**
     * @return User
     */
    public function getUserAccountManager()
    {
        return User::findOne(['user' => $this->contract->account_manager]);
    }

    /**
     * @return LkWizardState
     */
    public function getLkWizardState()
    {
        return LkWizardState::findOne(["contract_id" => $this->contract->id, "is_on" => 1]);
    }

    /**
     * @return ClientContragent
     */
    public function getContragent()
    {
        return $this->getContract()->getContragent();
    }

    /**
     * @param string $date
     * @return ClientContract
     */
    public function getContract($date = '')
    {
        return $this->getCachedHistoryModel(ClientContract::className(), $this->contract_id, $date, $this);
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return isset(self::$statuses[$this->status]) ? self::$statuses[$this->status]['name'] : $this->status;
    }

    /**
     * @return string
     */
    public function getStatusColor()
    {
        return isset(self::$statuses[$this->status]) ? self::$statuses[$this->status]['color'] : '';
    }

    /**
     * @return bool|ClientContractComment
     */
    public function getLastComment()
    {
        if ($this->_lastComment === false) {
            $this->_lastComment
                = ClientContractComment::find()
                ->andWhere(['contract_id' => $this->contract_id])
                ->andWhere(['is_publish' => 1])
                ->orderBy('ts desc')
                ->all();
        }

        return $this->_lastComment;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        return new DateTimeZone($this->timezone_name);
    }

    /**
     * @return ActiveQuery
     */
    public function getAllContacts()
    {
        return $this->hasMany(ClientContact::className(), ['client_id' => 'id'])
            ->indexBy('id')
            ->orderBy([
                'type' => SORT_ASC,
                'id' => SORT_ASC,
            ]);
    }

    /**
     * @return array
     */
    public function getOfficialContact()
    {
        $contacts = ClientContact::find()
            ->select(['type', 'data'])
            ->andWhere(['client_id' => $this->id, 'is_official' => 1])
            ->groupBy(['type', 'data'])
            ->orderBy('id')
            ->asArray()
            ->all();

        $result = ['fax' => [], 'phone' => [], 'email' => []];
        foreach ($contacts as $contact) {
            $result[$contact['type']][] = $contact['data'];
        }

        return $result;
    }

    /**
     * @return ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(ClientContact::className(), ['client_id' => 'id']);
    }

    /**
     * @param bool $isActive
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAdditionalInn($isActive = true)
    {
        return $this->hasMany(ClientInn::className(), ['client_id' => 'id'])
            ->andWhere(['is_active' => (int)$isActive])
            ->all();
    }

    /**
     * @return ActiveQuery
     */
    public function getAdditionalPayAcc()
    {
        return $this->hasMany(ClientPayAcc::className(), ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLkClientSettings()
    {
        return $this->hasOne(LkClientSettings::className(), ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLkNoticeSetting()
    {
        return $this->hasMany(LkNoticeSetting::className(), ['client_id' => 'id']);
    }

    /**
     * @param string $name
     * @return array|bool
     */
    public function getOption($name)
    {
        $option = $this->getOptions()->where(['option' => $name])->all();
        return $option !== null ? ArrayHelper::getColumn($option, 'value') : false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptions()
    {
        return $this->hasMany(ClientAccountOptions::className(), ['client_account_id' => 'id']);
    }

    /**
     * @param float $originalSum
     * @param null $taxRate
     * @return array
     */
    public function convertSum($originalSum, $taxRate = null)
    {
        if ($taxRate === null) {
            $taxRate = $this->getTaxRate();
        }

        if ($this->price_include_vat) {
            $sum = round($originalSum, 2);
            $sum_tax = round($taxRate / (100.0 + $taxRate) * $sum, 2);
            $sum_without_tax = $sum - $sum_tax;
        } else {
            $sum_without_tax = round($originalSum, 2);
            $sum_tax = round($sum_without_tax * $taxRate / 100, 2);
            $sum = $sum_without_tax + $sum_tax;
        }

        return [$sum, $sum_without_tax, $sum_tax];
    }

    /**
     * @return int
     */
    public function getTaxRate()
    {
        if ($this->getHistoryVersionRequestedDate()) {
            return ClientContract::dao()->getEffectiveVATRate($this->contract);
        } else {
            return $this->effective_vat_rate;
        }
    }

    /**
     * @param string $date
     * @return Organization
     */
    public function getOrganization($date = '')
    {
        $date = $date ?: ($this->getHistoryVersionRequestedDate() ?: null);
        return $this->getContract($date)->getOrganization($date);
    }

    /**
     * AfterSave
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->sync1C();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @throws Exception
     */
    public function sync1C()
    {
        if (!defined('PATH_TO_ROOT')) {
            define("PATH_TO_ROOT", \Yii::$app->basePath . '/stat/');
        }

        if (!defined("NO_WEB")) {
            define("NO_WEB", 1);
        }

        include_once PATH_TO_ROOT . 'conf.php';

        if (!defined('SYNC1C_UT_SOAP_URL') || !SYNC1C_UT_SOAP_URL) {
            return;
        }

        try {
            if (($Client = \Sync1C::getClient()) !== false) {
                $Client->saveClientCards($this->id);
            } else {
                throw new Exception('Ошибка синхронизации с 1С.');
            }
        } catch (\Sync1CException $e) {
            $e->triggerError();
        }
    }

    /**
     * BeforeSave
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!$this->password) {
            $this->password = Utils::password_gen();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return ClientCounter
     */
    public function getBillingCounters()
    {
        return ClientCounter::getCounters($this->id);
    }

    /**
     * @return ClientCounter
     */
    public function getBillingCountersFastMass()
    {
        return ClientCounter::getCountersFastMass($this->id);
    }

    /**
     * Возвращает список кодов предупреждений
     *
     * @return array
     */
    public function getVoipWarnings()
    {
        $warnings = [];
        $counters = $this->billingCounters;

        if ($counters->isLocal) {
            if ($counters->isSyncError) {
                $warnings[self::WARNING_SYNC_ERROR] = 'Баланс не синхронизирован';
            } else {
                $warnings[self::WARNING_UNAVAILABLE_BILLING] = 'Сервер статистики недоступен. Данные о балансе и счетчиках могут быть неверными';
            }
        }

        try {
            /** @var Locks $locks */
            $locks = Locks::find()->where(['client_id' => $this->id])->one();

            if ($locks) {
                if ($locks->is_finance_block) {
                    $warnings[self::WARNING_FINANCE] = $locks->getLastLock('is_finance_block');
                }

                if ($locks->is_overran) {
                    $warnings[self::WARNING_OVERRAN] = $locks->getLastLock('is_overran');
                }

                if ($locks->is_mn_overran) {
                    $warnings[self::WARNING_MN_OVERRAN] = $locks->getLastLock('is_mn_overran');
                }
            }
        } catch (\Exception $e) {
            $warnings[self::WARNING_UNAVAILABLE_LOCKS] = 'Сервер статистики недоступен. Данные о блокировках недоступны';
        }

        $need_lock_limit_day = ($this->voip_credit_limit_day != 0 && -$counters->daySummary > $this->voip_credit_limit_day);
        $need_lock_credit = ($this->credit >= 0 && $counters->realtimeBalance + $this->credit < 0);

        if ($need_lock_limit_day) {
            $warnings[self::WARNING_LIMIT_DAY] = 'Превышен дневной лимит: ' . (-$counters->daySummary) . ' > ' . $this->voip_credit_limit_day;
        }

        if ($need_lock_credit || array_key_exists(self::WARNING_FINANCE, $warnings)) {
            $warnings[self::WARNING_CREDIT]
                = 'Превышен лимит кредита: ' .
                sprintf('%0.2f', $counters->realtimeBalance) . ' < -' . $this->credit .
                (
                isset($warnings[self::WARNING_FINANCE]) ?
                    ' (на уровне биллинга): ' . (new DateTimeWithUserTimezone($warnings[self::WARNING_FINANCE]->dt, $this->timezone))->format('H:i:s d.m.Y') :
                    ''
                );
        }

        if ($this->is_bill_pay_overdue) {
            $warnings[self::WARNING_BILL_PAY_OVERDUE] = 'Блокировка по неоплате счета';
        }

        return $warnings;
    }

    /**
     * @return array|null
     */
    public function getVoipNumbers()
    {
        return self::dao()->getClientVoipNumbers($this);
    }

    /**
     * DAO
     *
     * @return ClientAccountDao
     */
    public static function dao()
    {
        return ClientAccountDao::me();
    }

    /**
     * @return bool
     */
    public function getHasVoip()
    {
        return UsageVoip::find()->andWhere(['client' => $this->client])->actual()->exists();
    }

    /**
     * Это магазин
     *
     * @return bool
     */
    public function isMulty()
    {
        return in_array($this->id, self::$shopIds);
    }

    /**
     * Получение баланса ЛС
     *
     * @param bool $isWithExp
     * @return array
     */
    public function makeBalance($isWithExp = false)
    {
        $res = [
            'balance' => $this->billingCounters->realtimeBalance,
            'currency' => $this->currency,
        ];

        if ($isWithExp) {
            $res['id'] = $this->id;
            $res['credit'] = $this->credit;
            $res['expenditure'] = $this->billingCounters->getAttributes();
            $res['view_mode'] = $this->lk_balance_view_mode;
        }

        return $res;
    }

    /**
     * Это партнер
     *
     * @return bool
     */
    public function isPartner()
    {
        return $this->contract->isPartner();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLkSettings()
    {
        return $this->hasOne(LkClientSettings::className(), ['client_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getFlag()
    {
        return $this->hasOne(ClientFlag::className(), ['account_id' => 'id']);
    }

    /**
     * @return CounterInteropTrunk
     */
    public function getInteropCounter()
    {
        $counter = CounterInteropTrunk::findOne(['account_id' => $this->id]);
        if (!$counter) {
            $counter = new CounterInteropTrunk();
            $counter->account_id = $this->id;
        }

        return $counter;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return Html::a(
            Html::encode($this->client),
            $this->getUrl()
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/client/view', 'id' => $this->id]);
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDatetimeWithTimezone()
    {
        $timezoneName = $this->timezone_name;
        $timezone = new DateTimeZone($timezoneName);
        return (new DateTimeImmutable)
            ->setTimezone($timezone);

    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getNameAndContacts($delimiter = ' / ')
    {
        $names = [];
        $names[] = $this->getName($delimiter);

        $allContacts = $this->allContacts;
        foreach ($allContacts as $contact) {
            if (!$contact->data) {
                continue;
            }

            $names[] = $contact->type . ': ' . $contact->data . ' ' . $contact->comment;
        }

        return implode($delimiter, $names);
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getName($delimiter = ' / ')
    {
        return implode($delimiter, [
            $this->contract->contragent->name,
            'Договор № ' . $this->contract->number,
            $this->getAccountType() . ' № ' . "<b style=\"font-size:120%;\">{$this->id}</b>"
        ]);
    }

    /**
     * @return string "ЛС" или "УЛС"
     */
    public function getAccountType()
    {
        return ($this->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) ? 'УЛС' : 'ЛС';
    }

    /**
     * @return string "*ЛС № 12345"
     */
    public function getAccountTypeAndId()
    {
        return $this->getAccountType() . ' № ' . $this->id;
    }

    /**
     * Счетчики для дашборда в ЛК
     *
     * @return array
     */
    public function getDashboardCounters()
    {
        return StatisticDay::getCounters($this);
    }

    /**
     * Список уровней цен
     *
     * @return array
     */
    public static function getPriceLevels()
    {
        return array_combine(range(1, 9), range(1, 9));
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'client',
            $orderBy = ['id' => SORT_ASC],
            $where = []
        );
    }
}
