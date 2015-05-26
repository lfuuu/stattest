<?php
namespace app\controllers;

use app\forms\contract\ContractEditForm;
use app\classes\BaseController;
use \Yii;
use yii\base\Exception;
use yii\helpers\Url;


class ContractController extends BaseController
{
    public function actionCreate($parentId, $childId = null)
    {
        $model = new ContractEditForm(['contragent_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::toRoute(['client/clientview', 'id' => $childId]));
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionEdit($id, $childId = null)
    {
        $model = new ContractEditForm(['id' => $id]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::toRoute(['client/clientview', 'id' => $childId]));
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }
}
