<?php
namespace app\models;

use app\classes\Assert;
use app\classes\model\HistoryActiveRecord;
use app\classes\voip\VoipStatus;
use app\classes\BillContract;
use DateTimeZone;
use yii\base\Exception;
use app\dao\ClientAccountDao;
use app\queries\ClientAccountQuery;
use app\classes\Utils;
/**
 * @property int $id
 * @property string $client
 * @property string $currency
 * @property string $nal
 * @property int $business_id
 * @property int $price_include_vat

 * @property ClientSuper $superClient
 * @property ClientContractComment $lastComment
 * @property Country $country
 * @property Region $accountRegion
 * @property DateTimeZone $timezone
 *
 * @property ClientContact $contract
 * @method static ClientAccount findOne($condition)
 * @property
 *
 */
class ClientAccount extends HistoryActiveRecord
{

    const STATUS_INCOME = 'income';

    const DEFAULT_REGION = Region::MOSCOW;
    const DEFAULT_VOIP_CREDIT_LIMIT_DAY = 1000;
    const DEFAULT_VOIP_IS_DAY_CALC = 1;
    const DEFAULT_CREDIT = 0;

    public $client_orig = '';

    public static $statuses = array(
        'negotiations'        => array('name'=>'в стадии переговоров','color'=>'#C4DF9B'),
        'testing'             => array('name'=>'тестируемый','color'=>'#6DCFF6'),
        'connecting'          => array('name'=>'подключаемый','color'=>'#F49AC1'),
        'work'                => array('name'=>'включенный','color'=>''),
        'closed'              => array('name'=>'отключенный','color'=>'#FFFFCC'),
        'tech_deny'           => array('name'=>'тех. отказ','color'=>'#996666'),
        'telemarketing'       => array('name'=>'телемаркетинг','color'=>'#A0FFA0'),
        'income'              => array('name'=>'входящие','color'=>'#CCFFFF'),
        'deny'                => array('name'=>'отказ','color'=>'#A0A0A0'),
        'debt'                => array('name'=>'отключен за долги','color'=>'#C00000'),
        'double'              => array('name'=>'дубликат','color'=>'#60a0e0'),
        'trash'               => array('name'=>'мусор','color'=>'#a5e934'),
        'move'                => array('name'=>'переезд','color'=>'#f590f3'),
        'suspended'           => array('name'=>'приостановленные','color'=>'#C4a3C0'),
        'denial'              => array('name'=>'отказ/задаток','color'=>'#00C0C0'),
        'once'                => array('name'=>'Интернет Магазин','color'=>'silver'),
        'reserved'            => array('name'=>'резервирование канала','color'=>'silver'),
        'blocked'             => array('name'=>'временно заблокирован','color'=>'silver'),
        'distr'               => array('name'=>'Поставщик','color'=>'yellow'),
        'operator'            => array('name'=>'Оператор','color'=>'lightblue')
    );

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


    public function rules()
    {
        $rules = [];
        $rules[] = ['voip_credit_limit_day', 'default', 'value' => self::DEFAULT_VOIP_CREDIT_LIMIT_DAY];
        $rules[] = ['voip_is_day_calc', 'default', 'value' => self::DEFAULT_VOIP_IS_DAY_CALC];
        $rules[] = ['region', 'default', 'value' => self::DEFAULT_REGION];
        $rules[] = ['credit', 'default', 'value' => self::DEFAULT_CREDIT];

        return $rules;
    }

    public function getType()
    {
        return ($this->contract->contragent->legal_type !='person') ? 'org' : 'person';
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
        return $this->sale_channel ? SaleChannel::getList()[$this->sale_channel] : '';
    }

/**************/
    public static function tableName()
    {
        return 'clients';
    }

    public static function dao()
    {
        return ClientAccountDao::me();
    }

    public static function find()
    {
        return new ClientAccountQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            'AccountPriceIncludeVat' => \app\classes\behaviors\AccountPriceIncludeVat::className(),
            'HistoryChanges' =>         \app\classes\behaviors\HistoryChanges::className(),
            'SetOldStatus' =>           \app\classes\behaviors\SetOldStatus::className(),
            'ActaulizeClientVoip' =>    \app\classes\behaviors\ActaulizeClientVoip::className(),
            'ClientAccountComments' =>  \app\classes\behaviors\ClientAccountComments::className(),
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
            'voip_is_day_calc' => 'Включить пересчет дневного лимита',
            'region' => 'Регион',
            'mail_print' => 'Массовая печать конвертов и закрывающих документов',
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
            'lk_balance_view_mode' => 'Отображение баланса в ЛК',
        ];
    }

    public function getSuperClient()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }


    /**
     * @return ClientContract
     */
    public function getContract()
    {
        $contract = ClientContract::findOne($this->contract_id);
        if($contract && $this->getHistoryVersionRequestedDate())
            $contract->loadVersionOnDate($this->getHistoryVersionRequestedDate());
        return $contract;
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }

    public function getRegionName()
    {
        return $this->accountRegion ? $this->accountRegion->name : $this->region;
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getAccountRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function getUserManager()
    {
        return User::findOne(['user' => $this->contract->manager]);
    }

    public function getLastContract($date = null)
    {
        return BillContract::getLastContract($this->id, $date);
    }

    public function getUserAccountManager()
    {
        return User::findOne(['user' => $this->contract->account_manager]);
    }

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
            ->andWhere(['client_id' => $this->id, 'is_official'=>1, 'is_active' => 1])
            ->groupBy(['type', 'data'])
            ->orderBy('id')
            ->asArray()
            ->all();

        $result = ['fax'=>[],'phone'=>[],'email'=>[]];
        foreach ($contacts as $contact) {
            $result[$contact['type']][] = $contact['data'];
        }

        return $result;
    }

    public function getAdditionalInn($isActive = true)
    {
        return $this->hasMany(ClientInn::className(), ['client_id' => 'id'])->andWhere(['is_active' => (int) $isActive])->all();
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
        if (!defined('PATH_TO_ROOT'))
        {
            define("PATH_TO_ROOT", \Yii::$app->basePath . '/stat/');
        }
        if (!defined("NO_WEB"))
            define("NO_WEB", 1);
            
        require_once PATH_TO_ROOT . 'conf.php';

        if(!defined('SYNC1C_UT_SOAP_URL') || !SYNC1C_UT_SOAP_URL)
            return;

        try {
            if (($Client = \Sync1C::getClient())!==false)
                $Client->saveClientCards($this->id);
            else
                throw new Exception('Ошибка синхронизации с 1С.');
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
        if (!$this->password)
            $this->password = Utils::password_gen();

        return parent::beforeSave($insert);
    }


    public function getRealtimeBalance()
    {
        return VoipStatus::create($this)->getRealtimeBalance();
    }

    public function getVoipWarnings()
    {
        return VoipStatus::create($this)->getWarnings();
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
}
