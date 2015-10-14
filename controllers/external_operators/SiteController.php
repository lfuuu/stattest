<?php

namespace app\controllers\external_operators;

use app\classes\operators\Operators;
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
    public $menuItem, $operatorsList = [], $operator;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'logout', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'who-is-it'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create-request', 'set-state'],
                        'allow' => true,
                        'roles' => ['external-operators.tt_create'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }
    public function beforeAction()
    {
        $this->operator = Yii::$app->session->get('operator');

        if (Yii::$app->user->identity && is_null($this->operator)) {
            $operators = Yii::$app->params['rights']['external-operators'];

            foreach ($operators['permissions'] as $key => $value) {
                if (strpos($key, 'operator_') === false) {
                    continue;
                }
                if (!Yii::$app->user->can('external-operators.' . $key)) {
                    continue;
                }
                $this->operatorsList[$key] = $value;
            }

            if ($this->action->uniqueId != 'site/who-is-it') {
                $operatorsCount = count($this->operatorsList);
                if ($operatorsCount > 1) {
                    $this->redirect('/site/who-is-it');
                }
                else if ($operatorsCount == 1) {
                    Yii::$app->session->set('operator', str_replace('operator_', '', array_pop(array_keys($this->operatorsList))));
                    $this->operator = Yii::$app->session->get('operator');
                }
                else {
                    return $this->actionLogout();
                }
            }
        }

        if ($this->action->id == 'error') {
            $this->layout = 'minimal.php';
        }

        return parent::beforeAction($this->action);
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
        $filter = Yii::$app->request->get('filter', []);
        $asFile = Yii::$app->request->get('as-file', 0);

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

        $operator = OperatorsFactory::me()->getOperator($this->operator);
        $report = $operator->getReport()->getReportResult($dateFrom, $dateTo, $filter['mode'], '');

        if ($asFile == 1) {
            $reportName = 'OnlimeDevices__' . $filter['mode'] . '__' . $dateFrom . '__' . $dateTo;

            Yii::$app->response->sendContentAsFile(
                $operator->GenerateExcel($report),
                $reportName . '.xls'
            );
            Yii::$app->end();
        }

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

    public function actionWhoIsIt() {
        $mode = Yii::$app->request->get('mode');

        if ($mode == 'do') {
            Yii::$app->session->set('operator', str_replace('operator_', '', Yii::$app->request->post('operator')));
            return $this->goHome();
        }

        $this->layout = 'external_operators/main';
        return $this->render('external_operators/who-is-it', [
            'operators' => $this->operatorsList,
        ]);
    }

    public function actionCreateRequest()
    {
        $operator = OperatorsFactory::me()->getOperator($this->operator);
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

        $operator = OperatorsFactory::me()->getOperator($this->operator);
        $model = $operator->requestStateForm;
        $scenario = Yii::$app->request->post('scenario');

        switch ($scenario) {
            case 'setComment':
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $bill->comment = Yii::$app->request->post('comment');
                    $bill->save();
                    $transaction->commit();
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                return $this->redirect(['set-state', 'bill_no' => $bill->bill_no]);
                break;
            case 'setFiles':
                $trouble->mediaManager->addFiles($files = 'files', $custom_names = 'custom_name_files');
                return $this->redirect(['set-state', 'bill_no' => $bill->bill_no]);
                break;
            default:
                if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($operator, $bill, $trouble)) {
                    return $this->redirect(['set-state', 'bill_no' => $bill->bill_no]);
                }
                break;
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
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm;
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
        Yii::$app->session->remove('operator');
        Yii::$app->user->logout();
        return $this->goHome();
    }

} 