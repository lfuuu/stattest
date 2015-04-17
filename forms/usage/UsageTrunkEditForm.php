<?php
namespace app\forms\usage;

use app\classes\Assert;
use Yii;
use DateTimeZone;
use DateTime;
use app\models\ClientAccount;
use app\models\UsageTrunk;

class UsageTrunkEditForm extends UsageTrunkForm
{
    /** @var ClientAccount */
    public $clientAccount;
    /** @var UsageTrunk */
    public $usage;
    /** @var DateTimeZone */
    public $timezone;
    /** @var DateTime */
    public $today;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['connection_point_id', 'client_account_id', 'trunk_name', 'actual_from'], 'required', 'on' => 'add'];
        $rules[] = [['trunk_name'], 'validateTrunkName', 'on' => 'add'];
        $rules[] = [['orig_min_payment', 'term_min_payment'], 'required', 'on' => 'edit'];
        return $rules;
    }


    public function add()
    {
        $actualFrom = new DateTime($this->actual_from, $this->timezone);
        $activationDt = clone $actualFrom;
        $activationDt->setTimezone(new DateTimeZone('UTC'));

        $actualTo = new DateTime('4000-01-01', $this->timezone);
        $expireDt = clone $actualTo;
        $expireDt->setTimezone(new DateTimeZone('UTC'));

        if ($actualFrom < $this->today) {
            $this->addError('actual_from', 'Дата подключения не может быть в прошлом');
            return false;
        }

        $usage = new UsageTrunk();
        $usage->client_account_id = $this->clientAccount->id;
        $usage->connection_point_id = $this->connection_point_id;
        $usage->actual_from = $actualFrom->format('Y-m-d');
        $usage->actual_to = $actualTo->format('Y-m-d');
        $usage->activation_dt = $activationDt->format('Y-m-d H:i:s');
        $usage->expire_dt = $expireDt->format('Y-m-d H:i:s');
        $usage->trunk_name = $this->trunk_name;
        $usage->orig_enabled = $this->orig_enabled;
        $usage->term_enabled = $this->term_enabled;
        $usage->orig_min_payment = $this->orig_min_payment;
        $usage->term_min_payment = $this->term_min_payment;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $usage->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $usage->id;

        return true;
    }

    public function edit()
    {
        $usage = $this->usage;
        Assert::isTrue($usage->isActive());

        $usage->trunk_name = $this->trunk_name;
        $usage->orig_enabled = $this->orig_enabled;
        $usage->term_enabled = $this->term_enabled;
        $usage->orig_min_payment = $this->orig_enabled ? $this->orig_min_payment : 0;
        $usage->term_min_payment = $this->term_enabled ? $this->term_min_payment : 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $usage->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }


    public function initModel(ClientAccount $clientAccount, UsageTrunk $usage = null) {
        $this->clientAccount = $clientAccount;
        $this->client_account_id = $clientAccount->id;
        $this->timezone = $clientAccount->timezone;

        $this->today = new DateTime('now', $this->timezone);
        $this->today->setTime(0, 0, 0);

        if ($usage) {
            $this->usage = $usage;
            $this->id = $usage->id;
            $this->connection_point_id = $usage->connection_point_id;

            $this->setAttributes($usage->getAttributes(), false);
            $this->trunk_name = $usage->trunk_name;
        } else {
            $this->actual_from = $this->today->format('Y-m-d');
        }
    }

    public function validateTrunkName($attribute, $params)
    {
        if (!$this->trunk_name) {
            return;
        }

        $actualFrom = new DateTime($this->actual_from, $this->timezone);
        $activationDt = clone $actualFrom;
        $activationDt->setTimezone(new DateTimeZone('UTC'));

        $actualTo = new DateTime('4000-01-01', $this->timezone);
        $expireDt = clone $actualTo;
        $expireDt->setTimezone(new DateTimeZone('UTC'));

        $queryTrunk =
            UsageTrunk::find()
                ->andWhere(['connection_point_id' => $this->connection_point_id])
                ->andWhere(['trunk_name' => $this->trunk_name])
                ->andWhere(
                    '(activation_dt between :from and :to) or (expire_dt between :from and :to)',
                    [':from' => $activationDt->format('Y-m-d H:i:s'), ':to' => $expireDt->format('Y-m-d H:i:s')]
                );
        if ($this->id) {
            $queryTrunk->andWhere('id != :id', [':id' => $this->id]);
        }
        $usages = $queryTrunk->all();
        foreach ($usages as $usage) {
            $this->addError('trunk_name', "Имя транка пересекается с id: {$usage->id}, клиент: {$usage->clientAccount->client}, c {$usage->actual_from} по {$usage->actual_to}");
        }
    }
}