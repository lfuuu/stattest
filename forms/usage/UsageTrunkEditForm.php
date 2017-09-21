<?php
namespace app\forms\usage;

use app\exceptions\ModelValidationException;
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

    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['connection_point_id', 'client_account_id', 'trunk_id', 'actual_from'], 'required', 'on' => 'add'];
        $rules[] = [['orig_min_payment', 'term_min_payment', 'trunk_id',], 'required', 'on' => 'edit'];
        $rules[] = [['trunk_id'], 'validateTrunkId', 'on' => ['edit', 'add']];
        return $rules;
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function add()
    {
        $actualFrom = new DateTime($this->actual_from, $this->timezone);
        $actualTo = new DateTime(UsageInterface::MAX_POSSIBLE_DATE, $this->timezone);

        if ($actualFrom < $this->today) {
            $this->addError('actual_from', 'Дата подключения не может быть в прошлом');
            return false;
        }

        $usage = new UsageTrunk;
        $usage->client_account_id = $this->clientAccount->id;
        $usage->connection_point_id = $this->connection_point_id;
        $usage->actual_from = $actualFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $usage->actual_to = $actualTo->format(DateTimeZoneHelper::DATE_FORMAT);
        $usage->trunk_id = $this->trunk_id;
        $usage->orig_enabled = $this->orig_enabled;
        $usage->term_enabled = $this->term_enabled;
        $usage->orig_min_payment = $this->orig_min_payment;
        $usage->term_min_payment = $this->term_min_payment;
        $usage->description = $this->description;
        $usage->ip = $this->ip;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            if (!$usage->save()) {
                throw new ModelValidationException($usage);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $usage->id;

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function edit()
    {
        $usage = $this->usage;
        Assert::isTrue($usage->isActive());

        $actualTo = new DateTime($this->actual_to, $this->timezone);

        if ($actualTo < $this->today) {
            $this->addError('actual_to', 'Дата отключения не может быть в прошлом');
            return false;
        }

        $usage->trunk_id = $this->trunk_id;
        $usage->orig_enabled = $this->orig_enabled;
        $usage->term_enabled = $this->term_enabled;
        $usage->orig_min_payment = $this->orig_enabled ? $this->orig_min_payment : 0;
        $usage->term_min_payment = $this->term_enabled ? $this->term_min_payment : 0;
        $usage->description = $this->description;
        $usage->ip = $this->ip;
        $usage->actual_to = $actualTo->format(DateTimeZoneHelper::DATE_FORMAT);

        $transaction = Yii::$app->db->beginTransaction();
        try {

            if (!$usage->save()) {
                throw new ModelValidationException($usage);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @param ClientAccount $clientAccount
     * @param UsageTrunk|null $usage
     */
    public function initModel(ClientAccount $clientAccount, UsageTrunk $usage = null)
    {
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
            $this->actual_from = $this->today->format(DateTimeZoneHelper::DATE_FORMAT);
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
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