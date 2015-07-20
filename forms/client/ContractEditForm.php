<?php
namespace app\forms\client;

use app\classes\Event;
use app\dao\ClientGridSettingsDao;
use app\models\ClientContractComment;
use app\models\ClientContractType;
use app\models\ClientContragent;
use app\models\ClientGridBussinesProcess;
use app\models\Organization;
use app\models\UserDepart;
use Yii;
use app\classes\Form;
use yii\base\Exception;
use app\models\ClientContract;
use yii\base\Theme;
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

        $public_comment = [];

    protected $contract = null;

    public $deferredDate = null;

    public function rules()
    {
        $rules = [
            [['number', 'date', 'manager', 'account_manager', 'comment'], 'string'],
            [['contragent_id', 'contract_type_id', 'business_process_id', 'business_process_status_id', 'super_id', 'organization_id'], 'integer'],
            ['state', 'in', 'range' => ['unchecked', 'checked_copy', 'checked_original']],
            [['public_comment'], 'safe'],
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
            $this->contract = ClientContract::findOne($this->id)->loadVersionOnDate($this->deferredDate);
            if ($this->contract === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->contract->getAttributes(), false);
        } elseif ($this->contragent_id) {
            $this->contract = new ClientContract();
            $this->contract->contragent_id = $this->contragent_id;
            $this->super_id = $this->contract->super_id = !$this->super_id ? ClientContragent::findOne($this->contragent_id)->super_id : $this->super_id;
        } else{
            $this->contract = new ClientContract();
        }
    }

    public function getOrganizationsList()
    {
        $date = $this->deferredDate ? $this->deferredDate : date('Y-m-d');
        $organizations = Organization::find()
            ->andWhere(['<=', 'actual_from', $date])
            ->andWhere(['>=', 'actual_to', $date])
            ->all();
        return ArrayHelper::map($organizations, 'id', 'name');
    }

    public function getBusinessProcessesList()
    {
        $arr = ClientGridBussinesProcess::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getBusinessProcessStatusesList()
    {
        $arr = ClientGridSettingsDao::me()->getAllByParams(['show_as_status' => true]);
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getCurrentBusinessProcessStatus()
    {
        return ClientGridSettingsDao::me()->getGridByBusinessProcessStatusId($this->business_process_status_id, false);
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
            Event::go('client_set_status', $contract->id);

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

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }
}