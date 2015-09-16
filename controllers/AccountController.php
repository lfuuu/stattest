<?php

namespace app\controllers;

use app\forms\client\AccountEditForm;
use app\forms\client\ClientEditForm;
use app\models\Country;
use app\models\ClientInn;
use app\models\ClientPayAcc;
use Yii;
use app\classes\BaseController;
use app\classes\Assert;
use yii\web\Response;
use yii\base\Exception;
use yii\filters\AccessControl;
use app\models\LkWizardState;
use app\models\ClientAccount;
use app\models\ClientSuper;


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
                        'actions' => ['view', 'index', 'unfix'],
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

            if ($state == "on") {

                if ($wizard) {
                    $wizard->is_on = 1;
                    $wizard->step = 1;
                    $wizard->state = "process";
                    $wizard->save();
                } else {
                    LkWizardState::create(
                        $account->contract->id, 
                        0, 
                        ($account->contract->contragent->country_id == Country::HUNGARY ? "t2t" : "mcn")
                    );
                }
            } else {

                Assert::isObject($wizard);

                if ($state == "off") {
                    $wizard->is_on = 0;
                    $wizard->save();
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
        $model = new AccountEditForm(['id' => $id, 'historyVersionRequestedDate' => $date]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['account/edit', 'id' => $id, 'showLastChanges' => 1, 'date' => $model->historyVersionStoredDate]);
        }

        if(!($this->getFixClient() && $this->getFixClient()->id == $id)){
            if($id) {
                Yii::$app->session->set('clients_client', $id);
                $this->applyFixClient($id);
            }
        }

        return $this->render("edit", [
            'model' => $model,
            'addAccModel' => new ClientPayAcc(),
            'addInnModel' => new ClientInn(),
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

    public function actionSuperClientSearch($query)
    {
        if (!Yii::$app->request->isAjax)
            return;

        $result =
            ClientSuper::find()
                ->where('name LIKE "%' . preg_replace('#[\'"\-~!@\#$%\^&\*()_=\+\[\]{};:\s]#u', '%', $query) . '%"')
                ->limit(20)
                ->all();
        $output = [];

        foreach ($result as $client) {
            $output[] = [
                'id' => $client->id,
                'text' => $client->name . ' (#' . $client->id . ')',
            ];
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $output;
    }

    public function actionUnfix()
    {
        //Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', 0);
        Yii::$app->user->identity->restriction_client_id = 0;
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

    public function actionAdditionalInnCreate($accountId)
    {
        $account = ClientAccount::findOne($accountId);
        if(!$account)
            throw new Exception('Account does not exist');

        $model = new ClientInn();
        $model->load(Yii::$app->request->post());
        $model->client_id = $accountId;
        $model->save();

        return $this->redirect(['account/edit', 'id' => $accountId]);
    }

    public function actionAdditionalInnDelete($id)
    {
        $model = ClientInn::findOne($id);
        if(!$model)
            throw new Exception('Inn does not exist');
        $model->is_active = 0;
        $model->save();

        return $this->redirect(['account/edit', 'id' => $model->client_id]);
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

        return $this->redirect(['account/edit', 'id' => $accountId]);
    }

    public function actionAdditionalPayAccDelete($id)
    {
        $model = ClientPayAcc::findOne($id);
        if(!$model)
            throw new Exception('Pay does not exist');
        $model->delete();

        return $this->redirect(['account/edit', 'id' => $model->client_id]);
    }
}
