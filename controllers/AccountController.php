<?php

namespace app\controllers;

use app\dao\ClientGridSettingsDao;
use app\forms\client\AccountEditForm;
use app\forms\client\ClientEditForm;
use app\models\ClientBP;
use app\models\ClientInn;
use app\models\ClientPayAcc;
use app\models\ClientSearch;
use Yii;
use app\classes\BaseController;
use app\classes\Assert;
use yii\base\Exception;
use yii\filters\AccessControl;
use app\models\LkWizardState;
use yii\helpers\Url;
use yii\web\Response;
use app\models\ClientAccount;


class AccountController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['clients.edit'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view', 'index', 'load-bp-statuses', 'unfix'],
                        'roles' => ['clients.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['set-block', 'set-voip-disable'],
                        'roles' => ['clients.restatus'],
                    ],
                ],
            ],
        ];
    }

    public function actionChangeWizardState($id, $state)
    {
        $accountId = $id;

        $account = ClientAccount::findOne($accountId);

        if (!$account || !LkWizardState::isBPStatusAllow($account->contract->business_process_status_id, $account->contract->id))
            throw new \Exception("Wizard не доступен на данном статусе бизнес процесса");

        $wizard = LkWizardState::findOne($account->contract->id);

        if (in_array($state, ['on', 'off', 'review', 'rejected', 'approve', 'first', 'next'])) {

            if ($state == "on" && !$wizard) {
                $wizard = new LkWizardState;
                $wizard->contract_id = $account->contract->id;
                $wizard->step = 1;
                $wizard->state = "process";
                $wizard->save();
            } else {

                Assert::isObject($wizard);

                if ($state == "off") {
                    $wizard->delete();
                } else {
                    if ($state == "first" || $state == "next") {
                        $wizard->step = ($state == "first" ? 1 : ($wizard->step < 4 ? $wizard->step + 1 : 4));
                        if ($wizard->step == 4) {
                            $state = "review";
                        } else {
                            $state = "process";
                        }
                    }

                    $wizard->state = $state;
                    $wizard->save();

                    if ($state == "approve") {
                        $wizard->add100Rub();
                    }
                }
            }
        }

        return $this->redirect(['client/view', 'id' => $id]);
    }

    public function actionCreate($parentId)
    {
        $model = new AccountEditForm(['contract_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['client/view', 'id' => $model->id]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);
    }

    public function actionEdit($id, $date = null)
    {
        $model = new AccountEditForm(['id' => $id, 'deferredDate' => $date]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['client/view', 'id' => $id]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);
    }

    public function actionSuperClientEdit($id, $childId)
    {
        $model = new ClientEditForm(['id' => $id]);

        if($childId===null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl().'&childId='.$childId);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['client/view', 'id' => $childId]);
        }

        return $this->render("superClientEdit", [
            'model' => $model
        ]);
    }

    public function actionIndex($bp = 0, $grid = 0)
    {
        $model = (new ClientSearch());
        $model->getGridSetting($bp, $grid);
        $model->setAttributes(Yii::$app->request->get());
        $dataProvider = $model->searchWithSetting();

        //var_dump($dataProvider); die;

        return $this->render('index', [
//          'searchModel' => $dataProvider,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    public function actionSearch()
    {
        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->isAjax) {
            $res = [];
            foreach ($dataProvider->models as $model)
                $res[] = [
                    'url' => Url::toRoute(['client/view', 'id' => $model->id]),
                    'value' => $model->contract->contragent->name_full,
                    'color' => $model->contract->getBusinessProcessStatus()['color'],
                    'id' => $model->id,
                ];
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $res;
        } else {
            if ($dataProvider->query->count() == 1)
                return $this->redirect(['client/view', 'id' => $dataProvider->query->one()->id]);
            else
                return $this->render('search', [
//              'searchModel' => $dataProvider,
                    'dataProvider' => $dataProvider,
                ]);
        }
    }
    public function actionUnfix()
    {
        //Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', '');
        return $this->redirect(Yii::$app->request->referrer);
        //return $this->goHome();
    }

    public function actionSetBlock($id)
    {
        $model = ClientAccount::findOne($id);
        if (!$model)
            throw new Exception('ЛС не найден');
        $model->is_blocked = !$model->is_blocked;
        $model->save();
        return $this->redirect(['client/view', 'id' => $id]);
    }

    public function actionSetVoipDisable($id)
    {
        $model = ClientAccount::findOne($id);
        if (!$model)
            throw new Exception('ЛС не найден');
        $model->voip_disabled = !$model->voip_disabled;
        $model->save();
        return $this->redirect(['client/view', 'id' => $id]);
    }

    public function actionLoadBpStatuses()
    {
        $processes = [];
        foreach (ClientBP::find()->orderBy("sort")->all() as $b) {
            $processes[] = ["id" => $b->id, "up_id" => $b->client_contract_id, "name" => $b->name];
        }

        $statuses = [];

        foreach (ClientGridSettingsDao::me()->getAllByParams(['show_as_status' => true]) as $s) {
            $statuses[] = ["id" => $s['id'], "name" => $s['name'], "up_id" => $s['grid_business_process_id']];
        }

        $res = ["processes" => $processes, "statuses" => $statuses];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    public function actionAdditionalInnList($accountId)
    {
        $account = ClientAccount::findOne($accountId);
        if(!$account)
            throw new Exception('Account does not exist');

        $model = new ClientInn();

        return $this->render('additional-inn-list', ['account' => $account, 'model' => $model]);
    }

    public function actionAdditionalInnCreate($accountId)
    {
        $account = ClientAccount::findOne($accountId);
        if(!$account)
            throw new Exception('Account does not exist');

        $model = new ClientInn();
        $model->load(Yii::$app->request->post());
        $model->client_id = $accountId;
        $model->save();

        return $this->redirect(['account/additional-inn-list', 'accountId' => $accountId]);
    }

    public function actionAdditionalInnDelete($id)
    {
        $model = ClientInn::findOne($id);
        if(!$model)
            throw new Exception('Inn does not exist');
        $model->is_active = 0;
        $model->save();

        return $this->redirect(['account/additional-inn-list', 'accountId' => $model->client_id]);
    }

    public function actionAdditionalPayAccList($accountId)
    {
        $account = ClientAccount::findOne($accountId);
        if(!$account)
            throw new Exception('Account does not exist');

        $model = new ClientPayAcc();

        return $this->render('additional-pay-acc-list', ['account' => $account, 'model' => $model]);
    }

    public function actionAdditionalPayAccCreate($accountId)
    {
        $account = ClientAccount::findOne($accountId);
        if(!$account)
            throw new Exception('Account does not exist');

        $model = new ClientPayAcc();
        $model->load(Yii::$app->request->post());
        $model->client_id = $accountId;
        $model->save();

        return $this->redirect(['account/additional-pay-acc-list', 'accountId' => $accountId]);
    }

    public function actionAdditionalPayAccDelete($id)
    {
        $model = ClientPayAcc::findOne($id);
        if(!$model)
            throw new Exception('Pay does not exist');
        $model->delete();

        return $this->redirect(['account/additional-pay-acc-list', 'accountId' => $model->client_id]);
    }
}
