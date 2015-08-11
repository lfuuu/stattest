<?php

namespace app\controllers;

use app\forms\client\ContragentEditForm;
use app\classes\BaseController;
use \Yii;
use yii\filters\AccessControl;


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
                        'actions' => ['edit', 'create'],
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
        $model = new ContragentEditForm(['id' => $id, 'historyVersionRequestedDate' => $date]);

        if(!($this->getFixClient() && $this->getFixClient()->getContract()->contragent_id == $id)){
            $contragentModel = $model->getContragentModel();
            $account = $contragentModel->getContracts()[0]->getAccounts()[0];
            if($account) {
                Yii::$app->session->set('clients_client', $account->id);
                $this->applyFixClient($account->id);
            }
        }

        if($childId===null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl().'&childId='.$childId);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $returnTo =
                Yii::$app->request->get('returnTo')
                    ?:['contragent/edit', 'id'=>$id, 'childId'=>$childId, 'showLastChanges'=>1];

            $this->redirect($returnTo);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }
}
