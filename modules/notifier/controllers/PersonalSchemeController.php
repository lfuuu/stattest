<?php

namespace app\modules\notifier\controllers;

use app\classes\BaseController;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\modules\notifier\forms\PersonalSchemeForm;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

class PersonalSchemeController extends BaseController
{

    /**
     * @return string
     * @throws InvalidParamException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionIndex()
    {
        if (!($clientAccount = $this->getFixClient()) instanceof ClientAccount) {
            Yii::$app->session->setFlash('error', 'Выберите клиента');
            return $this->redirect('/');
        }

        $form = new PersonalSchemeForm;
        $form->clientAccount = $clientAccount;

        if (
            $form->load(Yii::$app->request->post(), 'FormData')
            && $form->validate()
            && $form->saveData()
        ) {
            Yii::$app->session->setFlash('success', 'Данные персональной схемы оповещений обновлены');
        }

        return $this->render('grid', [
            'dataForm' => $form,
            'mailDeliveryLanguageOption' => $clientAccount->getOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE),
        ]);
    }

}
