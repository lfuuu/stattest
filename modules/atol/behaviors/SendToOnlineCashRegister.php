<?php

namespace app\modules\atol\behaviors;

use app\classes\model\ActiveRecord;
use app\classes\payments\recognition\processors\RecognitionProcessor;
use app\exceptions\ModelValidationException;
use app\models\ClientContact;
use app\models\Currency;
use app\models\EventQueue;
use app\models\Payment;
use app\models\PaymentApiChannel;
use app\models\PaymentAtol;
use app\modules\atol\classes\Api;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\AfterSaveEvent;


class SendToOnlineCashRegister extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'postponeSend',
            ActiveRecord::EVENT_AFTER_UPDATE => 'changeClientId',
            // @todo при update надо оформлять корректирующий чек
            // @todo при delete надо сторнировать
        ];
    }

    /**
     * В соответствии с ФЗ−54 отправить данные в онлайн-кассу. А она сама отправит чек покупателю и в налоговую
     * Сейчас для надежности, чтобы не задержать основное действие или тем более не сфаталить его, надо как можно меньше действий - максимум поставить в очередь
     *
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function postponeSend(Event $event)
    {
        /** @var Payment $payment */
        $payment = $event->sender;

        if ($payment->sum > 0) {
            self::addEvent($payment->id, $payment->isNeedToSendAtol, $payment->checkOrganizationId);
        }
    }

    public static function addEvent($paymentId, $isForcePush = false, $checkOrganizationId = false)
    {
        // поставить в очередь для отправки
        EventQueue::go(\app\modules\atol\Module::EVENT_SEND, [
                'paymentId' => $paymentId,
                'isForcePush' => $isForcePush,
            ] + ($checkOrganizationId ? ['checkOrganizationId' => $checkOrganizationId] : [])
        );
    }

    /**
     * В соответствии с ФЗ−54 отправить данные в онлайн-кассу. А она сама отправит чек покупателю и в налоговую
     *
     * @param int $paymentId
     * @param bool $isForcePush
     * @return false|string
     */
    public static function send($paymentId, $isForcePush = false, $checkOrganizationId = false)
    {
        $payment = Payment::findOne(['id' => $paymentId]);
        if (!$payment) {
            throw new \InvalidArgumentException('Неправильный платеж ' . $paymentId);
        }

        if (in_array($payment->client_id, RecognitionProcessor::SPECIAL_ACCOUNT_IDS)) {
            return false;
        }

        // уже отправленные не отправляем
        if ($payment->currency !== Currency::RUB || $payment->paymentAtol) {
            return false;
        }

        $isToSend = false;
        if ($isForcePush) {
            $isToSend = true;
        } elseif ($payment->type == Payment::TYPE_ECASH) {
            $isToSend = true;
        } elseif ($payment->type == Payment::TYPE_API) {
            if ($checkOrganizationId) {
                $isToSend = true;
            } else {
                $apiChannel = PaymentApiChannel::findOne(['code' => $payment->ecash_operator]);
                if ($apiChannel && $apiChannel->check_organization_id) {
                    $checkOrganizationId = $apiChannel->check_organization_id;
                    $isToSend = true;
                }
            }
        }

        if (!$isToSend) {
            return false;
        }

        $client = $payment->client;
        $contacts = $client->getOfficialContact();

        $organizationId = $checkOrganizationId ?: $payment->client->contract->organization_id;

        list($uuid, $log) = Api::me()->sendSell(
            $payment->id,
            reset($contacts[ClientContact::TYPE_EMAIL]),
            reset($contacts[ClientContact::TYPE_PHONE]),
            $payment->sum, $organizationId);

        $paymentAtol = new PaymentAtol;
        $paymentAtol->id = $payment->id;
        $paymentAtol->uuid = $uuid;
        $paymentAtol->uuid_status = PaymentAtol::UUID_STATUS_SENT;
        $paymentAtol->uuid_log = $log;
        if (!$paymentAtol->save()) {
            // не фаталить, иначе API-запрос потом будет отправлен повторно
            Yii::error(implode(' ', $paymentAtol->getFirstErrors()));
        }

        // поставить в очередь для обновления статуса
        EventQueue::go(\app\modules\atol\Module::EVENT_REFRESH, [
                'paymentId' => $payment->id,
            ]
        );

        return $log;
    }

    /**
     * Обновить статус из онлайн-кассы
     *
     * @param int $paymentId
     * @return string $status
     * @throws \app\exceptions\ModelValidationException
     * @throws \InvalidArgumentException
     * @throws \yii\db\Exception
     * @throws \LogicException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \HttpRequestException
     */
    public static function refreshStatus($paymentId)
    {
        $paymentAtol = PaymentAtol::findOne(['id' => $paymentId]);
        if (!$paymentAtol) {
            throw new \InvalidArgumentException('Неправильный платеж ' . $paymentId);
        }

        if (!$paymentAtol->uuid) {
            throw new \LogicException('Платеж не был отправлен в онлайн-кассу ' . $paymentId);
        }

        $organizationId = $paymentAtol->payment->client->contract->organization_id;

        list($status, $log) = Api::me()->getStatus($paymentAtol->uuid, $organizationId);

        switch ($status) {
            case Api::RESPONSE_STATUS_WAIT:
                $paymentAtol->uuid_status = PaymentAtol::UUID_STATUS_SENT;
                break;
            case Api::RESPONSE_STATUS_FAIL:
                $paymentAtol->uuid_status = PaymentAtol::UUID_STATUS_FAIL;
                break;
            case Api::RESPONSE_STATUS_DONE:
                $paymentAtol->uuid_status = PaymentAtol::UUID_STATUS_SUCCESS;
                break;
        }

        $paymentAtol->uuid_log = $log;
        if (!$paymentAtol->save()) {
            throw new ModelValidationException($paymentAtol);
        }

        return $status;
    }

    /**
     * Поведение на изменение ЛС у платежа.
     *
     * @param AfterSaveEvent $event
     * @return bool
     */
    public function changeClientId(AfterSaveEvent $event)
    {
        // перенос не со спец ЛС
        if (
            !isset($event->changedAttributes['client_id'])
            || !in_array($event->changedAttributes['client_id'], RecognitionProcessor::SPECIAL_ACCOUNT_IDS)
        ) {
            return true;
        }

        /** @var Payment $payment */
        $payment = $event->sender;

        if ($payment->paymentAtol) {
            // чек уже отправлен в АТОЛ
            return true;
        }

        self::addEvent($payment->id);

        return true;
    }
}
