<?php
namespace app\forms\client;

use app\classes\validators\InnValidator;
use app\models\ClientContractComment;
use app\models\ClientContractType;
use app\models\ClientContragent;
use app\models\ClientGridBussinesProcess;
use app\models\ClientGridSettings;
use app\models\EventQueue;
use app\models\HistoryVersion;
use app\models\Organization;
use app\models\UserDepart;
use Yii;
use app\classes\Form;
use yii\base\Exception;
use app\models\ClientContract;
use yii\helpers\ArrayHelper;

class ContractEditForm extends Form
{
    public
        $newClient = null,

        $id,
        $super_id,
        $contragent_id,
        $number = '',
        $date,
        $organization,
        $manager,
        $comment,
        $account_manager,
        $business_process_id,
        $business_process_status_id,
        $contract_type_id,
        $state,

        $public_comment = [];

    protected $contract = null;

    public $ddate = null;

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
            $this->contract = HistoryVersion::getVersionOnDate('ClientContract', $this->id, $this->ddate);
            if ($this->contract === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->contract->getAttributes(), false);
        } elseif ($this->contragent_id) {
            $this->contract = new ClientContract();
            $this->contract->contragent_id = $this->contragent_id;
            $this->super_id = $this->contract->super_id = !$this->super_id ? ClientContragent::findOne($this->contragent_id)->super_id : $this->super_id;
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

        $contract->setAttributes(
            array_filter($attributes,
                function($var){
                    return $var !== null;
                }
            ),
            false);

        if ($contract->save()) {
            $this->setAttributes($contract->getAttributes(), false);
            $this->newClient = $contract->newClient;
            ///////////////////
            $eq = new EventQueue();
            $eq->event = 'client_set_status';
            $eq->param = $contract->id;
            $eq->code = md5('client_set_status' . '|||' . $contract->id);

            $eq->save();
            /*\voipNumbers::check();*/
            return true;
        } else
            $this->addErrors($contract->getErrors());

        return false;
    }

    public function getContragentListBySuperId()
    {
        $superId = $this->super_id;
        $models = ClientContragent::find()->andWhere(['super_id' => $superId])->all();
        return ArrayHelper::map($models, 'id', 'name');
    }

    public function validate($attributeNames = null, $clearErrors = false)
    {
        if ($this->contract->attributes['state'] !== $this->state && $this->state != 'unchecked') {
            $contragent = ClientContragent::findOne($this->contragent_id);
            $contragent->setScenario('checked');
            if (!$contragent->validate()) {
                if (isset($contragent->errors['inn']) && isset($contragent->errors['kpp']))
                    $this->addError('state', 'Введите корректныйе ИНН и КПП у <a href="/contragent/edit?id=' . $this->contragent_id . '" target="_blank">контрагента</a>');
                elseif (isset($contragent->errors['inn']))
                    $this->addError('state', 'Введите корректный ИНН у <a href="/contragent/edit?id=' . $this->contragent_id . '" target="_blank">контрагента</a>');
                elseif (isset($contragent->errors['kpp']))
                    $this->addError('state', 'Введите корректный КПП у <a href="/contragent/edit?id=' . $this->contragent_id . '" target="_blank">контрагента</a>');
            }
        }
        return parent::validate($attributeNames, $clearErrors);
    }

    public function beforeValidate()
    {
        $this->number .= '';
        return parent::beforeValidate();
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }
}