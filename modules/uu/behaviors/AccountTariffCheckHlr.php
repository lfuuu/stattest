<?php

namespace app\modules\uu\behaviors;

use app\classes\adapters\Tele2Adapter;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\nnp\models\NdcType;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiPartner;
use app\modules\sim\models\ImsiProfile;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;


class AccountTariffCheckHlr extends Behavior
{
    const imsiPrefixRegExp = '/^25037/'; // Tele2 imsi prefix
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'doOn',
            ActiveRecord::EVENT_AFTER_UPDATE => 'doOff',
        ];
    }

    /**
     * Проверка при отключении услуги
     *
     * @param AfterSaveEvent $event
     */
    public function doOff(AfterSaveEvent $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        if (!$this->_check($event, false)) {
            return;
        }

        // нет изменений, или не выключили
        if (
            !isset($event->changedAttributes['tariff_period_id']) ||
            !(
                $event->changedAttributes['tariff_period_id']
                && !$accountTariff->tariff_period_id
            )
        ) {
            return;
        }

        EventQueue::go(EventQueue::SYNC_TELE2_UNSET_IMSI, [
            'account_tariff_id' => $accountTariff->id,
            'voip_number' => $accountTariff->voip_number,
        ]);
    }

    public static function unsetImsi($params)
    {
        $accountTariff = AccountTariff::findOne(['id' => $params['account_tariff_id']]);

        if (!$accountTariff) {
            throw new \LogicException('AccountTraiff ' . $params['account_tariff_id'] . ' не найден');
        }

        $number = $accountTariff->number;

        if (!$number) {
            throw new \LogicException('Номер у услуги не найден ' . $accountTariff->id);
        }

        $number->imsi = null;
        if (!$number->save()) {
            throw new ModelValidationException($number);
        }

        $transaction = Imsi::getDb()->beginTransaction();

        $partnerTele2Imsi = null;

        try {
            $imsis = Imsi::find()->where(['msisdn' => $number->number])->all();

            /** @var Imsi $imsi */
            foreach ($imsis as $imsi) {

                if ($imsi->partner_id = ImsiPartner::ID_TELE2) {
                    $partnerTele2Imsi = $imsi;
                }

                $imsi->msisdn = null;

                if (!$imsi->save()) {
                    throw new ModelValidationException($imsi);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        if ($partnerTele2Imsi && preg_match(self::imsiPrefixRegExp, $partnerTele2Imsi->imsi)) {
            EventQueue::go(EventQueue::SYNC_TELE2_UNLINK_IMSI, [
                'account_tariff_id' => $accountTariff->id,
                'voip_number' => $accountTariff->voip_number,
                'imsi' => $partnerTele2Imsi->imsi,
                'iccid' => $partnerTele2Imsi->iccid,
            ]);
        }
    }

    /**
     * проверка - работаем ли мы с этой услугой вообше
     *
     * @param AfterSaveEvent $event
     * @return bool
     */
    private function _check(AfterSaveEvent $event, $isWithWS = true)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        if ($accountTariff->service_type_id != ServiceType::ID_VOIP || $accountTariff->number->ndc_type_id != NdcType::ID_MOBILE) {
            return false;
        }

        if ($isWithWS && !($accountTariff->getParam('voip_numbers_warehouse_status') && $accountTariff->getParam('voip_numbers_warehouse_status') > 0)) {
            return false;
        }

        return true;
    }

    public function doOn(AfterSaveEvent $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        if (!$this->_check($event)) {
            return;
        }

        if ($accountTariff->number->imsi) {
            throw new \LogicException('IMSI уже прописан у номера ' . $accountTariff->voip_number);
        }

        EventQueue::go(EventQueue::SYNC_TELE2_GET_IMSI, [
            'account_tariff_id' => $accountTariff->id,
            'voip_number' => $accountTariff->voip_number,
            'voip_numbers_warehouse_status' => $accountTariff->voip_numbers_warehouse_status,
        ]);
    }

    public static function reservImsi($params)
    {
        if (!$params['voip_numbers_warehouse_status']) {
            throw new \LogicException('Склад не установлен');
        }

        $accountTariff = AccountTariff::findOne(['id' => $params['account_tariff_id']]);

        if (!$accountTariff) {
            throw new \LogicException('AccountTraiff ' . $params['account_tariff_id'] . ' не найден');
        }

        $transactionPg = Imsi::getDb()->beginTransaction();

        /** @var Imsi $foundImsi */
        $foundImsi = self::getNextImsi($params['voip_numbers_warehouse_status']);

        if (!$foundImsi) {
            throw new \LogicException('IMSI не найдена');
        }

        $card = $foundImsi->card;

//        $transactionMs = EventQueue::getDb()->beginTransaction();
        try {
            /** @var Imsi $imsi */
            foreach ($card->imsies as $imsi) {

                if (!in_array($imsi->profile_id, ImsiProfile::IDS_OWN)) {
                    continue;
                }

                $imsi->msisdn = $accountTariff->voip_number;
                $imsi->actual_from = date(DateTimeZoneHelper::DATE_FORMAT);

                if (!$imsi->save()) {
                    throw new ModelValidationException($imsi);
                }
            }

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

            $transactionPg->commit();
//            $transactionMs->commit();
        } catch (\Exception $e) {
            $transactionPg->rollBack();
//            $transactionMs->rollBack();
            throw $e;
        }

        return $card->iccid;
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
        return Tele2Adapter::me()->addSubscriber($requestId, $param['imsi'], $param['voip_number']);
    }

    public static function unlinkImsi($requestId, $params)
    {
        return Tele2Adapter::me()->deleteSubscriber($requestId, $params['imsi']);
    }
}
