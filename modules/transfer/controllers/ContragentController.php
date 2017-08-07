<?php

namespace app\modules\transfer\controllers;

use app\classes\Assert;
use app\classes\BaseController;
use app\models\ClientContragent;
use app\models\ClientSuper;
use app\modules\transfer\forms\contragent\BaseForm;
use Yii;
use yii\base\InvalidParamException;

class ContragentController extends BaseController
{

    /**
     * @param int $contragentId
     * @return string
     * @throws \yii\base\Exception
     * @throws InvalidParamException
     */
    public function actionIndex($contragentId)
    {
        $contragent = ClientContragent::findOne($contragentId);
        Assert::isObject($contragent);

        $superClient = ClientSuper::findOne($contragent->super_id);
        Assert::isObject($superClient);

        $model = new BaseForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->process()) {
            $contragent = ClientContragent::findOne($model->sourceClientAccount);
            Assert::isObject($contragent);

            $this->redirect([
                '/transfer/contragent/success',
                'contragentId' => $contragent->id,
            ]);
        }

        $this->layout = '/minimal';
        return $this->render('index', [
            'contragent' => $contragent,
            'client' => $superClient,
            'model' => $model,
        ]);
    }

    /**
     * @param int $contragentId
     * @return string
     * @throws \yii\base\Exception
     * @throws InvalidParamException
     */
    public function actionSuccess($contragentId)
    {
        $contragent = ClientContragent::findOne($contragentId);
        Assert::isObject($contragent);

        $this->layout = '/minimal';
        return $this->render('success', [
            'contragent' => $contragent,
            'superClient' => ClientSuper::findOne($contragent->super_id),
        ]);
    }

}