<?php
namespace app\forms\usage;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Assert;
use app\classes\Form;
use app\helpers\DateTimeZoneHelper;
use app\models\LogTarif;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\models\usages\UsageInterface;
use app\models\User;

class UsageVoipAddPackageForm extends Form
{
    public
        $usage_voip_id,
        $tariff_id,
        $actual_from,
        $status;

    /** @var UsageVoip $usage */
    private
        $usage,
        $clientTimezone;

    public function rules()
    {
        return [
            [['usage_voip_id', 'tariff_id',], 'integer'],
            [['actual_from',], 'string'],
            [['tariff_id', 'actual_from',], 'required'],
            ['status', 'default', 'value' => 'connecting']
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
        $this->usage = UsageVoip::find()
            ->where('id = :id AND ( actual_to = :max OR actual_to >= :now )', [
                'id' => $this->usage_voip_id,
                'max' => UsageInterface::MAX_POSSIBLE_DATE,
                'now' => (new DateTime('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ])
            ->one();

        $this->clientTimezone = $this->usage->clientAccount->timezone;

        $today = new DateTime('now', $this->clientTimezone);
        $today->setTime(0, 0, 0);

        $actualFrom = new DateTime($this->actual_from, $this->clientTimezone);

        if ($actualFrom < $today) {
            Assert::isUnreachable('Дата подключения не может быть в прошлом');
        }

        $usageActualFrom = new DateTime($this->usage->actual_from, $this->clientTimezone);
        $usageActualTo = new DateTime($this->usage->actual_to, $this->clientTimezone);
        if ($actualFrom < $usageActualFrom || $usageActualTo <= $actualFrom) {
            Assert::isUnreachable('Дата подключения должна быть во время действия услуги телефонии');
        }

        $usageVoipPackage = new UsageVoipPackage;
        $usageVoipPackage->setAttributes($this->getAttributes(), false);

        $usageVoipPackage->client = $this->usage->clientAccount->client;
        $usageVoipPackage->actual_to = $this->usage->actual_to;

        $today = new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));

        $logTariff = new LogTarif;
        $logTariff->service = 'usage_voip_package';
        $logTariff->id_tarif = $this->tariff_id;
        $logTariff->id_user = Yii::$app->has('user') && Yii::$app->user->getId() ? Yii::$app->user->getId() : User::SYSTEM_USER_ID;
        $logTariff->ts = $today->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $logTariff->date_activation = $this->actual_from;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $usageVoipPackage->save();

            $logTariff->id_service = $usageVoipPackage->id;
            $logTariff->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}