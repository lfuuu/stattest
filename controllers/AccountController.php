<?php

namespace app\controllers;

use app\forms\client\AccountEditForm;
use app\models\ClientBP;
use app\models\ClientGridSettings;
use app\models\ClientSearch;
use Yii;
use app\classes\BaseController;
use app\classes\Assert;
use yii\filters\AccessControl;
use app\models\LkWizardState;
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
                    'actions' => ['view', 'index', 'load-bp-statuses', 'unfix'],
                    'roles' => ['clients.read'],
                ],
                [
                    'allow' => true,
                    'actions' => ['edit', 'create'],
                    'roles' => ['clients.edit'],
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

        if (!$account || !LkWizardState::isBPStatusAllow($account->business_process_status_id, $account->id))
            throw new \Exception("Wizard не доступен на данном статусе бизнес процесса");

        $wizard = LkWizardState::findOne($accountId);

        if (in_array($state, ['on', 'off', 'review', 'rejected', 'approve', 'first', 'next'])) {

            if ($state == "on" && !$wizard) {
                $wizard = new LkWizardState;
                $wizard->contract_id = $accountId;
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

        $this->redirect(['client/view', 'id' => $id]);
    }

    public function actionCreate($parentId)
    {
        $model = new AccountEditForm(['contract_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['client/view', 'id' => $model->id]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);
    }

    public function actionEdit($id, $date = null)
    {
        $model = new AccountEditForm(['id' => $id, 'ddate' => $date]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['client/view', 'id' => $id]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionIndex()
    {
        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($dataProvider->query->count() == 1)
            return $this->redirect(['client/view', 'id' => $dataProvider->query->one()->id]);

        return $this->render('index', [
//          'searchModel' => $dataProvider,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUnfix($id)
    {
        //Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', '');
        return $this->goHome();
    }

    public function actionSetBlock($id)
    {
        $model = ClientAccount::findOne($id);
        if (!$model)
            throw new Exception('ЛС не найден');
        $model->is_blocked = !$model->is_blocked;
        $model->save();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }

    public function actionSetVoipDisable($id)
    {
        $model = ClientAccount::findOne($id);
        if (!$model)
            throw new Exception('ЛС не найден');
        $model->voip_disabled = !$model->voip_disabled;
        $model->save();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }

    public function actionLoadBpStatuses()
    {
        $processes = [];
        foreach (ClientBP::find()->orderBy("sort")->all() as $b) {
            $processes[] = ["id" => $b->id, "up_id" => $b->client_contract_id, "name" => $b->name];
        }

        $statuses = [];
        foreach (ClientGridSettings::find()->select(["id", "name", "grid_business_process_id"])->where(["show_as_status" => 1])->orderBy("sort")->all() as $s) {
            $statuses[] = ["id" => $s->id, "name" => $s->name, "up_id" => $s->grid_business_process_id];
        }

        $res = ["processes" => $processes, "statuses" => $statuses];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }
}
