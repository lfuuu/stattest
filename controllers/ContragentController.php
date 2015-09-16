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

        $request = Yii::$app->request->post();
        $notSave = (isset($request['notSave']) && $request['notSave']);
        if ($model->load($request) && !$notSave && $model->validate() && $model->save()) {
            $this->redirect(['client/view','id'=>$childId]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionEdit($id, $childId = null, $date = null)
    {
        $model = new ContragentEditForm(['id' => $id, 'historyVersionRequestedDate' => $date]);

        if(!($this->getFixClient() && $this->getFixClient()->getContract()->contragent_id == $id)){
            $contragentModel = $model->getContragentModel();
            $contracts = $contragentModel->getContracts();

            if (sizeof($contracts)) {
                $account = $contragentModel->getContracts()[0]->getAccounts()[0];
                if ($account) {
                    Yii::$app->session->set('clients_client', $account->id);
                    $this->applyFixClient($account->id);
                }
            }
        }

        if($childId===null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl().'&childId='.$childId);
        }

        $showLastChanges = false;
        $request = Yii::$app->request->post();
        $notSave = (isset($request['notSave']) && $request['notSave']);
        if ($model->load($request) && !$notSave && $model->validate() && $model->save()) {
            $showLastChanges = true;
        }

        return $this->render("edit", [
            'model' => $model,
            'showLastChanges' => $showLastChanges,
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
