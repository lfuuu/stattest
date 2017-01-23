<?php
namespace app\models;

use app\classes\behaviors\ClientContractComments;
use app\classes\behaviors\ContractContragent;
use app\classes\behaviors\LkWizardClean;
use app\classes\behaviors\SetOldStatus;
use app\classes\media\ClientMedia;
use app\classes\model\HistoryActiveRecord;
use app\dao\ClientContractDao;
use app\helpers\DateTimeZoneHelper;
use app\helpers\SetFieldTypeHelper;
use yii\db\ActiveQuery;

/**
 * Class ClientContract
 *
 * @property int id
 * @property int super_id
 * @property int contragent_id
 * @property string number
 * @property int organization_id
 * @property string manager
 * @property string account_manager
 * @property int business_id
 * @property int business_process_id
 * @property int business_process_status_id
 * @property int contract_type_id
 * @property string state
 * @property string financial_type
 * @property string federal_district
 * @property string is_external
 * @property int is_lk_access
 * @property int is_partner_login_allow - флаг, разрешающий партнёру-родителю вход в ЛК текущего клиента
 * @property ClientContragent contragent
 * @property ClientAccount[] accounts
 * @property Organization $organization
 * @property ClientMedia mediaManager
 * @property ContractType contractType
 * @property Business business
 * @property BusinessProcess businessProcess
 * @property BusinessProcessStatus businessProcessStatus
 */
class ClientContract extends HistoryActiveRecord
{
    const STATE_UNCHECKED = 'unchecked';
    const STATE_OFFER = 'offer';
    const STATE_CHECKED_ORIGINAL = 'checked_original';
    const STATE_CHECKED_COPY = 'checked_copy';

    const FINANCIAL_TYPE_EMPTY = '';
    const FINANCIAL_TYPE_PROFITABLE = 'profitable';
    const FINANCIAL_TYPE_CONSUMABLES = 'consumables';
    const FINANCIAL_TYPE_YIELD_CONSUMABLE = 'yield-consumable';

    const IS_EXTERNAL = 'external';
    const IS_INTERNAL = 'internal';

    const IS_LK_ACCESS_YES = 1;
    const IS_LK_ACCESS_NO = 0;

    public $newClient = null;

    public static $states = [
        self::STATE_UNCHECKED => 'Не проверено',
        self::STATE_OFFER => 'Оферта',
        self::STATE_CHECKED_ORIGINAL => 'Оригинал',
        self::STATE_CHECKED_COPY => 'Копия',
    ];

    public static $districts = [
        'cfd' => 'ЦФО',
        'sfd' => 'ЮФО',
        'nwfd' => 'СЗФО',
        'dfo' => 'ДФО',
        'sfo' => 'СФО',
        'ufo' => 'УФО',
        'pfo' => 'ПФО',
    ];

    public static $financialTypes = [
        self::FINANCIAL_TYPE_EMPTY => 'Не задано',
        self::FINANCIAL_TYPE_PROFITABLE => 'Доходный',
        self::FINANCIAL_TYPE_CONSUMABLES => 'Расходный',
        self::FINANCIAL_TYPE_YIELD_CONSUMABLE => 'Доходно-расходный',
    ];

    public static $externalType = [
        self::IS_EXTERNAL => 'Внешний',
        self::IS_INTERNAL => 'Внутренний',
    ];

    public static $lkAccess = [
        self::IS_LK_ACCESS_YES => 'Да',
        self::IS_LK_ACCESS_NO => 'Нет',
    ];

