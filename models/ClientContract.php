<?php
namespace app\models;

use app\classes\FileManager;
use app\dao\ClientGridSettingsDao;
use app\forms\client\AccountEditForm;
use yii\db\ActiveRecord;

/**
 * @property Organization $organization
 * @property
 */
class ClientContract extends ActiveRecord
{
    const CONTRACT_TYPE_MULTY = 5;

    public $newClient = null;
    public $historyVersionDate = null;

    public static $states = [
        'unchecked' => 'Не проверено',
        'checked_original' => 'Оригинал',
        'checked_copy' => 'Копия',
        'external' => 'Внешний',
    ];

    public static function tableName()
    {
        return 'client_contract';
    }

    public function attributeLabels()
    {
        return [
            'number' => '№ договора',
            'organization_id' => 'Организация',
            'manager' => 'Менеджер',
            'account_manager' => 'Аккаунт менеджер',
            'business_process_id' => 'Бизнес процесс',
            'business_process_status_id' => 'Статус бизнес процесса',
            'contract_type_id' => 'Тип договора',
            'state' => 'Статус договора',
            'contragent_id' => 'Контрагент',
        ];
    }

    public function behaviors()
    {
        return [
            'HistoryVersion' => \app\classes\behaviors\HistoryVersion::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            'ContractContragent' => \app\classes\behaviors\ContractContragent::className(),
            'LkWizardClean' => \app\classes\behaviors\LkWizardClean::className(),
            'SetOldStatus' => \app\classes\behaviors\SetOldStatus::className(),
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            return parent::save($runValidation = true, $attributeNames = null);
        } else {
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

    public function getManagerName()
    {
        $m = User::findByUsername($this->manager);
        return ($m) ? $m->name : $this->manager;
    }

    public function getAccountManagerName()
    {
        $m = User::findByUsername($this->account_manager);
        return ($m) ? $m->name : $this->account_manager;
    }

    public function getManagerColor()
    {
        $m = User::findByUsername($this->manager);
        return ($m) ? $m->color : '';
    }

    public function getAccountManagerColor()
    {
        $m = User::findByUsername($this->account_manager);
        return ($m) ? $m->color : '';
    }

    public function getBusinessProcess()
    {
        $m = $this->hasOne(ClientGridBussinesProcess::className(), ['id' => 'business_process_id'])->one();
        return ($m) ? $m->name : $this->business_process_id;
    }

    public function getContractType()
    {
        $m = ClientContractType::findOne($this->contract_type_id);
        return $m ? $m->name : $this->contract_type_id;
    }

    public function getBusinessProcessStatus()
    {
        return ClientGridSettingsDao::me()->getGridByBusinessProcessStatusId($this->business_process_status_id, false);
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        $date = $this->historyVersionDate ? $this->historyVersionDate : date('Y-m-d');
        $organization = Organization::find()
            ->andWhere(['organization_id' => $this->organization_id])
            ->andWhere(['<=', 'actual_from', $date])
            ->andWhere(['>=', 'actual_to', $date])
            ->one();
        return $organization;
    }

    /**
     * @return array|ClientContractComment[]
     */
    public function getComments()
    {
        return $this->hasMany(ClientContractComment::className(), ['contract_id' => 'id']);
    }

    /**
     * @return ClientSuper
     */
    public function getSuper()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }

    /**
     * @return ClientContragent
     */
    public function getContragent()
    {
        return ClientContragent::findOne($this->contragent_id)->loadVersionOnDate($this->historyVersionDate);
    }

    /**
     * @return array|ClientAccount[]
     */
    public function getAccounts()
    {
        $models = $this->hasMany(ClientAccount::className(), ['contract_id' => 'id'])->all();
        foreach($models as &$model)
        {
            $model = $model->loadVersionOnDate($this->historyVersionDate);
        }
        return $models;
    }

    /**
     * @return array|ClientFile[]
     */
    public function getAllFiles()
    {
        return $this->hasMany(ClientFile::className(), ['contract_id' => 'id']);
    }

    /**
     * @return FileManager
     */
    public function getFileManager()
    {
        return FileManager::create($this->id);
    }

    public function getAllDocuments()
    {
        return ClientDocument::find()
            ->andWhere(['contract_id' => $this->id, 'type' => ['agreement','contract']])
            ->all();
    }

    public function getDocument()
    {
        return ClientDocument::find()
            ->andWhere(['contract_id' => $this->id, 'type' => 'contract', 'is_active' => 1])
            ->orderBy('id DESC')
            ->one();
    }

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
            $client->validate();
            $client->save();
            $client->client = 'id' . $client->id;
            $client->save();
            $this->newClient = $client;
            $this->number = (string)$client->id;
            $this->save();
        }

        foreach($this->getAccounts() as $account)
            $account->sync1C();
    }

    /**
     * @return $this
     */
    public function loadVersionOnDate($date)
    {
        return HistoryVersion::loadVersionOnDate($this, $date);
    }

    public function statusesForChange()
    {
        if(!$this->state || $this->state == 'unchecked' || \Yii::$app->user->can('clients.changeback_contract_state'))
            return self::$states;

        if($this->state == 'checked_original')
            return ['checked_original' =>self::$states['checked_original']];

        if($this->state == 'checked_copy')
            return [
                'checked_copy' =>self::$states['checked_copy'],
                'checked_original' =>self::$states['checked_original'],
            ];

        return [];
    }
}
