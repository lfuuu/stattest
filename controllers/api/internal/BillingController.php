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