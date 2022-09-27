<?php

namespace app\controllers;

use app\classes\Language;
use app\models\LoginGetCodeForm;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;
use app\models\LoginForm;
use yii\web\Response;
use yii\web\ResponseFormatterInterface;

class SiteController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'get-code', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'change-language'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionIndex()
    {
        if (\Yii::$app->is2fAuth() && !\Yii::$app->user->identity->phone_mobile) {
            \Yii::$app->session->addFlash('error', "Вступила в силу обязательная двухфакторная аутентификацию. </br> Просим заполнить номер мобильного телефона на который будет приходить код.");
            return $this->redirect('/user/profile');
        }

        return $this->redirect('/?module=tt&action=view_type&type_pk=2&folder=256&filtred=true');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $this->layout = 'empty';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionGetCode()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            if (!\Yii::$app->request->isPost || !Yii::$app->request->post()) {
                throw new \InvalidArgumentException('Bad request1');
            }

            $form = new LoginGetCodeForm();

            if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
                throw new \InvalidArgumentException(implode('<br />' . PHP_EOL, $form->getFirstErrors()));
            }

            return ['status' => 'ok', 'code_make' => $form->makeCode()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionChangeLanguage($lang = null, $country = null)
    {
        Language::setCurrentLanguage(
            $lang ?
                :
                ($country ? Language::getLanguageByCountryId($country) : null)
        );
        return $this->goBack();
    }
}
