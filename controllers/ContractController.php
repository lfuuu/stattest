<?php
namespace app\controllers;

use app\forms\client\ContractEditForm;
use app\classes\BaseController;
use app\models\ClientContract;
use \Yii;
use yii\base\Exception;
use yii\filters\AccessControl;


class ContractController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['clients.edit'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['clients.read'],
                    ],
                ],
            ],
        ];
    }

    public function actionView($id)
    {
        $model = ClientContract::findOne($id);
        if(!$model)
            throw new Exception('Contract does not exists');

        $accountId = $model->getAccounts()[0]->id;
        return $this->redirect(['client/view', 'id' => $accountId]);
    }

    public function actionCreate($parentId, $childId = null)
    {
        $model = new ContractEditForm(['contragent_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['contract/edit','id'=>$model->id, 'childId'=>$childId, 'showLastChanges'=>1]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionEdit($id, $childId = null, $date = null)
    {
        $model = new ContractEditForm(['id' => $id, 'deferredDate' => $date]);

        if($childId===null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl().'&childId='.$childId);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['contract/edit','id'=>$id, 'childId'=>$childId, 'showLastChanges'=>1]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }
}
