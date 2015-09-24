<?php

namespace app\controllers\external_operators;

use Yii;
use DateTime;
use app\classes\Assert;
use app\classes\BaseController;
use app\models\Trouble;
use app\models\Bill;
use yii\filters\AccessControl;
use app\models\LoginForm;
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
                        'actions' => ['logout', 'index', 'create-request', 'set-state', 'download-report'],
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

        if ($filter['range']) {
            list ($dateFrom, $dateTo) = explode(' : ', $filter['range']);
        }
        else {
            $today = new DateTime('now');
            $firstDayThisMonth = clone $today;
            $lastDayThisMonth = clone $today;

            $dateFrom = $firstDayThisMonth->modify('first day of this month')->format('Y-m-d');
            $dateTo = $lastDayThisMonth->modify('last day of this month')->format('Y-m-d');
        }

        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator('onlime-devices');
        $report = $operator->getReport()->getReportResult($dateFrom, $dateTo, $filter['mode'], '');

        $this->layout = 'external_operators/main';
        $this->menuItem = 'indexReport';
        return $this->render('external_operators/default', [
            'operator' => $operator,
            'report' => $report,
            'filter' => [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'mode' => $filter['mode'],
            ],
        ]);
    }

    public function actionDownloadReport()
    {
        $filter = Yii::$app->request->get('filter');
        $dateFrom = $dateTo = '';
        if ($filter['range']) {
            list ($dateFrom, $dateTo) = explode(' : ', $filter['range']);
        }

        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator('onlime-devices');
        $operator->downloadReport($dateFrom, $dateTo, $filter);
    }

    public function actionCreateRequest()
    {
        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator('onlime-devices');
        $model = $operator->requestForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($operator)) {
            return $this->redirect(['set-state', 'bill_no' => $model->bill_no]);
        }

        $this->layout = 'external_operators/main';
        $this->menuItem = 'createRequest';
        return $this->render('external_operators/form', [
            'action' => 'create-request',
            'operator' => $operator,
            'model' => $model,
        ]);
    }

    public function actionSetState($bill_no)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);
        Assert::isObject($bill);
        $trouble = Trouble::findOne(['bill_no' => $bill->bill_no]);

        /** TODO: определять оператора от авторизованного пользователя */
        $operator = OperatorsFactory::me()->getOperator('onlime-devices');
        $model = $operator->requestStateForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($operator, $bill, $trouble)) {
            return $this->redirect(['set-state', 'bill_no' => $bill->bill_no]);
        }

        $this->layout = 'external_operators/main';
        return $this->render('external_operators/form', [
            'action' => 'set-state',
            'operator' => $operator,
            'model' => $model,
            'bill' => $bill,
            'trouble' => $trouble,
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