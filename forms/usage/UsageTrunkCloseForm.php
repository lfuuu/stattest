<?php
namespace app\forms\usage;

use Yii;
use DateTimeZone;
use app\models\UsageTrunk;
use app\classes\Assert;
use app\classes\Form;

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
        $expireDt = clone $actualTo;
        $expireDt->setTimezone(new DateTimeZone('UTC'));

        if ($actualTo < $nextDay) {
            Yii::$app->session->addFlash('error', 'Закрыть услугу можно только со следующего дня');
            return false;
        }

        $usage->actual_to = $actualTo->format('Y-m-d');
        $usage->expire_dt = $expireDt->format('Y-m-d H:i:s');

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