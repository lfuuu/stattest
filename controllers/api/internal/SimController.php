<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\web\NotImplementedHttpException;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\ImsiStatus;

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
     * @SWG\Get(tags = {"SIM"}, path = "/internal/sim/get-card-statuses", summary = "Список статусов SIM-карт", operationId = "GetCardStatuses",
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
     * @SWG\Get(tags = {"SIM"}, path = "/internal/sim/get-imsi-statuses", summary = "Список статусов IMSI", operationId = "GetImsiStatuses",
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
}