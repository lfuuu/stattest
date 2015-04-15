<?php

namespace app\controllers;

use app\forms\contragent\ContragentEditForm;
use app\models\ClientContragent;
use app\classes\BaseController;


class ContragentController extends BaseController
{
    public function actionEdit($id)
    {
        $model = ClientContragent::findOne($id);
        $form = new ContragentEditForm();

        $form->setAttributes($model->getAttributes());

        return $this->render("edit", [
            "model" => $form
        ]);

    }
}
