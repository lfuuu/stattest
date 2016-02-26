<?php
namespace app\forms\usage;

use app\models\UsageCallChat;
use app\models\usages\UsageInterface;
use Yii;
use DateTimeZone;
use DateTime;
use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\UsageTrunk;

class UsageCallChatEditForm extends UsageCallChatForm
{
    /** @var ClientAccount */
    public $clientAccount;
    /** @var UsageTrunk */
    public $usage;

    public $actual_from;
    public $actual_to = "";
    public $status;
    public $comment = "";
    public $tarif_id;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['actual_from', 'status','tarif_id'], 'required'];
        $rules[] = ['comment', 'string'];
        return $rules;
    }


    public function add()
    {
        $usage = new UsageCallChat();
        $usage->client = $this->clientAccount->client;

        $usage->actual_from = $this->actual_from;
        $usage->actual_to = $this->actual_to ?: UsageInterface::MAX_POSSIBLE_DATE;

        $usage->status = $this->status;
        $usage->comment = $this->comment;
        $usage->tarif_id = $this->tarif_id;

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

        $usage->actual_from = $this->actual_from;
        $usage->actual_to = $this->actual_to ?: UsageInterface::MAX_POSSIBLE_DATE;

        $usage->status = $this->status;
        $usage->tarif_id = $this->tarif_id;
        $usage->comment = $this->comment;

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


    public function initModel(ClientAccount $clientAccount, UsageCallChat $usage = null)
    {
        $this->clientAccount = $clientAccount;


        if ($usage) {
            $this->usage = $usage;
            $this->setAttributes($usage->getAttributes(), false);
            $this->actual_to = $usage->actual_to == UsageInterface::MAX_POSSIBLE_DATE ? '' : $usage->actual_to;
        } else {
            $today = new DateTime('now', $this->clientAccount->timezone);
            $today->setTime(0, 0, 0);

            $this->actual_from = $today->format('Y-m-d');
        }
    }
}