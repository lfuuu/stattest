<?php
namespace app\controllers;

use app\forms\contract\ContractEditForm;
use app\classes\BaseController;
use \Yii;
use yii\base\Exception;


class ContractController extends BaseController
{
    public function actionEdit($id)
    {
        $model = new ContractEditForm(['id' => $id]);

        if ($model->load(Yii::$app->request->post())) {
            $model->save();
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }
}
