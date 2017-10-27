<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\ModelValidationException;
use app\exceptions\web\NotImplementedHttpException;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiStatus;
use Exception;
use Yii;
use yii\base\InvalidParamException;

class SimController extends ApiInternalController
{
    const DEFAULT_LIMIT = 50;
    const MAX_LIMIT = 100;

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
     *   @SWG\Property(property = "status", type = "object", description = "Статус", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "actual_from", type = "string", description = "Действует с")
     * ),
     *
     * @SWG\Get(tags = {"SIM-card"}, path = "/internal/sim/get-cards", summary = "Список SIM-карт ЛС", operationId = "GetCards",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", required = true, default = ""),
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
    public function actionGetCards($client_account_id)
    {
        $query = Card::find()->where(['client_account_id' => $client_account_id]);
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->_simCardRecord($model);
        }

        return $result;
    }

    /**
     * @param Card $card
     * @return array
     */
    private function _simCardRecord(Card $card)
    {
        return [
            'iccid' => $card->iccid,
            'imei' => $card->imei,
            'is_active' => $card->is_active,
            'status' => $this->_getIdNameRecord($card->status),
            'imsies' => $this->_simImsiesRecord($card->imsies),
        ];
    }

    /**
     * @param Imsi[] $imsies
     * @return array
     */
    private function _simImsiesRecord($imsies)
    {
        $records = [];
        foreach ($imsies as $imsi) {
            $records[] = [
                'imsi' => $imsi->imsi,
                'msisdn' => $imsi->msisdn,
                'did' => $imsi->did,
                'is_anti_cli' => $imsi->is_anti_cli,
                'is_roaming' => $imsi->is_roaming,
                'is_active' => $imsi->is_active,
                'status' => $this->_getIdNameRecord($imsi->status),
                'actual_from' => $imsi->actual_from,
            ];
        }

        return $records;
    }

    /**
     * @SWG\Put(tags = {"SIM-card"}, path = "/internal/sim/edit-card", summary = "Редактировать SIM-карту", operationId = "EditSimCard",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "iccid", type = "integer", description = "ICCID", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "imsi", type = "integer", description = "IMSI", in = "query", required = true, default = ""),
     *
     *   @SWG\Parameter(name = "did", type = "integer", description = "Новое значение DID", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_anti_cli", type = "integer", description = "Новое значение Анти-АОН", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_roaming", type = "integer", description = "Новое значение Роуминг", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_active", type = "integer", description = "Новое значение Вкл.", in = "formData", default = ""),
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
            throw new InvalidParamException('Неправильные параметры iccid / client_account_id');
        }

        $imsies = $card->imsies;
        if (!isset($imsies[$imsi])) {
            throw new InvalidParamException('Неправильные параметры imsi');
        }

        $imsiObject = $imsies[$imsi];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            $imsiObject->setAttributes($post);
            if (!$imsiObject->save()) {
                throw new ModelValidationException($imsiObject);
            }

            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            \Yii::error($e);
            throw $e;
        }
    }
}