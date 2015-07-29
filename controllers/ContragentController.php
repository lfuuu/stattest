<?php

namespace app\controllers;

use app\forms\client\ContragentEditForm;
use app\classes\BaseController;
use \Yii;
use yii\filters\AccessControl;
use app\models\ClientSuper;
use app\models\ClientContragent;
use app\forms\contragent\ContragentTransferForm;
use app\classes\Assert;

class ContragentController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['edit', 'create', 'transfer', 'transfer-success'],
                        'roles' => ['clients.edit'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate($parentId, $childId = null)
    {
        $model = new ContragentEditForm(['super_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['client/view','id'=>$childId]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionEdit($id, $childId = null, $date = null)
    {
        $model = new ContragentEditForm(['id' => $id, 'deferredDate' => $date]);

        if($childId===null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl().'&childId='.$childId);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['client/view','id'=>$childId]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionTransfer($id)
    {
        $contragent = ClientContragent::findOne($id);
        Assert::isObject($contragent);

        $client = ClientSuper::findOne($contragent->super_id);

        $model = new ContragentTransferForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->process()) {
            $contragent = ClientContragent::find()->where(['super_id' => $model->targetClientAccount])->limit(1)->one();
            Assert::isObject($contragent);

            $this->redirect([
                'contragent/transfer-success',
                'id' => $contragent->id,
            ]);
        }

        $this->layout = 'minimal';
        return $this->render('transfer', [
            'contragent' => $contragent,
            'client' => $client,
            'model' => $model,
        ]);
    }

    public function actionTransferSuccess($id)
    {
        $contragent = ClientContragent::findOne($id);
        Assert::isObject($contragent);

        $this->layout = 'minimal';
        return $this->render('transfer_success', [
            'contragent' => $contragent,
        ]);
    }

}
