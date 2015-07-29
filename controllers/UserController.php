<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\classes\BaseController;
use app\models\User;

class UserController extends BaseController
{

    public function actionAjaxDeptUsers($id)
    {
        if (!Yii::$app->request->isAjax)
            return;

        Yii::$app->response->format = Response::FORMAT_JSON;
        $usersList = User::getUserListByDepart($id, ['enabled' => true, 'primary' => 'user']);
        $output = [];

        foreach ($usersList as $user => $name) {
            $output[] = [
                'id' => $user,
                'text' => $name,
            ];
        }

        return $output;
    }

}