<?php
namespace app\models;

use app\classes\Assert;
use app\classes\BillContract;
use app\classes\Html;
use app\classes\model\HistoryActiveRecord;
use app\classes\Utils;
use app\classes\voip\VoipStatus;
use app\dao\ClientAccountDao;
use app\models\billing\Locks;
use app\queries\ClientAccountQuery;
use DateTimeZone;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $client
 * @property int $super_id
 * @property int $contract_id
 * @property int $country_id
 * @property string $currency
 * @property string $nal
 * @property int $balance
 * @property int $credit
 * @property int $voip_credit_limit_day
 * @property int $business_id
 * @property int $price_include_vat
 * @property int $is_active
 * @property int $region
 * @property string $address_post
 * @property string $site_name
 * @property string $timezone_name
 *
 * @property Currency $currencyModel
 * @property ClientSuper $superClient
 * @property ClientContractComment $lastComment
 * @property Country $country
 * @property Region $accountRegion
 * @property DateTimeZone $timezone
 * @property LkClientSettings $lkClientSettings
 * @property LkNoticeSetting $lkNoticeSetting
 * @property ClientContact $contact
 * @property ClientContract $contract
 * @property ClientContragent $contragent
 * @property Organization organization
 * @property User userAccountManager
 * @property LkWizardState lkWizardState
 * @property ClientCounter billingCounters
 * @property ClientCounter billingCountersFastMass
 * @method static ClientAccount findOne($condition)
 */
class ClientAccount extends HistoryActiveRecord
{
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
    const DEFAULT_VOIP_CREDIT_LIMIT_DAY = 1000;
    const DEFAULT_VOIP_IS_DAY_CALC = 1;
    const DEFAULT_CREDIT = 0;

    public $client_orig = '';

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

    public static $shopIds = [14050, 18042];

    /** Virtual variables */
    public $payment_info;
    /** /Virtual variables */

    private $_lastComment = false;

    /*For old stat*/

    public static function tableName()
    {
        return 'clients';
    }

    public function rules()
    {
        $rules = [];
        $rules[] = ['voip_credit_limit_day', 'default', 'value' => self::DEFAULT_VOIP_CREDIT_LIMIT_DAY];
        $rules[] = ['voip_is_day_calc', 'default', 'value' => self::DEFAULT_VOIP_IS_DAY_CALC];
        $rules[] = ['region', 'default', 'value' => self::DEFAULT_REGION];
        $rules[] = ['credit', 'default', 'value' => self::DEFAULT_CREDIT];

        return $rules;
    }

