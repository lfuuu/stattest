<?php
namespace app\models;

use app\classes\Assert;
use app\dao\ClientGridSettingsDao;
use app\classes\BillContract;
use DateTimeZone;
use yii\base\Exception;
use yii\db\ActiveRecord;
use app\dao\ClientAccountDao;
use app\queries\ClientAccountQuery;
use app\models\ClientContact;
use yii\helpers\ArrayHelper;


/**
 * @property int $id
 * @property string $client
 * @property string $currency
 * @property string $nal
 * @property int $nds_zero
 * @property int $contract_type_id
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
 */
class ClientAccount extends ActiveRecord
{
    const STATUS_INCOME = 'income';

    public $client_orig = '';
    public $historyVersionDate = null;

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

    public static $contractTypes = [
        'full' => 'Полный (НДС 18%)',
        'simplified' => 'без НДС'
    ];

    private $_lastComment = false;
/*For old stat*/
    public function getType()
    {
        return $this->contract->contragent->legal_type;
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

    public function getContract_type_id()
    {
        return $this->contract->contract_type_id;
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

    public function getNds_zero()
    {
        return $this->contract->contragent->tax_regime == 'full' ? 0: 1;
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
        return $this->contract->contragent->opf;
    }

    public function getOkvd()
    {
        return $this->contract->contragent->okvd;
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

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            return parent::save($runValidation = true, $attributeNames = null);
        }
        else {
            if (substr(php_sapi_name(), 0, 3) == 'cli' || !\Yii::$app->request->post('deferred-date') || \Yii::$app->request->post('deferred-date') === date('Y-m-d')) {
                return parent::save($runValidation = true, $attributeNames = null);
            } else {
                $behaviors = $this->behaviors;
                unset($behaviors['HistoryVersion']);
                $behaviors = array_keys($behaviors);
                foreach ($behaviors as $behavior)
                    $this->detachBehavior($behavior);
                $this->beforeSave(false);
            }
            return true;
        }
    }

    public function behaviors()
    {
        return [
            'HistoryVersion' => \app\classes\behaviors\HistoryVersion::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            'SetOldStatus' => \app\classes\behaviors\SetOldStatus::className(),
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
            'currency' => 'Валюта',
            'account_manager' => 'Ак. менеджер',
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
        return ClientContract::findOne($this->contract_id)->loadVersionOnDate($this->historyVersionDate);
    }

    public function getContractType()
    {
        return $this->hasOne(ClientContractType::className(), ['id' => 'contract_type_id']);
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
        return LkWizardState::findOne($this->contract->id);
    }

    /**
     * @return ClientContragent
     */
    public function getContragent()
    {
        return $this->getContract()->getContragent()->loadVersionOnDate($this->historyVersionDate);
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

    public function getOrganization()
    {
        return $this->contract->getOrganization();
    }

    public function getDefaultTaxId()
    {
        return $this->nds_zero ? 0 : $this->contract->getOrganization()->vat_rate;
    }

    public function getAllContacts()
    {
        return $this->hasMany(ClientContact::className(), ['client_id' => 'id']);
    }

    public function getOfficialContact()
    {
        $res = [];
        $contacts = ClientContact::find()
            ->andWhere(['client_id' => $this->id, 'is_official'=>1, 'is_active' => 1])
            ->groupBy(['type'])
            ->all();
        return ArrayHelper::map($contacts, 'type', 'data');
    }

    public function getBpStatuses()
    {
        $processes = [];
        foreach (ClientBP::find()->orderBy("sort")->all() as $b) {
            $processes[] = ["id" => $b->id, "up_id" => $b->client_contract_id, "name" => $b->name];
        }

        $statuses = [];
        foreach (ClientGridSettingsDao::me()->getAllByParams(['show_as_status' => true]) as $s) {
            $statuses[] = ["id" => $s['id'], "name" => $s['name'], "up_id" => $s['grid_business_process_id']];
        }

        return ["processes" => $processes, "statuses" => $statuses];
    }

    public function getAdditionalInn($isActive = true)
    {
        return $this->hasMany(ClientInn::className(), ['client_id' => 'id'])->andWhere(['is_active' => (int) $isActive])->all();
    }

    public function getAdditionalPayAcc()
    {
        return $this->hasMany(ClientPayAcc::className(), ['client_id' => 'id']);
    }

    /**
     * @return $this
     */
    public function loadVersionOnDate($date)
    {
        return HistoryVersion::loadVersionOnDate($this, $date);
    }

    public function getTaxRate()
    {
        if ($this->nds_zero) {
            return 0;
        }

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

    public function getServerPbxId($region)
    {
        return self::dao()->getServerPbxId($this, $region);
    }

}
