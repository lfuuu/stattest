<?php
namespace app\forms\client;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Form;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContractReward;

class ContractRewardsEditForm extends Form
{

    public
        $contract_id,
        $usage_type,
        $actual_from,
        $period_type = ClientContractReward::PERIOD_ALWAYS,
        $once_only = 0,
        $percentage_once_only = 0,
        $percentage_of_fee = 0,
        $percentage_of_over = 0,
        $percentage_of_margin = 0,
        $period_month = 0;

    /**
     * @return array
     */
    public function rules()
    {
        return (new ClientContractReward)->rules();
    }

    /**
     * @inheritdoc
     */
    protected function preProcess()
    {
        $this->actual_from .= '-01';
    }

    /**
     * @return bool
     */
    public function save()
    {
        $now = (new DateTime('first day of this month'))->setTime(0, 0, 0);
        $actualFrom = (new DateTime($this->actual_from))->setTime(0, 0, 0);

        if ($actualFrom <= $now) {
            $this->addError('actual_from', 'Нельзя управлять вознаграждением в прошлом');
            return false;
        }

        $reward = ClientContractReward::find()->where([
            'contract_id' => $this->contract_id,
            'usage_type' => $this->usage_type,
            'actual_from' => $actualFrom->format(DateTimeZoneHelper::DATE_FORMAT),
        ])->one();

        if (!$reward) {
            $reward = new ClientContractReward;
        }

        $reward->setAttributes($this->getAttributes());
        $reward->user_id = Yii::$app->user->id;
        $reward->insert_time =
            (new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        return $reward->save();
    }

}
