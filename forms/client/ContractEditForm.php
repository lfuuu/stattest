<?php
namespace app\forms\client;

use app\classes\Event;
use app\models\BusinessProcessStatus;
use app\models\ClientContractComment;
use app\models\ClientContragent;
use app\models\BusinessProcess;
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
        $organization_id,
        $manager,
        $comment,
        $account_manager,
        $business_process_id,
        $business_process_status_id,
        $contract_type_id,
        $state,
        $is_external = 0,

        $save_comment_stage = false,
        $public_comment = [];

    protected $contract = null;

    public $historyVersionRequestedDate;
    public $historyVersionStoredDate;

    public function rules()
    {
        $rules = [
            [['date', 'manager', 'account_manager', 'comment', 'historyVersionStoredDate',], 'string'],
            [['contragent_id', 'contract_type_id', 'business_process_id', 'business_process_status_id', 'super_id', 'organization_id', 'is_external'], 'integer'],
            ['state', 'in', 'range' => ['unchecked', 'checked_copy', 'checked_original',]],
            ['business_process_id', 'default', 'value' => 1],
            ['business_process_status_id', 'default', 'value' => 19],
            [['public_comment', 'save_comment_stage'], 'safe'],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientContract())->attributeLabels() + ['comment' => 'Комментарий'];
    }

    public function getModel()
    {
        return $this->contract;
    }

    public function init()
    {
        if ($this->id) {
            $this->contract = ClientContract::findOne($this->id);
            if($this->contract && $this->historyVersionRequestedDate) {
                $this->contract->loadVersionOnDate($this->historyVersionRequestedDate);
            }
            if ($this->contract === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->contract->getAttributes(), false);
        } elseif ($this->contragent_id) {
            $this->contract = new ClientContract();
            $this->contract->contragent_id = $this->contragent_id;
            $this->super_id = $this->super_id ? $this->super_id  : ClientContragent::findOne($this->contragent_id)->super_id;
            $this->contract->super_id = $this->super_id;
        } else{
            $this->contract = new ClientContract();
        }
    }

    public function getOrganizationsList()
    {
        $date = date('Y-m-d');
        if($this->contract && $this->contract->getHistoryVersionStoredDate())
            $date = $this->contract->getHistoryVersionStoredDate();
        $organizations = Organization::find()
            ->andWhere(['<=', 'actual_from', $date])
            ->andWhere(['>=', 'actual_to', $date])
            ->all();
        return ArrayHelper::map($organizations, 'organization_id', 'name');
    }

    public function getBusinessProcessesList()
    {
        $arr = BusinessProcess::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getBusinessProcessStatusesList()
    {
        $arr = BusinessProcessStatus::find()->orderBy(['business_process_id' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC])->all();;
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getCurrentBusinessProcessStatus()
    {
        return BusinessProcessStatus::findOne($this->business_process_status_id);
    }

    public function save()
    {
        if($this->save_comment_stage) {
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
        if($contract && $this->historyVersionStoredDate) {
            $contract->setHistoryVersionStoredDate($this->historyVersionStoredDate);
        }

        if ($contract->save()) {
            $this->setAttributes($contract->getAttributes(), false);
            $this->newClient = $contract->newClient;

            foreach($contract->getAccounts() as $account)
                Event::go('client_set_status', $account->id);

            return true;
        } else
            $this->addErrors($contract->getErrors());

        return false;
    }

    public function getContragentListBySuperId()
    {
        $superId = $this->super_id;
        return ClientContragent::find()->andWhere(['super_id' => $superId])->all();
    }

    public function validate($attributeNames = null, $clearErrors = false)
    {
        if(!array_key_exists($this->state, $this->getModel()->statusesForChange()))
            $this->addError('state', 'Вы не можете менять статус');

        if( !$this->getIsNewRecord() &&  $this->contract_type_id != $this->getModel()->contract_type_id && !Yii::$app->user->can('clients.restatus'))
            $this->addError('state', 'Вы не можете менять тип договора');

        if(
            ($this->business_process_id != $this->getModel()->business_process_id || $this->business_process_status_id != $this->getModel()->business_process_status_id)
            && !Yii::$app->user->can('clients.restatus')
        )
            $this->addError('state', 'Вы не можете менять бизнес процесс');

        if ($this->contract->attributes['state'] !== $this->state && $this->state != 'unchecked') {
            $contragent = ClientContragent::findOne($this->contragent_id);
            if(!$contragent->getIsNewRecord())
                $contragent->hasChecked = true;
            if (!$contragent->validate()) {
                if (isset($contragent->errors['inn']) && isset($contragent->errors['kpp']))
                    $this->addError('state', 'Введите корректные ИНН и КПП у <a href="/contragent/edit?id=' . $this->contragent_id . '" target="_blank">контрагента</a>');
                elseif (isset($contragent->errors['inn']))
                    $this->addError('state', 'Введите корректный ИНН у <a href="/contragent/edit?id=' . $this->contragent_id . '" target="_blank">контрагента</a>');
                elseif (isset($contragent->errors['kpp']))
                    $this->addError('state', 'Введите корректный КПП у <a href="/contragent/edit?id=' . $this->contragent_id . '" target="_blank">контрагента</a>');
            }
        }

        return parent::validate($attributeNames, $clearErrors);
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }
}