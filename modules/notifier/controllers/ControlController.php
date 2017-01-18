<?php

namespace app\modules\notifier\controllers;

use Yii;
use app\classes\BaseController;
use app\modules\notifier\forms\ControlForm;

class ControlController extends BaseController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        $form = new ControlForm;

        if ($form->load(Yii::$app->request->post()) && $form->validate(['whitelist']) && $form->saveWhiteList()) {
            $this->redirect('/notifier/control');
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate(['country_code']) && $form->applyPublish()) {
            $this->redirect('/notifier/control');
        }

        return $this->render('view',
            [
                'dataForm' => $form,
            ]
        );
    }

}
