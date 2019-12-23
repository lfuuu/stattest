<?php

namespace app\modules\uu\behaviors;

use app\classes\adapters\Tele2Adapter;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\sim\classes\VoipHlr;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiPartner;
use app\modules\sim\models\ImsiProfile;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;


class AccountTariffCheckHlr extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'check',
        ];
    }

    public function check(AfterSaveEvent $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        if ($accountTariff->service_type_id != ServiceType::ID_VOIP) {
            return;
        }

        if (VoipHlr::me()->isNumberBelongHlr($accountTariff->voip_number, VoipHlr::ID_TELE2)) {

            if ($accountTariff->number->imsi) {
                throw new \LogicException('IMSI уже прописан у номера ' . $accountTariff->voip_number);
            }

            EventQueue::go(EventQueue::SYNC_TELE2_GET_IMSI, [
                'account_tariff_id' => $accountTariff->id,
                'voip_number' => $accountTariff->voip_number,
                'voip_numbers_warehouse_status' => $accountTariff->voip_numbers_warehouse_status,
            ]);
        }
    }

    public static function reservImsi($params)
    {
        $accountTariff = AccountTariff::findOne(['id' => $params['account_tariff_id']]);

        if (!$accountTariff) {
            throw new \LogicException('AccountTraiff ' . $params['account_tariff_id'] . ' не найден');
        }

//        $transactionPg = Imsi::getDb()->beginTransaction();

        /** @var Imsi $imsi */
        $imsi = self::getNextImsi($params['voip_numbers_warehouse_status']);

        if (!$imsi) {
            throw new \LogicException('IMSI не найдена');
        }

//        $transactionMs = EventQueue::getDb()->beginTransaction();
        try {
            $imsi->msisdn = $accountTariff->voip_number;
            $imsi->actual_from = date(DateTimeZoneHelper::DATE_FORMAT);

            if (!$imsi->save()) {
                throw new ModelValidationException($imsi);
            }

            $card = $imsi->card;

            $card->client_account_id = $accountTariff->client_account_id;

            if (!$card->save()) {
                throw new ModelValidationException($card);
            }

            $number = $accountTariff->number;
            $number->imsi = $imsi->imsi;

            if (!$number->save()) {
                throw new ModelValidationException($number);
            }

            EventQueue::go(EventQueue::SYNC_TELE2_LINK_IMSI, [
                'account_tariff_id' => $accountTariff->id,
                'voip_number' => $accountTariff->voip_number,
                'imsi' => $imsi->imsi,
                'iccid' => $imsi->iccid,
            ]);

//            $transactionPg->commit();
//            $transactionMs->commit();
        } catch (\Exception $e) {
//            $transactionPg->rollBack();
//            $transactionMs->rollBack();
            throw $e;
        }

        return $imsi->imsi;
    }

    /**
     * @param integer $statusId
     * @return array|\yii\db\ActiveRecord
     */
    private static function getNextImsi($statusId)
    {
        return Imsi::find()->alias('i')
            ->joinWith('card c')
            ->andWhere([
                'i.profile_id' => ImsiProfile::ID_TELE2_TEST,
                'i.partner_id' => ImsiPartner::ID_TELE2,
                'c.status_id' => $statusId,
                'c.is_active' => 1,
                'i.is_active' => 1,
                'c.client_account_id' => null,
            ])
            ->one();
    }

    public static function linkImsi($requestId, $param)
    {
        return Tele2Adapter::me()->addSubscriber($requestId, $param['imsi'], $param['voip_number'], $param['account_tariff_id']);
    }
}
