<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\ModelValidationException;
use app\exceptions\web\NotImplementedHttpException;
use app\models\EventQueue;
use app\models\Number;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiStatus;
use app\modules\sim\models\RegionSettings;
use Exception;
use Yii;
use yii\base\InvalidParamException;

class SimController extends ApiInternalController
{
    const DEFAULT_LIMIT = 50;
    const MAX_LIMIT = 100;
    const EDIT_CARD_ERROR_CODE_WRONG_CARD = 1;
    const EDIT_CARD_ERROR_CODE_WRONG_IMSI = 2;
    const EDIT_CARD_ERROR_CODE_NUMBER_NOT_FOUND = 3;
    const EDIT_CARD_ERROR_CODE_WRONG_REGION = 4;
    const EDIT_CARD_ERROR_CODE_NUMBER_NOT_MCN = 5;
    const EDIT_CARD_ERROR_CODE_NUMBER_NO_DATA = 6;
    const EDIT_CARD_ERROR_CODE_NUMBER_OCCUPIED = 7;

    use IdNameRecordTrait;

    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(tags = {"SIM-card"}, path = "/internal/sim/get-card-statuses", summary = "Список статусов SIM-карт", operationId = "GetCardStatuses",
     *
     *   @SWG\Response(response = 200, description = "Список статусов SIM-карт",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @return array
     */
    public function actionGetCardStatuses()
    {
        $query = CardStatus::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"SIM-card"}, path = "/internal/sim/get-imsi-statuses", summary = "Список статусов IMSI", operationId = "GetImsiStatuses",
     *
     *   @SWG\Response(response = 200, description = "Список статусов IMSI",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @return array
     */
    public function actionGetImsiStatuses()
    {
        $query = ImsiStatus::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "simCardRecord", type = "object",
     *   @SWG\Property(property = "iccid", type = "integer", description = "ICCID"),
     *   @SWG\Property(property = "imei", type = "integer", description = "IMEI"),
     *   @SWG\Property(property = "is_active", type = "integer", description = "Вкл."),
     *   @SWG\Property(property = "status", type = "object", description = "Статус", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "imsies", type = "array", description = "Массив IMSI", @SWG\Items(ref = "#/definitions/simImsiRecord"))
     * ),
     *
     * @SWG\Definition(definition = "simImsiRecord", type = "object",
     *   @SWG\Property(property = "imsi", type = "integer", description = "IMSI"),
     *   @SWG\Property(property = "msisdn", type = "integer", description = "MSISDN"),
     *   @SWG\Property(property = "did", type = "integer", description = "DID"),
     *   @SWG\Property(property = "is_anti_cli", type = "integer", description = "Анти-АОН"),
     *   @SWG\Property(property = "is_roaming", type = "integer", description = "Роуминг"),
     *   @SWG\Property(property = "is_active", type = "integer", description = "Вкл."),
     *   @SWG\Property(property = "is_default", type = "integer", description = "По-умолчанию"),
     *   @SWG\Property(property = "status", type = "object", description = "Статус", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "profile", type = "object", description = "Статус", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "actual_from", type = "string", description = "Действует с")
     * ),
     *
     * @SWG\Get(tags = {"SIM-card"}, path = "/internal/sim/get-cards", summary = "Список SIM-карт ЛС", operationId = "GetCards",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "iccid", type = "string", description = "ICCID", in = "query", required = false, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список SIM-карт ЛС",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/simCardRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $client_account_id
     * @return array
     */
    public function actionGetCards($client_account_id, $iccid = null)
    {
        $query = Card::find()
            ->where(['client_account_id' => $client_account_id])
            ->with('status', 'imsies', 'imsies.profile', 'imsies.status');

        $iccid && $query->andWhere(['iccid' => $iccid]);

        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->simCardRecord($model);
        }

        return $result;
    }

    /**
     * @param Card $card
     * @return array
     */
    protected function simCardRecord(Card $card)
    {
        return [
            'iccid' => (string)$card->iccid,
            'imei' => (string)$card->imei,
            'is_active' => $card->is_active,
            'region' => $this->regionRecord($card->region_id),
            'status' => $this->_getIdNameRecord($card->status),
            'imsies' => $this->simImsiesRecord($card->imsies),
            'sim_type' => $this->_getIdNameRecord($card->type),
        ];
    }

    /**
     * @param Imsi[] $imsies
     * @return array
     */
    protected function simImsiesRecord($imsies)
    {
        $records = [];
        foreach ($imsies as $imsi) {
            $records[] = [
                'imsi' => (string)$imsi->imsi,
                'msisdn' => (string)$imsi->msisdn,
                'did' => (string)$imsi->did,
                'is_anti_cli' => $imsi->is_anti_cli,
                'is_roaming' => $imsi->is_roaming,
                'is_active' => $imsi->is_active,
                'is_default' => $imsi->is_default,
                'status' => $this->_getIdNameRecord($imsi->status),
                'profile' => $this->_getIdNameRecord($imsi->profile),
                'actual_from' => $imsi->actual_from,
            ];
        }

        return $records;
    }

    /**
     * @param int $regionId
     * @return array
     */
    protected function regionRecord($regionId)
    {
        $record = [];

        if ($regionSettings = RegionSettings::findByRegionId($regionId)) {
            $record = [
                'id' => $regionSettings->region_id,
                'name' => $regionSettings->getRegionFullName(),
            ];
        }

        return $record;
    }

    /**
     * @SWG\Put(tags = {"SIM-card"}, path = "/internal/sim/edit-card", summary = "Редактировать SIM-карту", operationId = "EditSimCard",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "iccid", type = "integer", description = "ICCID", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "imsi", type = "integer", description = "IMSI", in = "query", required = true, default = ""),
     *
     *   @SWG\Parameter(name = "did", type = "integer", description = "Новое значение DID (пустое значегние - NULL)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "msisdn", type = "integer", description = "Новое значение MSISDN (пустое значегние - NULL)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_anti_cli", type = "integer", description = "Новое значение Анти-АОН", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_roaming", type = "integer", description = "Новое значение Роуминг", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_active", type = "integer", description = "Новое значение Вкл.", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_default", type = "integer", description = "По-умолчанию", in = "formData", default = ""),
     *
     *   @SWG\Response(response = 200, description = "SIM-карта отредактирована",
     *     @SWG\Schema(type = "boolean", description = "true - успешно")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $client_account_id
     * @param int $iccid
     * @param int $imsi
     * @return int
     * @throws \Exception
     */
    public function actionEditCard($client_account_id, $iccid, $imsi)
    {
        $card = Card::findOne(['iccid' => $iccid, 'client_account_id' => $client_account_id]);
        if (!$card) {
            throw new InvalidParamException('Не найдена карта по iccid и client_account_id', self::EDIT_CARD_ERROR_CODE_WRONG_CARD);
        }

        $imsies = $card->imsies;
        if (!isset($imsies[$imsi])) {
            throw new InvalidParamException('Неправильные параметры imsi - нет такой imsi для данного iccid', self::EDIT_CARD_ERROR_CODE_WRONG_IMSI);
        }

        $post = Yii::$app->request->post();
        $post = array_map(function ($value) {
            return
                $value === 'NULL' || $value === '' ?
                    NULL :
                    (is_bool($value) ? (int)$value : $value);
        }, $post);

        if (!empty($post['msisdn'])) {
            $msisdn = $post['msisdn'];

            $number = Number::findOne(['number' => $msisdn]);
            if (!$number) {
                throw new InvalidParamException('Неверный msisdn, не найден в voip_numbers', self::EDIT_CARD_ERROR_CODE_NUMBER_NOT_FOUND);
            }

            if (!RegionSettings::checkIfRegionsEqual($number->region, $card->region_id)) {
                throw new InvalidParamException('Регион номера и регион SIM-карты не совместимы', self::EDIT_CARD_ERROR_CODE_WRONG_REGION);
            }

            try {
                $isMcnNumber = $number->isMcnNumber();
            } catch (Exception $e) {
                throw new InvalidParamException('Не удалось получить данные по номеру', self::EDIT_CARD_ERROR_CODE_NUMBER_NO_DATA);
            }

            if (!$isMcnNumber) {
                // временно отключаем, так как данные о переезде к нам приходят с запозданием из БДПН (первоисточник)
                //throw new InvalidParamException('Выбранный номер не принадлежит МСН Телеком', self::EDIT_CARD_ERROR_CODE_NUMBER_NOT_MCN);
            }

            $imsiExists = Imsi::find()
                ->andWhere(['msisdn' => $msisdn])
                ->andWhere('iccid != ' . $iccid)
                ->one();
            if ($imsiExists) {
                throw new InvalidParamException('Данный номер уже связан с другой SIM картой', self::EDIT_CARD_ERROR_CODE_NUMBER_OCCUPIED);
            }
        }

        $imsiObject = $imsies[$imsi];

        $transaction = Yii::$app->db->beginTransaction();
        $transactionSim = Card::getDb()->beginTransaction();
        try {
            $imsiObject->setAttributes($post);
            if (!$imsiObject->save()) {
                throw new ModelValidationException($imsiObject);
            }

            $transaction->commit();
            $transactionSim->commit();
            return true;
        } catch (Exception $e) {
            $transactionSim->rollBack();

            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            \Yii::error($e);
            throw $e;
        }
    }


    /**
     * @SWG\Get(tags = {"SIM-card"}, path = "/internal/sim/get-subscriber-status", summary = "Получить статус SIM-карты", operationId = "getSubscriberStatus",
     *   @SWG\Parameter(name = "imsi", type = "integer", description = "IMSI", in = "query", required = true, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Статус SIM-карты",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @return array
     */
    public function actionGetSubscriberStatus($imsi)
    {
        $this->_checkImsi($imsi);

        $event = EventQueue::go(EventQueue::SYNC_TELE2_GET_STATUS, ['imsi' => $imsi]);

        $result = $this->_waitEvent($event);

        $json = json_decode($result, true);

        if ($json) {
            $isConnected = $json['isConnected'];
            if (isset($json["GetResponse"]["MOAttributes"]["nsGetSubscriberData"]["nsSubscriberData"])) {
                return [$json["GetResponse"]["MOAttributes"]["nsGetSubscriberData"]["nsSubscriberData"] + ['isConnected' => $isConnected]];
            }
            return $json;
        }

        throw new \BadMethodCallException('answer error: ' . var_export($result, true));
    }

    private function _waitEvent(EventQueue $event)
    {
        $usleep = 200000; // 0.2 sec
        $count = 0;
        $result = false;
        do {
            usleep($usleep);
            $event->refresh();

            if ($event->status == EventQueue::STATUS_OK && $event->trace) {
                $result = $event->trace;
                break;
            }

        } while ($count++ < 120 && !$result);

        if (!$result) {
            throw new \RuntimeException('Timeout', 502);
        }

        return $result;
    }

    private function _checkImsi($imsi)
    {
        if (!$imsi || !preg_match('/^25\d{13}/', $imsi)) {
            throw new \InvalidArgumentException('bad IMSI');
        }
    }

    private function _checkMsisdn($msisdn)
    {
        if (!$msisdn || !preg_match('/^7\d{10}/', $msisdn)) {
            throw new \InvalidArgumentException('bad MSISDN');
        }
    }


    /**
     * @SWG\Get(tags = {"SIM-card"}, path = "/internal/sim/add-call-forwarding-on-not-reachable", summary = "Установить переадресацию при недоступности", operationId = "addCallForwardingOnNotReachable",
     *   @SWG\Parameter(name = "imsi", type = "integer", description = "IMSI", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "msisdn", type = "integer", description = "IMSI", in = "query", required = true, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Статус SIM-карты",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @return array
     */
    public function actionAddCallForwardingOnNotReachable($imsi, $msisdn)
    {
        $this->_checkImsi($imsi);
        $this->_checkMSISDN($msisdn);

        $event = EventQueue::go(EventQueue::SYNC_TELE2_SET_CFNRC, ['imsi' => $imsi, 'msisdn' => $msisdn]);

        $result = $this->_waitEvent($event);

        $json = json_decode($result, true);

        if ($json) {
            return $json;
        }

        throw new \BadMethodCallException('answer error: ' . var_export($result, true));
    }


    /**
     * @SWG\Get(tags = {"SIM-card"}, path = "/internal/sim/remove-call-forwarding-on-not-reachable", summary = "Снять переадресацию при недоступности", operationId = "removeCallForwardingOnNotReachable",
     *   @SWG\Parameter(name = "imsi", type = "integer", description = "IMSI", in = "query", required = true, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Статус SIM-карты",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @return array
     */
    public function actionRemoveCallForwardingOnNotReachable($imsi)
    {
        $this->_checkImsi($imsi);

        $event = EventQueue::go(EventQueue::SYNC_TELE2_UNSET_CFNRC, ['imsi' => $imsi]);

        $result = $this->_waitEvent($event);

        $json = json_decode($result, true);

        if ($json) {
            return $json;
        }

        throw new \BadMethodCallException('answer error: ' . var_export($result, true));
    }


}
