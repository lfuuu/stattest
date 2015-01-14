<?php

namespace app\controllers;

use app\forms\support\SubmitTicketCommentForm;
use Yii;
use app\classes\BaseController;


class SupportController extends BaseController
{
    public function actionCommentTicket()
    {
        $model = new SubmitTicketCommentForm();
        $model->load(Yii::$app->request->bodyParams);

        if (!$model->save()) {
            foreach ($model->getErrors() as $errors) {
                foreach ($errors as $error) {
                    Yii::$app->session->addFlash('error', $error);
                }
            }
        }

        $this->redirect(Yii::$app->request->referrer);
    }
}