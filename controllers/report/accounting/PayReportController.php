<?php

namespace app\controllers\report\accounting;

use app\classes\BaseController;
use app\modules\atol\behaviors\SendToOnlineCashRegister;
use app\models\filter\PayReportFilter;
use Yii;

class PayReportController extends BaseController
{
    /**
     * Вывод списка
     *
     * @return string
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\Exception
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \HttpRequestException
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $this->view->title = 'Платежи';
        $filterModel = new PayReportFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionSendToAtol($id)
    {
        try {
            $log = SendToOnlineCashRegister::send($id);
            Yii::$app->session->setFlash('success', $log);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRefreshStatus($id)
    {
        try {
            $status = SendToOnlineCashRegister::refreshStatus($id);
            Yii::$app->session->setFlash('success', $status);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
}