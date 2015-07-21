<?php
namespace app\forms\usage;

use Yii;
use DateTime;
use app\classes\Assert;
use app\classes\Form;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;

class UsageVoipAddPackageForm extends Form
{
    public
        $usage_voip_id,
        $tariff_id,
        $actual_from;

    private
        $usage,
        $clientTimezone;

    public function rules()
    {
        return [
            [['usage_voip_id','tariff_id',], 'integer'],
            [['actual_from',], 'string'],
            [['tariff_id','actual_from',], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tariff_id' => 'Тариф',
            'actual_from' => 'Дата подключения',
        ];
    }

    public function process()
    {
        $this->usage = UsageVoip::findOne($this->usage_voip_id); /** @var UsageVoip $usage */
        Assert::isObject($this->usage);

        $this->clientTimezone = $this->usage->clientAccount->timezone;

        $today = new DateTime('now', $this->clientTimezone);
        $actualFrom = new DateTime($this->actual_from, $this->clientTimezone);

        if ($actualFrom < $today) {
            Assert::isUnreachable('Дата подключения не может быть в прошлом');
        }

        $usageVoipPackage = new UsageVoipPackage;
        $usageVoipPackage->setAttributes($this->getAttributes(), false);

        $activation_dt = new \DateTime($this->actual_from, $this->clientTimezone);
        $activation_dt->setTime(0, 0, 1);

        $usageVoipPackage->activation_dt = $activation_dt->format('Y-m-d H:i:s');
        $usageVoipPackage->client = $this->usage->clientAccount->client;

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