<?php

namespace app\controllers;

use app\models\ClientContragent;
use app\classes\BaseController;
use app\models\ClientPerson;
use \Yii;
use yii\base\Exception;


class ContragentController extends BaseController
{
    public function actionEdit($id)
    {
        $model = ClientContragent::findOne($id);
        if ($model === null)
            new Exception('Id not exists');

        $person = new ClientPerson();
        $find = $person->findOne($id);
        if ($find)
            $person = $find;
        else
            $person->contraget_id = $id;

        $person->load(Yii::$app->request->post());
        $model->load(Yii::$app->request->post());

        if ($model->validate() && $person->validate()) {
            $model->save();
            $person->save();
            //return $this->goBack();
        }

        return $this->render("edit", [
            'model' => $model,
            'person' => $person
        ]);

    }
}