    public function behaviors()
    {
        return [
            'AccountPriceIncludeVat' => \app\classes\behaviors\AccountPriceIncludeVat::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            'SetOldStatus' => \app\classes\behaviors\SetOldStatus::className(),
            'ActaulizeClientVoip' => \app\classes\behaviors\ActualizeClientVoip::className(),
            'ClientAccountComments' => \app\classes\behaviors\ClientAccountComments::className(),
            'ClientAccountEvent' => \app\classes\behaviors\important_events\ClientAccount::className(),
        ];
    }

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
            'voip_credit_limit' => 'Телефония, лимит использования (месяц)',
            'voip_disabled' => 'Выключить телефонию (МГ, МН, Местные мобильные)',
            'voip_credit_limit_day' => 'Телефония, лимит использования (день)',
            'balance' => 'Баланс',
            'voip_is_day_calc' => 'Пересчет дневного лимита',
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
            'anti_fraud_disabled' => 'Отключен анти-фрод'
        ];
    }

    public static function dao()
    {
        return ClientAccountDao::me();
    }

    public static function find()
    {
        return new ClientAccountQuery(get_called_class());
    }


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

    public function getType()
    {
        return ($this->contract->contragent->legal_type != 'person') ? 'org' : 'person';
    }

    public function getFirma()
    {
        return $this->contract->organization->firma;
    }

    public function getManager()
    {
        return $this->contract->manager;
    }

    public function getManager_name()
    {
        return User::find()->where(['user' => $this->contract->manager])->one()->name;;
    }

    public function getNumber()
    {
        return $this->contract->number;
    }

    public function getBusiness_process_id()
    {
        return $this->contract->business_process_id;
    }

    public function getBusiness_process_status_id()
    {
        return $this->contract->business_process_status_id;
    }

    public function getBusinessId()
    {
        return $this->contract->business_id;
    }

    public function getAccount_manager()
    {
        return $this->contract->account_manager;
    }

    public function getCompany()
    {
        return $this->contract->contragent->name;
    }

    public function getCompany_full()
    {
        return $this->contract->contragent->name_full;
    }

    public function getAddress_jur()
    {
        return $this->contract->contragent->address_jur;
    }

    public function getInn()
    {
        return $this->contract->contragent->inn;
    }

    public function getKpp()
    {
        return $this->contract->contragent->kpp;
    }

    public function getSigner_position()
    {
        return $this->contract->contragent->position;
    }

    public function getSigner_positionV()
    {
        return $this->contract->contragent->positionV;
    }

    public function getSigner_name()
    {
        return $this->contract->contragent->fio;
    }

    public function getSigner_nameV()
    {
        return $this->contract->contragent->fioV;
    }

    public function getOgrn()
    {
        return $this->contract->contragent->ogrn;
    }

    public function getOkpo()
    {
        return $this->contract->contragent->okpo;
    }

    public function getOpf()
    {
        return $this->contract->contragent->opf_id;
    }

    public function getOkvd()
    {
        return $this->contract->contragent->okvd;
    }


    public function getChannelName()
    {
        return $this->sale_channel ? SaleChannelOld::getList()[$this->sale_channel] : '';
    }


    /**
     * @return ClientContract
     */
    public function getContract()
    {
        $contract = ClientContract::findOne($this->contract_id);
        if ($contract && $this->getHistoryVersionRequestedDate()) {
            $contract->loadVersionOnDate($this->getHistoryVersionRequestedDate());
        }
        return $contract;
    }

    /**
     * @return Business
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
     * @return Country
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * @return Region
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
        return BillContract::getLastContract($this->id, $date);
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

    public function getStatusName()
    {
        /** @var $this ClientAccount */
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['name']
                : $this->status;
    }

    public function getStatusColor()
    {
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['color']
                : '';
    }

    public function getLastComment()
    {
        if ($this->_lastComment === false) {
            $this->_lastComment =
                ClientContractComment::find()
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
     * @return Organization
     */
    public function getOrganization($date = '')
    {
        return $this->contract->getOrganization($date);
    }

    public function getAllContacts()
    {
        return $this->hasMany(ClientContact::className(), ['client_id' => 'id']);
    }

    public function getOfficialContact()
    {
        $contacts = ClientContact::find()
            ->select(['type', 'data'])
            ->andWhere(['client_id' => $this->id, 'is_official' => 1, 'is_active' => 1])
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

    public function getAdditionalInn($isActive = true)
    {
        return $this->hasMany(ClientInn::className(),
            ['client_id' => 'id'])->andWhere(['is_active' => (int)$isActive])->all();
    }

    public function getAdditionalPayAcc()
    {
        return $this->hasMany(ClientPayAcc::className(), ['client_id' => 'id']);
    }

    public function getTaxRate()
    {
        $organization = $this->getOrganization();
        Assert::isObject($organization, 'Organization not found');

        return $organization->vat_rate;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptions()
    {
        return $this->hasMany(ClientAccountOptions::className(), ['client_account_id' => 'id']);
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
     * @param $name
     * @return array
     */
    public function getOption($name)
    {
        return ArrayHelper::getColumn($this->getOptions()->where(['option' => $name])->all(), 'value');
    }

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

    public function sync1C()
    {
        if (!defined('PATH_TO_ROOT')) {
            define("PATH_TO_ROOT", \Yii::$app->basePath . '/stat/');
        }
        if (!defined("NO_WEB")) {
            define("NO_WEB", 1);
        }

        require_once PATH_TO_ROOT . 'conf.php';

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

    public function afterSave($insert, $changedAttributes)
    {
        $this->sync1C();
        parent::afterSave($insert, $changedAttributes);
    }

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
     * @return array
     */
    public function getVoipWarnings()
    {
        $warnings = [];
        $counters = $this->billingCounters;

        if ($counters->isLocal) {
            $warnings['unavailable.billing'] = 'Сервер статистики недоступен. Данные о балансе и счетчиках могут быть неверными';
        }

        try {
            $locks = Locks::find()->where(['client_id' => $this->id])->one();

            if ($locks) {
                if ($locks->voip_auto_disabled_local) {
                    $warnings['voip.auto_disabled_local'] = 'ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (МГ, МН, Местные мобильные)';
                }
                if ($locks->voip_auto_disabled) {
                    $warnings['voip.auto_disabled'] = 'ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (Полная блокировка)';
                }
            }
        } catch (\Exception $e) {
            $warnings['unavailable.locks'] = 'Сервер статистики недоступен. Данные о блокировках недоступны';
        }

        $need_lock_limit_day = ($this->voip_credit_limit_day != 0 && -$counters->daySummary > $this->voip_credit_limit_day);
        $need_lock_limit_month = ($this->voip_credit_limit != 0 && -$counters->monthSummary > $this->voip_credit_limit);
        $need_lock_credit = ($this->credit >= 0 && $counters->realtimeBalance + $this->credit < 0);

        if ($need_lock_limit_day) {
            $warnings['lock.limit_day'] = 'Превышен дневной лимит: ' . (-$counters->daySummary) . ' > ' . $this->voip_credit_limit_day;
        }
        if ($need_lock_limit_month) {
            $warnings['lock.limit_month'] = 'Превышен месячный лимит: ' . (-$counters->monthSummary) . ' > ' . $this->voip_credit_limit;
        }
        if ($need_lock_credit) {
            $warnings['lock.credit'] = 'Превышен лимит кредита: ' . $counters->realtimeBalance . ' < -' . $this->credit;
        }

        return $warnings;
    }

    public function getVoipNumbers()
    {
        return self::dao()->getClientVoipNumbers($this);
    }

    public function getHasVoip()
    {
        return UsageVoip::find()->andWhere(['client' => $this->client])->actual()->exists();
    }

    public function isMulty()
    {
        return in_array($this->id, self::$shopIds);
    }

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

    public function isPartner()
    {
        return $this->contract->business_id == Business::PARTNER;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLkSettings()
    {
        return $this->hasOne(LkClientSettings::className(), ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
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
    public function getUrl()
    {
        return Url::to(['/client/view', 'id' => $this->id]);
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
}
