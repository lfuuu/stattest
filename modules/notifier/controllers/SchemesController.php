<?php

namespace app\modules\notifier\controllers;

use app\classes\BaseController;
use app\modules\notifier\forms\SchemesForm;
use Yii;
use yii\base\InvalidParamException;

class SchemesController extends BaseController
{

    /**
     * @return string
     * @throws InvalidParamException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionIndex()
    {
        $form = new SchemesForm;

        if ($form->load() && $form->validate() && $form->save()) {
            $this->redirect('/notifier/schemes');
        }

        if ($form->hasErrors()) {
            Yii::$app->session->addFlash('error', 'Ошибка сохранения данных');
        }

        return $this->render('index', [
            'dataForm' => $form,
        ]);
    }

}
