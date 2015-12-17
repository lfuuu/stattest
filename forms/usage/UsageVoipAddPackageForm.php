<?php
namespace app\forms\usage;

use Yii;
use DateTime;
use DateTimeZone;
use yii\base\ModelEvent;
use app\classes\Assert;
use app\classes\Form;
use app\models\LogTarif;
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
        $today->setTime(0, 0, 0);

        $actualFrom = new DateTime($this->actual_from, $this->clientTimezone);

        if ($actualFrom < $today) {
            Assert::isUnreachable('Дата подключения не может быть в прошлом');
        }

        $usageVoipPackage = new UsageVoipPackage;
        $usageVoipPackage->setAttributes($this->getAttributes(), false);

        $activation_dt = new DateTime($this->actual_from, $this->clientTimezone);
        $activation_dt->setTime(0, 0, 0);

        $usageVoipPackage->activation_dt = $activation_dt->format('Y-m-d H:i:s');
        $usageVoipPackage->client = $this->usage->clientAccount->client;


        $today = new DateTime('now', new DateTimeZone('UTC'));

        $logTarif = new logTarif;
        $logTarif->service = 'usage_voip_package';
        $logTarif->id_tarif = $this->tariff_id;
        $logTarif->id_user = Yii::$app->user->getId();
        $logTarif->ts = $today->format('Y-m-d H:i:s');
        $logTarif->date_activation = $this->actual_from;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $usageVoipPackage->save();

            $logTarif->id_service = $usageVoipPackage->id;
            $logTarif->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $usageVoipPackage->trigger(static::EVENT_AFTER_SAVE, new ModelEvent);

        return false;
    }

}
