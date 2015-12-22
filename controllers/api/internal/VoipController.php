<?php

namespace app\controllers\api\internal;

use Yii;
use DateTime;
use app\classes\ApiInternalController;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use app\models\billing\Calls;

class VoipController extends ApiInternalController
{

    private
        $accountId,
        $number;

    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    public function actionCalls()
    {
        $requestData = $this->getRequestParams();
        $this->getRequiredParams($requestData);

        $year   = isset($requestData['year']) ? $requestData['year'] : (new DateTime())->format('Y');
        $month  = isset($requestData['month']) ? $requestData['month'] : (new DateTime())->format('m');
        $offset = isset($requestData['offset']) ? $requestData['offset']: 0;
        $limit  = isset($requestData['limit']) ? $requestData['limit']: 1000;

        return Calls::dao()->getCalls($this->accountId, $this->number, $year, $month, $offset, $limit);
    }

    private function getRequiredParams(array $data)
    {
        $accountId = isset($data['account_id']) ? $data['account_id'] : null;
        $number = isset($data['number']) ? $data['number'] : null;

        if (!$accountId && !$number) {
            throw new BadRequestHttpException;
        }

        $this->accountId = $accountId;
        $this->number = $number;
    }

}