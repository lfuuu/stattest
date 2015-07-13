<?php
namespace app\forms\usage;

use app\models\LogTarif;
use app\models\UsageVoip;
use Yii;
use app\classes\Assert;
use app\classes\Form;

class UsageVoipCloseForm extends Form
{
    public $usage_id;
    public $close_date;

    public function rules()
    {
        return [
            [['usage_id'], 'integer'],
            [['close_date'], 'string'],
            [['usage_id', 'close_date'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'close_date' => 'Дата закрытия',
        ];
    }

    public function process()
    {
        $usage = UsageVoip::findOne($this->usage_id); /** @var UsageVoip $usage */
        Assert::isObject($usage);
        Assert::isTrue($usage->isActive());

        $timezone = $usage->clientAccount->timezone;
        $nextDay = new \DateTime('now', $timezone);
        $nextDay->modify('+1 day');
        $nextDay->setTime(0, 0, 0);

        $closeDate = new \DateTime($this->close_date, $timezone);
        if ($closeDate < $nextDay) {
            Yii::$app->session->addFlash('error', 'Закрыть услугу можно только со следующего дня');
            return false;
        }

        $usage->actual_to = $closeDate->format('Y-m-d');

        $nextHistoryItems =
            LogTarif::find()
                ->andWhere(['service' => 'usage_voip'])
                ->andWhere(['id_service' => $usage->id])
                ->andWhere('date_activation > :date', [$usage->actual_to])
                ->all();

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $usage->save();

            foreach ($nextHistoryItems as $nextHistoryItem) {
                $nextHistoryItem->delete();
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}