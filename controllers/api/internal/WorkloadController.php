<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\web\NotImplementedHttpException;
use app\models\voip\filter\CdrWorkload;
use Yii;

/**
 * Данные о нагрузке на номер
 *
 * Class WorkloadController
 */
class WorkloadController extends ApiInternalController
{
    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(tags = {"Numbers"}, path = "/internal/workload/get-number-workload", summary = "Нагрузка на номер за период времени", operationId = "Нагрузка на номер за период времени",
     *     @SWG\Parameter(name = "date", type = "string", description = "Interval of workload calculation", in = "query", default = ""),
     *     @SWG\Parameter(name = "number", type = "string", description = "Phone number to calculation workload", in = "query", default = ""),
     *     @SWG\Parameter(name = "period", type = "string", description = "Calculation interval", in = "query", default = "hour"),
     *
     *     @SWG\Response(response = 200, description = "Нагрузка на номер за период времени",
     *         @SWG\Schema(type = "array", @SWG\Items(
     *                                          type="object",
     *                                          @SWG\Property(property="interval", type="string"),
     *                                          @SWG\Property(property="workload", type="string")))
     *     ),
     *     @SWG\Response(response = "default", description = "Ошибки",
     *         @SWG\Schema(ref = "#/definitions/error_result")
     *     )
     * )
     * @return array
     */
    public function actionGetNumberWorkload()
    {
        $model = new CdrWorkload();
        $model->load(Yii::$app->request->get());

        return $model->getNumberWorkload();
    }
}
