<?php

namespace app\modules\uu\behaviors;

use app\classes\adapters\Tele2Adapter;
use app\classes\HandlerLogger;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\nnp\models\NdcType;
use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiPartner;
use app\modules\sim\models\ImsiProfile;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;
use yii\db\Exception;


class AccountTariffCheckHlr extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'doEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'doEvent',
        ];
    }

    /**
     * Проверка при отключении услуги
     *
     * @param AfterSaveEvent $event
     */
    public function doEvent(AfterSaveEvent $event)
    {
        if (!\Yii::$app->isRus()) {
            return;
        }

        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        if (
            $accountTariff->service_type_id != ServiceType::ID_VOIP
            || $accountTariff->number->ndc_type_id != NdcType::ID_MOBILE
            || !array_key_exists('tariff_period_id', $event->changedAttributes)
            || $event->changedAttributes['tariff_period_id'] == $accountTariff->tariff_period_id
        ) {
            return;
        }

        $isTurnOn = !$event->changedAttributes['tariff_period_id'] && $accountTariff->tariff_period_id; // turn On
        $isTurnOff = $event->changedAttributes['tariff_period_id'] && !$accountTariff->tariff_period_id; // turn Off

        if (!$isTurnOn && !$isTurnOff) {
            HandlerLogger::me()->add('nothing (change tariff)');
            return;
        }

        if ($isTurnOn && !($accountTariff->getParam('voip_numbers_warehouse_status') && $accountTariff->getParam('voip_numbers_warehouse_status') > 0)) {
            HandlerLogger::me()->add('mob number without sim');
            return;
        }

        if ($isTurnOn) {
            if ($accountTariff->prev_usage_id) {
                HandlerLogger::me()->add('transfer sim');
                $this->transferCard($accountTariff);
            } else {
                // проставляем IMSI если его нет.
                if ($accountTariff->number && !$accountTariff->number->imsi) {
                    HandlerLogger::me()->add('get imsi');
                    EventQueue::go(EventQueue::SYNC_TELE2_GET_IMSI, [
                        'account_tariff_id' => $accountTariff->id,
                        'voip_number' => $accountTariff->voip_number,
                        'voip_numbers_warehouse_status' => $accountTariff->voip_numbers_warehouse_status ?: $accountTariff->getParam('voip_numbers_warehouse_status'),
                    ]);
                }
            }
            return;
        }

        // turn Off
        if (AccountTariff::find()->where(['prev_usage_id' => $accountTariff->id])->exists()) {
            HandlerLogger::me()->add('Мобильный номер установлен на перенос. Не отключаем IMSI.');
            return;
        }

        HandlerLogger::me()->add('unset imsi');
        EventQueue::go(EventQueue::SYNC_TELE2_UNSET_IMSI, [
            'account_tariff_id' => $accountTariff->id,
            'voip_number' => $accountTariff->voip_number,
        ]);
    }

    /**
     * Затягиваем SIM-карту на ЛС с услугой
     *
     * @param AccountTariff $accountTariff
     * @return void
     * @throws ModelValidationException
     */
    public function transferCard(AccountTariff $accountTariff)
    {
        $card = $accountTariff->number->imsiModel->card;

        $card->client_account_id = $accountTariff->client_account_id;

        if (!$card->save()) {
            throw new ModelValidationException($card);
        }
    }

    public static function unsetImsi($params)
    {
        $accountTariff = AccountTariff::findOne(['id' => $params['account_tariff_id']]);

        if (!$accountTariff) {
            throw new \LogicException('AccountTariff ' . $params['account_tariff_id'] . ' не найден');
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

        try {
            $imsis = Imsi::find()->where(['msisdn' => $number->number])->all();

            /** @var Imsi $imsi */
            foreach ($imsis as $imsi) {

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
/*
        if ($partnerTele2Imsi) {
            EventQueue::go(EventQueue::SYNC_TELE2_UNLINK_IMSI, [
                'account_tariff_id' => $accountTariff->id,
                'voip_number' => $accountTariff->voip_number,
                'imsi' => $partnerTele2Imsi->imsi,
                'iccid' => $partnerTele2Imsi->iccid,
            ]);
        }
*/
    }

    public static function reservImsi($params)
    {
        $accountTariff = AccountTariff::findOne(['id' => $params['account_tariff_id']]);

        if (!$accountTariff) {
            throw new \LogicException('AccountTraiff ' . $params['account_tariff_id'] . ' не найден');
        }

        $transactionPg = Imsi::getDb()->beginTransaction();

        if (isset($params['card'])) {
            if (!($params['card'] instanceof Card)) {
                throw new \InvalidArgumentException('Card not Card model');
            }
            $card = $params['card'];
        } else {
            if (!$params['voip_numbers_warehouse_status']) {
                throw new \LogicException('Склад не установлен');
            }

            /** @var Imsi $foundImsi */
            $foundImsi = self::getNextImsi($params['voip_numbers_warehouse_status']);

            if (!$foundImsi) {
                throw new \LogicException('IMSI не найдена');
            }

            $card = $foundImsi->card;
        }

//        $transactionMs = EventQueue::getDb()->beginTransaction();

        $linkedImsi = null;
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

                if ($imsi->profile_id == ImsiProfile::ID_MSN_RUS) {
                    $linkedImsi = $imsi;
                }
            }

            if (!$linkedImsi) {
                throw new \LogicException('не найдена IMSI');
            }

            $card->client_account_id = $accountTariff->client_account_id;

            if (!$card->save()) {
                throw new ModelValidationException($card);
            }

            $number = $accountTariff->number;
            $number->imsi = $linkedImsi->imsi;

            if (!$number->save()) {
                throw new ModelValidationException($number);
            }
/*
            EventQueue::go(EventQueue::SYNC_TELE2_LINK_IMSI, [
                'account_tariff_id' => $accountTariff->id,
                'voip_number' => $accountTariff->voip_number,
                'imsi' => $linkedImsi->imsi,
                'iccid' => $linkedImsi->iccid,
            ]);
*/
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
                'i.profile_id' => ImsiProfile::ID_MSN_RUS,
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