    public
        $attributesAllowedForVersioning = [
        'contragent_id',
        'organization_id',
        'business_id',
        'business_process_id',
        'business_process_status_id',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'number' => '№ договора',
            'organization_id' => 'Организация',
            'manager' => 'Менеджер',
            'account_manager' => 'Аккаунт менеджер',
            'business_process_id' => 'Бизнес процесс',
            'business_process_status_id' => 'Статус бизнес процесса',
            'business_id' => 'Подразделение',
            'contract_type_id' => 'Тип договора',
            'state' => 'Статус договора',
            'financial_type' => 'Финансовый тип договора',
            'federal_district' => 'Федеральный округ (ФО)',
            'contragent_id' => 'Контрагент',
            'is_external' => 'Внешний договор',
            'is_lk_access' => 'Доступ к ЛК',
            'is_partner_login_allow' => 'Доступ партнеру в ЛК',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            'ContractContragent' => ContractContragent::className(),
            'LkWizardClean' => LkWizardClean::className(),
            'SetOldStatus' => SetOldStatus::className(),
            'ClientContractComments' => ClientContractComments::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\ClientContract::className(),
        ];
    }

    /**
     * @return \app\dao\ClientContractDao
     */
    public function dao()
    {
        return ClientContractDao::me();
    }

    /**
     * Документ, на основании которого действуем. Используется для выставления счетов и документов
     *
     * @param \DateTime|null $date
     * @return null|\app\models\ClientDocument
     */
    public function getContractInfo(\DateTime $date = null)
    {
        return ClientContractDao::me()->getContractInfo($this, $date);
    }

    /**
     * @return string
     */
    public function getManagerName()
    {
        $m = User::findByUsername($this->manager);
        return ($m) ? $m->name : $this->manager;
    }

    /**
     * @return string
     */
    public function getAccountManagerName()
    {
        $m = User::findByUsername($this->account_manager);
        return ($m) ? $m->name : $this->account_manager;
    }

    /**
     * @return string
     */
    public function getManagerColor()
    {
        $m = User::findByUsername($this->manager);
        return ($m) ? $m->color : '';
    }

    /**
     * @return string
     */
    public function getAccountManagerColor()
    {
        $m = User::findByUsername($this->account_manager);
        return ($m) ? $m->color : '';
    }

    /**
     * @return ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBusinessProcess()
    {
        return $this->hasOne(BusinessProcess::className(), ['id' => 'business_process_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBusinessProcessStatus()
    {
        return $this->hasOne(BusinessProcessStatus::className(), ['id' => 'business_process_status_id']);
    }

    /**
     * @param string $date
     * @return Organization
     */
    public function getOrganization($date = '')
    {
        $date = $date ?: ($this->getHistoryVersionRequestedDate() ?: date(DateTimeZoneHelper::DATE_FORMAT));
        /** @var Organization $organization */
        $organization = Organization::find()->byId($this->organization_id)->actual($date)->one();
        return $organization;
    }

    /**
     * @return ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(ClientContractComment::className(), ['contract_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSuper()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }

    /**
     * @return null|ActiveQuery
     */
    public function getContractType()
    {
        if ($this->business_id != Business::OPERATOR && $this->business_id != Business::PARTNER) {
            return null;
        }

        return $this->hasOne(ContractType::className(), ['id' => 'contract_type_id']);
    }

    /**
     * @param string $date
     * @return ClientContragent
     */
    public function getContragent($date = '')
    {
        $date = $date ?: ($this->getHistoryVersionRequestedDate() ?: null);

        $contragent = ClientContragent::findOne($this->contragent_id);
        if ($contragent && $date) {
            $contragent->loadVersionOnDate($date);
        }

        return $contragent;
    }

    /**
     * @param bool $isFromHistory
     * @return ClientAccount[]|array
     */
    public function getAccounts($isFromHistory = true)
    {
        $models = ClientAccount::findAll(['contract_id' => $this->id]);

        if (!$isFromHistory) {
            return $models;
        }

        foreach ($models as &$model) {
            if ($model && $historyDate = $this->getHistoryVersionRequestedDate()) {
                $model->loadVersionOnDate($historyDate);
            }
        }

        return $models;
    }

    /**
     * @return ClientMedia
     */
    public function getMediaManager()
    {
        return new ClientMedia($this);
    }

    /**
     * @return ClientDocument[]
     */
    public function getAllDocuments()
    {
        return ClientDocument::find()
            ->andWhere(['contract_id' => $this->id, 'type' => ['agreement', 'contract']])
            ->all();
    }

    /**
     * @return ClientDocument
     */
    public function getDocument()
    {
        /** @var ClientDocument $clientDocument */
        $clientDocument = ClientDocument::find()
            ->contractId($this->id)
            ->active()
            ->contract()
            ->orderBy(['id' => SORT_DESC])
            ->one();
        return $clientDocument;
    }

    /**
     * @param string $usageType
     * @return ClientContractReward[]
     */
    public function getRewards($usageType = null)
    {
        $link = $this->hasMany(ClientContractReward::className(), ['contract_id' => 'id']);

        if (!is_null($usageType)) {
            $link->andWhere(['usage_type' => $usageType]);
        }

        $link->orderBy(['actual_from' => SORT_DESC]);

        return $link->all();
    }

    /**
     * @return array
     */
    public function getFederalDistrictAsArray()
    {
        return SetFieldTypeHelper::getFieldValue($this, 'federal_district');
    }

    /**
     * @param bool $runValidation
     * @param string[] $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (is_array($this->federal_district)) {
            $this->federal_district = SetFieldTypeHelper::generateFieldValue($this, 'federal_district',
                $this->federal_district, false);
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\base\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $contragent = ClientContragent::findOne($this->contragent_id);
            $client = new ClientAccount();
            $client->contract_id = $this->id;
            $client->super_id = $this->super_id;
            $client->country_id = $contragent->country_id;
            $client->currency = Currency::defaultCurrencyByCountryId($contragent->country_id);
            $client->is_active = 0;
            $client->client = '';
            $client->sale_channel = 0;
            $client->consignee = '';
            $client->validate();
            $client->save();

            $client->client = 'id' . $client->id;
            $client->save();

            $this->newClient = $client;
            $this->number = (string)$client->id;
            $this->save();
        }

        foreach ($this->getAccounts() as $account) {
            $account->sync1C();
        }
    }

    /**
     * @return array
     */
    public function statusesForChange()
    {
        if (!$this->state || $this->state == self::STATE_UNCHECKED || \Yii::$app->user->can('clients.changeback_contract_state')) {
            return self::$states;
        }

        if ($this->state == self::STATE_CHECKED_ORIGINAL) {
            return [self::STATE_CHECKED_ORIGINAL => self::$states[self::STATE_CHECKED_ORIGINAL]];
        }

        if ($this->state == self::STATE_CHECKED_COPY) {
            return [
                self::STATE_CHECKED_COPY => self::$states[self::STATE_CHECKED_COPY],
                self::STATE_CHECKED_ORIGINAL => self::$states[self::STATE_CHECKED_ORIGINAL],
            ];
        }

        if ($this->state == self::STATE_OFFER) {
            return [
                self::STATE_OFFER => self::$states[self::STATE_OFFER],
                self::STATE_CHECKED_COPY => self::$states[self::STATE_CHECKED_COPY],
                self::STATE_CHECKED_ORIGINAL => self::$states[self::STATE_CHECKED_ORIGINAL],
            ];
        }

        return [];
    }

    /**
     * @return bool
     */
    public function isPartner()
    {
        return $this->business_id == Business::PARTNER;
    }

    /**
     * @return int
     */
    public function isPartnerAgent()
    {
        return $this->contragent->partner_contract_id;
    }

}
