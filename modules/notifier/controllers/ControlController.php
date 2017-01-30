<?php

namespace app\modules\notifier\controllers;

use app\classes\BaseController;
use app\modules\notifier\forms\ControlForm;
use Yii;
use yii\base\InvalidParamException;

class ControlController extends BaseController
{

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        return $this->render('view', [
            'dataForm' => (new ControlForm),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function actionApplyWhiteList()
    {
        $form = new ControlForm;

        if ($form->load(Yii::$app->request->post()) && $form->validate(['whitelist'])) {
            $form->saveWhiteList();
        } else {
            Yii::$app->session->setFlash('error', $form->getErrorsAsString());
        }

        $this->redirect('/notifier/control');
    }

    /**
     * @param int $countryCode
     * @throws InvalidParamException
     */
    public function actionApplyScheme($countryCode)
    {
        $form = new ControlForm;
        $form->countryCode = $countryCode;

        if ($form->validate(['countryCode'])) {
            $form->applyPublish();
        } else {
            Yii::$app->session->setFlash('error', $form->getErrorsAsString());
        }

        $this->redirect('/notifier/control');
    }

}
