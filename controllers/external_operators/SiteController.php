<?php

namespace app\controllers\external_operators;

use Yii;
use DateTime;
use yii\filters\AccessControl;
use app\classes\BaseController;
use app\models\LoginForm;
use app\classes\operators\OperatorOnlime;
use app\classes\operators\OperatorsFactory;

class SiteController extends BaseController
{
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
                        'actions' => ['logout', 'index', 'create-request', 'set-state'],
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
        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator(OperatorOnlime::OPERATOR_CLIENT);

        $today = new DateTime('now');
        $firstDayThisMonth = clone $today;
        $lastDayThisMonth = clone $today;

        $currentRange =
            $firstDayThisMonth->modify('first day of this month')->format('Y-m-d') .
            ' : ' .
            $lastDayThisMonth->modify('last day of this month')->format('Y-m-d');

        if (isset($filter['range']))
            $currentRange = $filter['range'];

        $this->layout = 'external_operators/main';
        $this->menuItem = 'indexReport';
        return $this->render('external_operators/default', [
            'currentRange' => $currentRange,
            'operator' => $operator,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filter' => $filter,
        ]);
    }

    public function actionCreateRequest()
    {
        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator(OperatorOnlime::OPERATOR_CLIENT);
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

    public function actionSetState($id)
    {
        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator(OperatorOnlime::OPERATOR_CLIENT);
        $model = $operator->requestStateForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            print 'aaaa';
            exit;
            return $this->redirect('/');
        }

        $this->layout = 'minimal';
        return $this->render('external_operators/forms/set-state', [
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