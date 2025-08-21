<?php

namespace app\controllers\api\internal\voip;

use app\exceptions\ModelValidationException;
use app\models\Number;
use Yii;
use app\exceptions\web\NotImplementedHttpException;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use yii\base\InvalidParamException;

class MsTeamsController extends ApiInternalController
{

    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }


    /**
     * @SWG\Post(tags = {"Numbers"}, path = "/internal/voip/ms-teams/transfer-number-to/", summary = "Перенос номера в MS Teams", operationId = "ms-teams-transfer-number-to",
     *   @SWG\Parameter(name="number",type="string",description="номер телефона",in="formData",default=""),
     *   @SWG\Response(response = 200, description = "Получение информации о статусе переноса номера",
     *   )
     * )
     */

    public function actionTransferNumberTo()
    {
        $requestData = Yii::$app->request->post() ?: [];

        $model = DynamicModel::validateData(
            $requestData,
            [
                ['number', 'string'],
                ['number', 'trim'],
                ['number', 'required'],
            ]
        );

        if ($model->hasErrors()) {
            $errors = $model->getFirstErrors();
            throw new \InvalidArgumentException(reset($errors));
        }

        $number = Number::findOne(['number' => $model['number']]);

        if (!$number) {
            throw new InvalidParamException('Number not found');
        }

        if ($number->status == Number::STATUS_ACTIVE_MSTEAMS) { // already
            return ['current_status' => $number->status];
        }

        if (!in_array($number->status, Number::$statusGroup[Number::STATUS_GROUP_ACTIVE])) {
            throw new \InvalidArgumentException('Number not in active status');
        }


        if (!$number->is_in_msteams) {
            $number->is_in_msteams = 1;

            $transaction = Number::getDb()->beginTransaction();
            try {
                if (!$number->save()) {
                    throw new ModelValidationException($number);
                }

                Number::dao()->actualizeStatus($number);
                $transaction->commit();
            } catch (\Exception $e) {
                Yii::error($e);
                $transaction->rollBack();
                throw $e;
            }
            $number->refresh();
        }

        return ['current_status' => $number->status];
    }

    /**
     * @SWG\Post(tags = {"Numbers"}, path = "/internal/voip/ms-teams/return-number-from/", summary = "Вернут номер из MS Teams", operationId = "ms-teams-return-number-from",
     *   @SWG\Parameter(name="number",type="string",description="номер телефона",in="formData",default=""),
     *   @SWG\Response(response = 200, description = "Получение информации о статусе номера",
     *   )
     * )
     */

    public function actionReturnNumberFrom()
    {
        $requestData = Yii::$app->request->post() ?: [];

        $model = DynamicModel::validateData(
            $requestData,
            [
                ['number', 'string'],
                ['number', 'trim'],
                ['number', 'required'],
            ]
        );

        if ($model->hasErrors()) {
            $errors = $model->getFirstErrors();
            throw new \InvalidArgumentException(reset($errors));
        }

        $number = Number::findOne(['number' => $model['number']]);

        if (!$number) {
            throw new InvalidParamException('Number not found');
        }

        if (!in_array($number->status, Number::$statusGroup[Number::STATUS_GROUP_ACTIVE])) {
            throw new \InvalidArgumentException('Number not in active status');
        }

        if ($number->status != Number::STATUS_ACTIVE_MSTEAMS) { // already
            return ['current_status' => $number->status];
        }


        $transaction = Number::getDb()->beginTransaction();
        try {
            $number->is_in_msteams = 0;

            if (!$number->save()) {
                throw new ModelValidationException($number);
            }

            Number::dao()->actualizeStatus($number);
            $transaction->commit();
        } catch (\Exception $e) {
            Yii::error($e);
            $transaction->rollBack();
            throw $e;
        }

        $number->refresh();

        return ['current_status' => $number->status];
    }


    /**
     * @SWG\Get(tags = {"Numbers"}, path = "/internal/voip/ms-teams/get-numbers", summary = "Получение номеров в статусе 'MS Teams'", operationId = "ms-teams-get-numbers",
     *   @SWG\Response(response = 200, description = "Получение списка номеров",
     *   )
     * )
     */

    public function actionGetNumbers()
    {
        return Number::find()
            ->select('number')
            ->where([
                'status' => Number::STATUS_ACTIVE_MSTEAMS,
            ])->column();
    }
}
