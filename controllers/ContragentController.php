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

    public function actionEdit($id, $childId = null)
    {
        $model = new ContragentEditForm(['id' => $id]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::toRoute(['client/clientview','id'=>$childId]));
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }
}
