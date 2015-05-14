<?php

namespace app\controllers;

use app\forms\contragent\ContragentEditForm;
use app\models\ClientContragent;
use app\classes\BaseController;
use app\models\ClientPerson;
use \Yii;
use yii\base\Exception;


class ContragentController extends BaseController
{
    public function actionEdit($id)
    {
        $model = new ContragentEditForm(['id' => $id]);

        if ($model->load(Yii::$app->request->post())) {
            $model->save();
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }
}
