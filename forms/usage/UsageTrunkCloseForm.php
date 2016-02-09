<?php
namespace app\forms\usage;

use Yii;
use DateTimeZone;
use app\classes\Assert;
use app\classes\Form;
use app\helpers\DateTimeZoneHelper;
use app\models\UsageTrunk;

class UsageTrunkCloseForm extends Form
{
    public $usage_id;
    public $actual_to;

    public function rules()
    {
        return [
            [['usage_id'], 'integer'],
            [['actual_to'], 'string'],
            [['usage_id'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'actual_to' => 'Дата отключения',
        ];
    }

    public function process()
    {
        $usage = UsageTrunk::findOne($this->usage_id); /** @var UsageTrunk $usage */
        Assert::isObject($usage);
        Assert::isTrue($usage->isActive());

        $timezone = $usage->clientAccount->timezone;
        $nextDay = new \DateTime('now', $timezone);
        $nextDay->modify('+1 day');
        $nextDay->setTime(0, 0, 0);

        $actualTo = new \DateTime($this->actual_to, $timezone);

        if ($actualTo < $nextDay) {
            Yii::$app->session->addFlash('error', 'Закрыть услугу можно только со следующего дня');
            return false;
        }

        $usage->actual_to = $actualTo->format('Y-m-d');
        $usage->expire_dt = DateTimeZoneHelper::getExpireDateTime($this->actual_to, $timezone);

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

}