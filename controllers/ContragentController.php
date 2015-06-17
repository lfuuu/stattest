<?php

namespace app\controllers;

use app\forms\contragent\ContragentEditForm;
use app\classes\BaseController;
use \Yii;
use yii\base\Exception;
use yii\helpers\Url;


class ContragentController extends BaseController
{
    public function actionCreate($parentId, $childId = null)
    {
        $model = new ContragentEditForm(['super_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::toRoute(['client/clientview','id'=>$childId]));
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionEdit($id, $childId = null, $date = null)
    {
        $model = new ContragentEditForm(['id' => $id, 'ddate' => $date]);

        if($childId===null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl().'&childId='.$childId);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::toRoute(['client/clientview','id'=>$childId]));
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }
}
