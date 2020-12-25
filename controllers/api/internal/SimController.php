<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\ModelValidationException;
use app\exceptions\web\NotImplementedHttpException;
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
            throw new InvalidParamException('Неправильные параметры iccid / client_account_id');
        }

        $imsies = $card->imsies;
        if (!isset($imsies[$imsi])) {
            throw new InvalidParamException('Неправильные параметры imsi');
        }

        $post = Yii::$app->request->post();
        $post = array_map(function ($value) {
            return
                $value === 'NULL' || $value === '' ?
                    NULL :
                    (is_bool($value) ? (int)$value : $value);
        }, $post);

        if (!empty($post['did'])) {
            $number = Number::findOne(['number' => $post['did']]);
            if (!$number) {
                throw new InvalidParamException('Неверный did');
            }

            if (!RegionSettings::checkIfRegionsEqual($number->region, $card->region_id)) {
                throw new InvalidParamException('Регион номера и регион SIM-карты не совметимы');
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
}