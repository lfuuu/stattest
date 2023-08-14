<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\monitoring\MonitorFactory;
use app\classes\monitoring\SyncErrorsUsageBase;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class MonitoringController extends ApiInternalController
{
    public function actionIndex()
    {
        throw new NotImplementedHttpException();
    }
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * @SWG\Get(tags = {"Monitoring"}, path = "/internal/monitoring/sync-errors-usage-voip/", summary = "Мониторинг ключевых событий. шибки синхронизации. Телефония", operationId = "sync_errors_usage_voip",
     *   @SWG\Response(response = 200, description = "данные об услугах",
     *     )
     *   ),
     * )
     *
     * @throws BadRequestHttpException
     */
    public function actionSyncErrorsUsageVoip()
    {
        $dataPrivider = MonitorFactory::me()->getSyncErrorsUsageVoip()->getResult();

        $rr = [];
        foreach ($dataPrivider->allModels as $r) {
            if (!isset($rr[$r['status']])) {
                $rr[$r['status']] = [];
            }

            $rr[$r['status']][] = [
                    'did' => $r['usage_id'],
                    'account_id' => (int)$r['account_id']
                ]
                + ($r['status'] == SyncErrorsUsageBase::STATUS_ACCOUNT_DIFF ? ['account_id2' => (int)$r['account_id2']] : []);
        }

        return $rr;
    }
}
