<?php
namespace app\forms\usage;

use app\models\usages\UsageInterface;
use Yii;
use DateTimeZone;
use DateTime;
use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\Trunk;
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
        $rules[] = [['connection_point_id', 'client_account_id', 'trunk_id', 'actual_from'], 'required', 'on' => 'add'];
        $rules[] = [['trunk_id'], 'validateTrunkId', 'on' => 'add'];
        $rules[] = [['orig_min_payment', 'term_min_payment','trunk_id',], 'required', 'on' => 'edit'];
        $rules[] = [['trunk_id'], 'validateTrunkId', 'on' => 'edit'];
        return $rules;
    }


    public function add()
    {
        $actualFrom = new DateTime($this->actual_from, $this->timezone);
        $activationDt = clone $actualFrom;
        $activationDt->setTimezone(new DateTimeZone('UTC'));

        $actualTo = new DateTime(UsageInterface::MAX_POSSIBLE_DATE, $this->timezone);

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
        $usage->expire_dt = DateTimeZoneHelper::getExpireDateTime(UsageInterface::MAX_POSSIBLE_DATE, $this->timezone);
        $usage->trunk_id = $this->trunk_id;
        $usage->orig_enabled = $this->orig_enabled;
        $usage->term_enabled = $this->term_enabled;
        $usage->orig_min_payment = $this->orig_min_payment;
        $usage->term_min_payment = $this->term_min_payment;
        $usage->description = $this->description;

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

        $usage->trunk_id = $this->trunk_id;
        $usage->orig_enabled = $this->orig_enabled;
        $usage->term_enabled = $this->term_enabled;
        $usage->orig_min_payment = $this->orig_enabled ? $this->orig_min_payment : 0;
        $usage->term_min_payment = $this->term_enabled ? $this->term_min_payment : 0;
        $usage->description = $this->description;

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
            $this->trunk_id = $usage->trunk_id;
        } else {
            $this->actual_from = $this->today->format('Y-m-d');
        }
    }

    public function validateTrunkId($attribute, $params)
    {
        if (!$this->trunk_id) {
            return;
        }

        $trunk = Trunk::findOne($this->trunk_id);
        if ($trunk === null) {
            $this->addError('trunk_id', "Транк #{$this->trunk_id} не найден");
        }
    }
}