<?php

namespace app\modules\notifier\controllers;

use app\classes\ApiInternalController;
use app\helpers\DateTimeZoneHelper;
use app\modules\notifier\models\Logger;
use Yii;

class ApiController extends ApiInternalController
{

    /**
     * @return string
     */
    public function actionApplyGlobalScheme()
    {
        $data = $this->getRequestParams();

        $log = Logger::find()
            ->where(['action' => Logger::ACTION_APPLY_SCHEME])
            ->andWhere(['value' => (int)$data['country_code']])
            ->andWhere(['IS', 'result', null])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        if (!is_null($log)) {
            $log->result = $data['updated'];
            $log->updated_at = date(DateTimeZoneHelper::DATETIME_FORMAT);
            if (!$log->save()) {
                Yii::error(print_r($log->getFirstErrors(), true));
            }
        }
    }

}