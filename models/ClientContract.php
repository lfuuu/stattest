<?php
namespace app\models;

use app\forms\client\ClientEditForm;
use yii\db\ActiveRecord;
use app\classes\behaviors\HistoryVersion;
use app\classes\behaviors\HistoryChanges;

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
            HistoryVersion::className(),
            HistoryChanges::className(),
        ];
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
            $client = new ClientEditForm(['contract_id' => $this->id]);
            $client->save();
            if($client->id > $this->id){
                $this->id = $client->id;
                $this->save();
                $client->contract_id = $this->id;
                $client->save();
            }
            else{
                $client->id = $this->id;
                $client->save();
            }
        }
    }
}
