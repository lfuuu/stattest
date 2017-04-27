<?php

namespace app\modules\notifier\controllers;

use app\classes\Assert;
use app\classes\BaseController;
use app\models\Country;
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
        $form = new ControlForm;

        return $this->render('view', [
            'dataForm' => new ControlForm,
        ]);
    }

    /**
     * @throws InvalidParamException
     */
    public function actionApplyWhiteList()
    {
        $form = new ControlForm;

        if (
            $form->load(Yii::$app->request->post())
            && $form->validate(['whitelist'])
            && $form->applyWhiteList()
        ) {
            Yii::$app->session->setFlash('success', 'Данные белого списка оповещений сохранены');
        }

        $this->redirect('/notifier/control');
    }

    /**
     * @param int $countryCode
     * @throws InvalidParamException
     */
    public function actionApplyScheme($countryCode)
    {
        /** @var Country $country */
        $country = Country::findOne(['code' => $countryCode]);
        Assert::isObject($country);

        $form = new ControlForm;
        $form->countryCode = $countryCode;

        if ($form->validate(['countryCode']) && $form->applyPublish()) {
            Yii::$app->session->setFlash('success', 'Данные общей схемы "' . $country->name . '" оповещений обновлены');
        } else {
            Yii::$app->session->setFlash('error', $form->getErrorsAsString());
        }

        $this->redirect('/notifier/control');
    }

}
