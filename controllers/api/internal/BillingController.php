<?php

namespace app\controllers\api\internal;

use Yii;
use app\classes\Assert;
use app\classes\ApiInternalController;
use app\models\billing\Trunk;
use app\models\billing\Server;

class BillingController extends ApiInternalController
{

    const NOA = 3;

    /**
     * @SWG\Definition(
     *   definition="test_auth_result",
     *   type="object",
     *   @SWG\Property(
     *     property="report",
     *     type="object",
     *     description="Отчет по вычислению маршрута звонка"
     *   ),
     *   @SWG\Property(
     *     property="summary",
     *     type="string",
     *     description="Результат вычисления маршрута звонка"
     *   )
     * ),
     * @SWG\Get(
     *   tags={"Работа с биллингом"},
     *   path="/internal/billing/test-auth",
     *   summary="Тестирование маршрутизации",
     *   operationId="Тестирование маршрутизации",
     *   @SWG\Parameter(name="trunkName",type="string",description="название транка",in="query"),
     *   @SWG\Parameter(name="srcNumber",type="string",description="номер с которого идет звонок",in="query"),
     *   @SWG\Parameter(name="dstNumber",type="string",description="номер на который идет звонок",in="query"),
     *   @SWG\Parameter(name="srcNoa",type="string",description="NOA srcNumber номера",in="query"),
     *   @SWG\Parameter(name="dstNoa",type="string",description="NOA dstNumber номера",in="query"),
     *   @SWG\Response(
     *     response=200,
     *     description="тестирование маршрутизации",
     *     @SWG\Definition(
     *       ref="#/definitions/test_auth_result"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @param string $trunkName
     * @param string $srcNumber
     * @param string $dstNumber
     * @param int $srcNoa
     * @param int $dstNoa
     * @return []
     * @throws \yii\base\Exception
     */
    public function actionTestAuth($trunkName, $srcNumber, $dstNumber, $srcNoa = self::NOA, $dstNoa = self::NOA)
    {
        /** @var Trunk $trunk */
        $trunk = Trunk::findOne(['name' => $trunkName]);
        Assert::isObject($trunk);

        /** @var Server $server */
        $server = Server::findOne($trunk->server_id);
        Assert::isObject($server);

        return $this->getResponse(
            $server->apiUrl . '/test/auth?' . http_build_query([
                'trunk_name' => $trunk->trunk_name,
                'src_number' => $srcNumber,
                'dst_number' => $dstNumber,
                'src_noa' => $srcNoa,
                'dst_noa' => $dstNoa,
            ])
        );
    }

    /**
     * @SWG\Definition(
     *   definition="test_call_result",
     *   type="object",
     *   @SWG\Property(
     *     property="report",
     *     type="object",
     *     description="Отчет по вычислению маршрута звонка"
     *   ),
     *   @SWG\Property(
     *     property="summary",
     *     type="string",
     *     description="Результат вычисления маршрута звонка"
     *   ),
     * ),
     * @SWG\Get(
     *   tags={"Работа с биллингом"},
     *   path="/internal/billing/test-call",
     *   summary="Тестирование звонка",
     *   operationId="Тестирование звонка",
     *   @SWG\Parameter(name="trunkName",type="string",description="название транка",in="query"),
     *   @SWG\Parameter(name="srcNumber",type="string",description="номер с которого идет звонок",in="query"),
     *   @SWG\Parameter(name="dstNumber",type="string",description="номер на который идет звонок",in="query"),
     *   @SWG\Parameter(name="srcNoa",type="string",description="NOA srcNumber номера",in="query"),
     *   @SWG\Parameter(name="dstNoa",type="string",description="NOA dstNumber номера",in="query"),
     *   @SWG\Response(
     *     response=200,
     *     description="тестирование звонка",
     *     @SWG\Definition(
     *       ref="#/definitions/test_call_result"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @param string $trunkName
     * @param string $srcNumber
     * @param string $dstNumber
     * @param int $srcNoa
     * @param int $dstNoa
     * @return []
     * @throws \yii\base\Exception
     */
    public function actionTestCall($trunkName, $srcNumber, $dstNumber, $srcNoa = self::NOA, $dstNoa = self::NOA)
    {
        /** @var Trunk $trunk */
        $trunk = Trunk::findOne(['name' => $trunkName]);
        Assert::isObject($trunk);

        /** @var Server $server */
        $server = Server::findOne($trunk->server_id);
        Assert::isObject($server);

        return $this->getResponse(
            $server->apiUrl . '/test/calc?' . http_build_query([
                'src_route' => $trunk->trunk_name,
                'dst_route' => $trunk->trunk_name,
                'src_number' => $srcNumber,
                'dst_number' => $dstNumber,
                'src_noa' => $srcNoa,
                'dst_noa' => $dstNoa,
            ])
        );
    }

    /**
     * @param string $request
     * @return []
     */
    private function getResponse($request)
    {
        $response = file_get_contents($request);
        $response = explode("\n", str_replace("\r", '', $response));

        $report = [];
        $summary = '';
        foreach ($response as $text) {
            list($type, $action, $params) = explode('|', $text);

            if ($type === 'RESULT') {
                $summary = $params;
            }

            $report[] = [
                'type' => $type,
                'action' => $action,
                'params' => $params,
            ];
        }

        return [
            'report' => $report,
            'summary' => $summary,
        ];
    }

}