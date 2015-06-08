<?php
namespace app\forms\contract;

use app\models\ClientContractComment;
use app\models\ClientContractType;
use app\models\ClientContragent;
use app\models\ClientGridBussinesProcess;
use app\models\ClientGridSettings;
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
        $comment,
        $account_manager,
        $business_process_id = 1,
        $business_process_status_id = 1,
        $contract_type_id = 2,
        $state,

        $public_comment = [];

    protected $contract = null;

    public function rules()
    {
        $rules = [
            [['number', 'date', 'organization', 'manager', 'account_manager', 'comment'], 'string'],
            [['contragent_id', 'contract_type_id', 'business_process_id', 'business_process_status_id', 'super_id'], 'integer'],
            ['state', 'in', 'range' => ['unchecked', 'checked_copy', 'checked_original']],
            [['public_comment'], 'safe'],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientContract())->attributeLabels() + ['comment' => 'Комментарий'];
    }

    public function init()
    {
        if ($this->id) {
            $this->contract = ClientContract::findOne($this->id);
            if ($this->contract === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->contract->getAttributes(), false);
        } elseif ($this->contragent_id) {
            $this->contract = new ClientContract();
            $this->contract->contragent_id = $this->contragent_id;
            $this->super_id =$this->contract->super_id = ClientContragent::findOne($this->contragent_id)->super_id;
        } else
            throw new Exception('You must send id or contragent_id');
    }

    public function getOrganizationsList()
    {
        $arr = Organization::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getBusinessProcessesList()
    {
        $arr = ClientGridBussinesProcess::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getBusinessProcessStatusesList()
    {
        $arr = ClientGridSettings::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getCurrentBusinessProcessStatus()
    {
        return ClientGridSettings::findOne($this->business_process_status_id);
    }

    public function getContractTypes()
    {
        $arr = ClientContractType::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function save()
    {
        $comments = ClientContractComment::find()->where(['contract_id' => $this->id])->all();
        foreach ($comments as $comment) {
            if (in_array($comment->id, array_keys($this->public_comment)))
                $comment->is_publish = 1;
            else
                $comment->is_publish = 0;
            $comment->save();
        }

        if ($this->comment) {
            $comment = new ClientContractComment();
            $comment->comment = $this->comment;
            $comment->user = Yii::$app->user->identity->user;
            $comment->ts = date('Y-m-d H:i:s');
            $comment->is_publish = 0;
            $comment->contract_id = $this->id;
            $comment->save();
        }

        $contract = $this->contract;

        $attributes = $this->getAttributes();
        unset($attributes['public_comment'], $attributes['comment']);

        $contract->setAttributes($this->getAttributes(), false);

        if ($contract->save()) {
            return true;
        }
        return false;
    }

    public function beforeValidate()
    {
        if(!parent::beforeValidate())
            return false;

        $this->number = $this->contragent_id . '-' . date('y');

        return true;
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }
}