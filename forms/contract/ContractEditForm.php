<?php
namespace app\forms\contract;

use app\models\Organization;
use app\models\UserDepart;
use Yii;
use app\classes\Form;
use yii\base\Exception;
use app\models\ClientContract;
use app\models\User;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ContractEditForm extends Form
{
    public $id,
        $super_id,
        $contragent_id,
        $number,
        $date,
        $organization,
        $manager,
        $account_manager,
        $business_process_id,
        $business_process_status_id,
        $signer_name,
        $signer_position,
        $signer_nameV,
        $signer_positionV,
        $contract_type_id,
        $status;////??;

    protected $contract = null;

    public function rules()
    {
        $rules = [
            [['number', 'date', 'organization', 'manager', 'account_manager', 'signer_name', 'signer_position', 'signer_nameV', 'signer_positionV'], 'string'],
            [['contragent_id', 'contract_type_id', 'business_process_id', 'business_process_status_id', 'super_id'], 'integer']
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientContract())->attributeLabels();
    }

    public function init()
    {
        if ($this->id) {
            $this->contract = ClientContract::findOne($this->id);
            if ($this->contract === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->contract->getAttributes(), false);
        } else {
            $this->contract = new ClientContract();
        }
    }

    public function getAccountManagersList()
    {
        $arr = User::find()->where(['usergroup' => 'account_managers', 'enabled' => 'yes'])->all();
        return ArrayHelper::map($arr, 'user', 'name');
    }

    public function getManagersList()
    {
        $arr = User::find()->where(['usergroup' => 'manager', 'enabled' => 'yes'])->all();
        return ArrayHelper::map($arr, 'user', 'name');
    }

    public function getOrganizationsList()
    {
        $arr = Organization::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function save()
    {
        $contract = $this->contract;

        $contract->setAttributes($this->getAttributes(), false);

        if ($contract->save()) {
            return true;
        }
        return false;
    }
}