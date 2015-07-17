<?php
namespace app\forms\usage;

use app\models\UsageVoipPackage;
use Yii;
use app\classes\Assert;
use app\classes\Form;
use app\models\UsageVoip;

class UsageVoipAddPackageForm extends Form
{
    public
        $usage_voip_id,
        $tariff_id,
        $actual_from,
        $actual_to;

    public function rules()
    {
        return [
            [['usage_voip_id','tariff_id',], 'integer'],
            [['actual_from','actual_to',], 'string'],
            [['tariff_id','actual_from',], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tariff_id' => 'Тариф',
            'actual_from' => 'Активен с',
            'actual_to' => 'Активен до',
        ];
    }

    public function process()
    {
        $usage = UsageVoip::findOne($this->usage_voip_id); /** @var UsageVoip $usage */
        Assert::isObject($usage);

        $usageVoipPackage = new UsageVoipPackage;
        $usageVoipPackage->setAttributes($this->getAttributes(), false);

        $timezone = $usage->clientAccount->timezone;

        $activation_dt = new \DateTime($this->actual_from, $timezone);
        $activation_dt->setTime(0, 0, 1);

        $usageVoipPackage->activation_dt = $activation_dt->format('Y-m-d H:i:s');

        if ($this->actual_to) {
            $expire_dt = new \DateTime($this->actual_to, $timezone);
            $expire_dt->setTime(23, 59, 59);

            $usageVoipPackage->expire_dt = $expire_dt->format('Y-m-d H:i:s');
        }

        $usageVoipPackage->client = $usage->clientAccount->client;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $usageVoipPackage->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return false;
    }

}