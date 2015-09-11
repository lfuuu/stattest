<?php

namespace app\controllers\external_operators;

use app\classes\operators\OperatorsFactory;
use app\forms\external_operators\RequestOnlimeForm;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;
use app\models\LoginForm;
use app\dao\reports\ReportOnlimeDao;

class SiteController extends BaseController
{
    const OPERAPOR_CLIENT = 'onlime';

    public $menuItem;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'create-request'],
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
        $filter = Yii::$app->request->get('filter');
        $dateFrom = $dateTo = '';
        if ($filter['range']) {
            list ($dateFrom, $dateTo) = explode(' : ', $filter['range']);
        }
        $operator = OperatorsFactory::me()->getOperator('onlime');

        $this->layout = 'external_operators/main';
        $this->menuItem = 'indexReport';
        return $this->render('external_operators/default', [
            'defaultRange' => '',
            'operator' => $operator,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filter' => $filter,
        ]);
    }

    public function actionCreateRequest()
    {
        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator('onlime');
        $model = $operator->requestForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect('/');
        }

        $this->layout = 'external_operators/main';
        $this->menuItem = 'createRequest';
        return $this->render('external_operators/forms/create-request', [
            'operator' => $operator,
            'model' => $model,
        ]);
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
            $this->layout = 'minimal';

            return $this->render('external_operators/login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

} 