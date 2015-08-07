<?php
namespace app\forms\usage;

use app\models\LogTarif;
use Yii;
use app\classes\Assert;
use app\classes\Form;
use app\classes\Event;

class UsageVoipDeleteHistoryForm extends Form
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['id'], 'required'],
        ];
    }

    public function process()
    {
        $historyItem = LogTarif::findOne($this->id); /** @var LogTarif $historyItem */
        Assert::isObject($historyItem);

        $usage = $historyItem->usageVoip;
        Assert::isObject($usage);

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $historyItem->delete();

            Event::go('update_phone_product', ['account_id' => $this->clientAccount->id]);
            Event::go('actualize_number', ['number' => $usage->E164]);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }
}