<?php
namespace app\models;

use app\forms\client\AccountEditForm;
use yii\db\ActiveRecord;

class ClientContract extends ActiveRecord
{
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
            'contract_type_id' => 'Тип',
            'state' => 'Статус договора',
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

    public function getClients()
    {
        return $this->hasMany(ClientAccount::className(), ['contract_id' => 'id']);
    }

    public function getManagerName()
    {
        //$m = $this->hasOne(User::className(), ['user' => 'manager']);
        $m = User::findByUsername($this->manager);
        return ($m) ? $m->name : $this->manager;
    }

    public function getAccountManagerName()
    {
        //$m = $this->hasOne(User::className(), ['user' => 'account_manager']);
        $m = User::findByUsername($this->account_manager);
        return ($m) ? $m->name : $this->account_manager;
    }

    public function getManagerColor()
    {
        //$m = $this->hasOne(User::className(), ['user' => 'manager']);
        $m = User::findByUsername($this->manager);
        return ($m) ? $m->color : $this->manager;
    }

    public function getAccountManagerColor()
    {
        //$m = $this->hasOne(User::className(), ['user' => 'account_manager']);
        $m = User::findByUsername($this->account_manager);
        return ($m) ? $m->color : $this->account_manager;
    }

    public function getBusinessProcess()
    {
        $m = $this->hasOne(ClientGridBussinesProcess::className(), ['id' => 'business_process_id'])->one();
        return ($m) ? $m->name : $this->business_process_id;
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

    public function getComments()
    {
        return $this->hasMany(ClientContractComment::className(), ['contract_id' => 'id']);
    }

    public function getSuper()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }

    public function getContragent()
    {
        return $this->hasOne(ClientContragent::className(), ['id' => 'contragent_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($insert){
            $client = new AccountEditForm(['contract_id' => $this->id]);
            $client->save();
            $this->number = $client->id;
            /*
            if($client->id > $this->id){
                $this->id = $client->id;
                $this->number = $this->id.'-'.date('y');
                $this->save();
                $client->contract_id = $this->id;
                $client->save();
            }
            else{
                $this->number = $this->id.'-'.date('y');
                $this->save();
                $client->id = $this->id;
                $client->save();
            }
            */
        }
    }
}
