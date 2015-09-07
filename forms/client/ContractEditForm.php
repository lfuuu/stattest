<?php
namespace app\forms\client;

use app\classes\Event;
use app\helpers\SetFieldTypeHelper;
use app\models\BusinessProcessStatus;
use app\models\ClientContractComment;
use app\models\ClientContragent;
use app\models\BusinessProcess;
use app\models\Organization;
use Yii;
use app\classes\Form;
use yii\base\Exception;
use app\models\ClientContract;
use yii\helpers\ArrayHelper;
use app\forms\comment\ClientContractCommentForm;

class ContractEditForm extends Form
{
    public
        $newClient = null,

        $id,
        $super_id,
        $contragent_id,
        $number,
        $date,
        $organization_id,
        $manager,
        $comment,
        $account_manager,
        $business_process_id,
        $business_process_status_id,
        $business_id,
        $state,
        $contract_type_id,
        $financial_type,
        $federal_district,
        $is_external,

        $save_comment_stage = false,
        $public_comment = [];

    protected $contract = null;

    public $historyVersionRequestedDate;
    public $historyVersionStoredDate;

    public function rules()
    {
        $rules = [
            [['date', 'manager', 'account_manager', 'comment', 'historyVersionStoredDate',], 'string'],
            [['contragent_id', 'business_id', 'business_process_id', 'business_process_status_id', 'super_id',
                'organization_id', 'contract_type_id'], 'integer'],
            [['contract_type_id'], 'default', 'value' => 0],
            ['business_process_id', 'default', 'value' => BusinessProcess::TELECOM_MAINTENANCE],
            ['number', 'default', 'value' => ''],
            ['business_process_status_id', 'default', 'value' => BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES],
            [['public_comment', 'save_comment_stage'], 'safe'],
            ['financial_type', 'in', 'range' => array_keys(ClientContract::$financialTypes)],
            ['federal_district', function($attribute){
                SetFieldTypeHelper::validateField($this->getModel(), $attribute, $this->$attribute, $this);
            }],
            ['is_external', 'in', 'range' => ClientContract::$externalType],
            [['federal_district', 'financial_type'], 'default', 'value' => ''],
            ['state', 'validateState'],
            [['business_process_id', 'business_process_status_id'], function($attribute){
                if(!Yii::$app->user->can('clients.restatus') && $this->$attribute !== $this->getModel()->$attribute)
                    $this->addError('state', 'Вы не можете менять бизнес процесс');
            }],
            ['business_id', function($attribute){
                if( !$this->getIsNewRecord() &&  $this->$attribute != $this->getModel()->$attribute && !Yii::$app->user->can('clients.restatus'))
                    $this->addError('state', 'Вы не можете менять тип договора');
            }],
            ['is_external', 'default', 'value' => ClientContract::IS_INTERNAL],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientContract())->attributeLabels() + ['comment' => 'Комментарий'];
    }

    /**
     * @return ClientContract
     */
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

    public function save()
    {
        /** @var ClientContract $contract */
        $contract = $this->contract;

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
                $comment = new ClientContractCommentForm;
                $comment->comment = $this->comment;
                $comment->contract_id = $this->id;
                $comment->save();
            }
        }

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

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }

    public function validateState($attribute)
    {
        if(!array_key_exists($this->$attribute, $this->getModel()->statusesForChange()))
            $this->addError($attribute, 'Вы не можете менять статус');

        if ($this->getModel()->$attribute !== $this->state && $this->state != ClientContract::STATE_UNCHECKED) {
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
    }
}