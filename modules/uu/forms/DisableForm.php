<?php

namespace app\modules\uu\forms;

use app\classes\Form;
use app\classes\validators\AccountIdValidator;
use app\classes\WebApplication;
use app\helpers\DateTimeZoneHelper;
use app\models\Param;
use app\modules\uu\models\AccountTariff;

/**
 * Class DisableForm
 */
class DisableForm extends Form
{
    public $serviceCount = 0;
    public $code = '';
    public $date = '';
    public $clientAccountId = '';

    public function rules()
    {
        return [
            [['code', 'date', 'clientAccountId'], 'required'],
            ['clientAccountId', AccountIdValidator::class],
            [['code', 'date'], 'string'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            ['date', 'validateCode']
        ];
    }

    public function validateCode($attr)
    {
        $user = \Yii::$app->user->getId();
        $code = Param::getParam('disable_code_' . $user) ?: 'xxx';

        if ($code != $this->code) {
            $this->addError('date', 'Неправильный код');
        }
        Param::setParam('disable_code_' . $user, 'yyy');
    }

    public function generateCode()
    {
        $user = \Yii::$app->user->getId();
        $code = rand(1000000, 9999999);
        Param::setParam('disable_code_' . $user, $code);

        return $code;
    }

    public function attributeLabels()
    {
        return ['code' => 'Код', 'date' => 'Дата отключения'];
    }

    public function go()
    {
        $datetimeStr = DateTimeZoneHelper::setDateTime($this->date)->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $accountTariffQuery = AccountTariff::find()
            ->where([
                'client_account_id' => $this->clientAccountId
            ])
            ->andWhere(['NOT', ['tariff_period_id' => null]])
            ->orderBy([
                'service_type_id' => SORT_ASC,
                'id' => SORT_ASC
            ]);

        $transaction = \Yii::$app->db->beginTransaction();
        $message = '';
        try {
            /** @var AccountTariff $accountTariff */
            foreach ($accountTariffQuery->each() as $accountTariff) {
                $message .= PHP_EOL . '<br>' . $accountTariff->getLink() . ': ' . $accountTariff->voip_number . ' ... ';
                if (!$accountTariff->isEditable()) {
                    $message .= 'услуга нередактируемая';
                    continue;
                }


                $accountTariffLogs = $accountTariff->accountTariffLogs;
                if (($lastTariffLog = reset($accountTariffLogs)) && !$lastTariffLog->tariff_period_id) {
                    if ($lastTariffLog->actual_from_utc < $datetimeStr) {
                        $message .= 'уже отключено';
                        continue;
                    }
                }

                $this->serviceCount++;

                $accountTariff->setClosed($datetimeStr);
                $message .= 'ok';
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $message .= 'ошибка. Все предыдущие действия будут отменены.';
            $transaction->rollBack();
            if (\Yii::$app instanceof WebApplication) {
                \Yii::$app->session->addFlash('error', $message);
            }

            throw $e;
        }

        if (\Yii::$app instanceof WebApplication) {
            \Yii::$app->session->addFlash('success', $message);
        }
        return $message;
    }

}