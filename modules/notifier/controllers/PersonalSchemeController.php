<?php

namespace app\modules\notifier\controllers;

use Yii;
use app\classes\BaseController;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\modules\notifier\forms\PersonalSchemeForm;
use app\modules\notifier\filters\PersonalSchemeFilter;

class PersonalSchemeController extends BaseController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        if (!($clientAccount = $this->getFixClient()) instanceof ClientAccount) {
            Yii::$app->session->addFlash('error', 'Выберите клиента');
            return $this->redirect('/');
        }

        $form = new PersonalSchemeForm;
        $form->clientAccountId = $clientAccount->id;

        $formFilter = new PersonalSchemeFilter;
        $formFilter->load(Yii::$app->request->get());

        if ($form->load(Yii::$app->request->post(), 'FormData') && $form->validate()) {
            $form->saveData();
        }

        return $this->render('index',
            [
                'formData' => $form->loadData(),
                'formFilterModel' => $formFilter,
                'mailDeliveryLanguageOption' => $clientAccount->getOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE),
                'form' => $form,
            ]
        );
    }

}
