<?php

namespace app\modules\notifier\controllers;

use Yii;
use app\classes\BaseController;
use app\modules\notifier\forms\SchemesForm;

class SchemesController extends BaseController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        $form = (new SchemesForm);

        if ($form->load() && $form->validate() && $form->save()) {
            $this->redirect('/notifier/schemes');
        }

        if ($form->hasErrors()) {
            Yii::$app->session->addFlash('error', 'Ошибка сохранения данных');
        }

        return $this->render('view',
            [
                'dataForm' => $form,
            ]
        );
    }

}
