<?php
namespace app\models;

use app\classes\FileManager;
use app\forms\client\AccountEditForm;
use yii\db\ActiveRecord;

class ClientContract extends ActiveRecord
{
    public $newClient = null;
    public $historyVersionDate = null;

    public static $states = [
        'unchecked' => 'Не проверено',
        'checked_original' => 'Оригинал',
        'checked_copy' => 'Копия',
    ];

    public static function tableName()
    {
        return 'client_contract';
    }

    public function attributeLabels()
    {
        return [
            'number' => '№ договора',
            'organization' => 'Организация',
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
            'LkWizardClean' => \app\classes\behaviors\LkWizardClean::className(),
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
        $m = $this->hasOne(ClientGridSettings::className(), ['id' => 'business_process_status_id'])->one();
        return ($m) ? ['name' => $m->name, 'color' => $m->color] : ['name' => $this->business_process_status_id, 'color' => ''];
    }

    public function getOrganizationName()
    {
        $m = $this->hasOne(Organization::className(), ['id' => 'organization'])->one();
        return ($m) ? $m->name : $this->organization;
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
        return $this->hasMany(ClientDocument::className(), ['contract_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $client = new ClientAccount();
            $client->contract_id = $this->id;
            $client->super_id = $this->super_id;
            $client->validate();
            $client->save();
            $this->newClient = $client;
            $this->number = $client->id;
            $this->save();
        }
    }

    /**
     * @return $this
     */
    public function loadVersionOnDate($date)
    {
        return HistoryVersion::loadVersionOnDate($this, $date);
    }
}
