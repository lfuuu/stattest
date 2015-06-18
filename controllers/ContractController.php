<?php
namespace app\controllers;

use app\forms\client\ContractEditForm;
use app\classes\BaseController;
use \Yii;
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
                        'actions' => ['edit', 'create'],
                        'roles' => ['clients.edit'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate($parentId, $childId = null)
    {
        $model = new ContractEditForm(['contragent_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['client/view', 'id' => $childId]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionEdit($id, $childId = null, $date = null)
    {
        $model = new ContractEditForm(['id' => $id, 'ddate' => $date]);

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
}
