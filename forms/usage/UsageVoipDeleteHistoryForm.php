<?php

namespace app\forms\usage;

use app\classes\Assert;
use app\classes\Form;
use app\models\EventQueue;
use app\models\LogTarif;
use Yii;

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
        $historyItem = LogTarif::findOne($this->id);
        /** @var LogTarif $historyItem */
        Assert::isObject($historyItem);

        $usage = $historyItem->usageVoip;
        Assert::isObject($usage);

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $historyItem->delete();

            EventQueue::go(EventQueue::ACTUALIZE_NUMBER, ['number' => $usage->E164]);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }
}
