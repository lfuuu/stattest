<?php

namespace app\controllers;

use app\classes\Assert;
use app\classes\BaseController;
use app\classes\HttpClient;
use app\exceptions\ModelValidationException;
use app\forms\client\AccountEditForm;
use app\forms\client\ClientEditForm;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientInn;
use app\models\ClientPayAcc;
use app\models\ClientSuper;
use app\models\Country;
use app\models\EventQueue;
use app\models\LkWizardState;
use app\modules\webhook\models\Call;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Response;


class AccountController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                        'actions' => ['set-block', 'set-voip-disable', 'fix-fin-lock'],
                        'roles' => ['clients.restatus'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @param string $state
     * @return Response
     * @throws \Exception
     */
    public function actionChangeWizardState($id, $state)
    {
        $accountId = $id;

        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(['id' => $accountId]);

        if (!$account || !LkWizardState::isBPStatusAllow($account->contract->business_process_status_id,
                $account->contract->id)
        ) {
            throw new \LogicException("Wizard не доступен на данном статусе бизнес процесса");
        }

        $wizard = LkWizardState::findOne(['contract_id' => $account->contract->id]);

        if (in_array($state, ['on', 'off', 'review', 'rejected', 'approve', 'first', 'next'])) {

            if ($state === "on") {

                if ($wizard) {
                    $wizard->is_on = 1;
                    $wizard->step = 1;
                    $wizard->state = "process";
                    $wizard->save();
                } else {
                    LkWizardState::create(
                        $account->contract->id,
                        0,
                        LkWizardState::TYPE_RUSSIA
                    );
                }
            } else {

                Assert::isObject($wizard);

                if ($state === "off") {
                    $wizard->is_on = 0;
                    $wizard->save();
                } else {
                    if ($state === "first" || $state === "next") {
                        $wizard->step = ($state === "first" ? 1 : ($wizard->step < 4 ? $wizard->step + 1 : 4));
                        if ($wizard->step == 4) {
                            $state = "review";
                        } else {
                            $state = "process";
                        }
                    }

                    $wizard->state = $state;
                    $wizard->save();
                }
            }
        }

        return $this->redirect(['client/view', 'id' => $id]);
    }

    public function actionChangeWizardType($id, $type)
    {
        $accountId = $id;

        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(['id' => $accountId]);

        if (
            !$account
            || !($contract = $account->contract)
            || !LkWizardState::isBPStatusAllow($contract->business_process_status_id, $contract->id)
            || !($wizard = $contract->lkWizardState)

        ) {
            throw new \LogicException("Wizard не доступен на данном статусе бизнес процесса");
        }

        if (!in_array($type, array_keys(LkWizardState::$name))) {
            throw new InvalidParamException('Неверный тип Wizardа');
        }

        $wizard->type = $type;

        if (!$wizard->save()) {
            throw new ModelValidationException($wizard);
        }

        return $this->redirect(['client/view', 'id' => $accountId]);
    }

    /**
     * @param int $parentId
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     */
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

    /**
     * @param int $id
     * @param string $date
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id, $date = null)
    {
        $model = new AccountEditForm(['id' => $id, 'historyVersionRequestedDate' => $date]);
        $post = Yii::$app->request->post();

        if ($post && $model->load($post) && $model->validate() && $model->save()) {
            return $this->redirect([
                'account/edit',
                'id' => $id,
                'showLastChanges' => 1,
                'date' => $model->historyVersionStoredDate
            ]);
        }

        if (!($this->getFixClient() && $this->getFixClient()->id == $id)) {
            if ($id) {
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

    /**
     * @param int $id
     * @param int $childId
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionSuperClientEdit($id, $childId)
    {
        $model = new ClientEditForm(['id' => $id]);

        if ($childId === null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl() . '&childId=' . $childId);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['client/view', 'id' => $childId]);
        }

        return $this->render("superClientEdit", [
            'model' => $model
        ]);
    }

    /**
     * @param string $query
     * @return array
     */
    public function actionSuperClientSearch($query)
    {
        if (!Yii::$app->request->isAjax) {
            return [];
        }

        $result = ClientSuper::find()
            ->where('name LIKE "%' . preg_replace('#[\'"\-~!@\#$%\^&\*()_=\+\[\]{};:\s]#u', '%', $query) . '%"')
            ->orWhere(['id' => preg_replace('#\D#', '', $query)])
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

    /**
     * @return Response
     */
    public function actionUnfix()
    {
        // Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', 0);
        Yii::$app->user->identity->restriction_client_id = 0;
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function actionSetBlock($id)
    {
        $model = ClientAccount::findOne($id);
        if (!$model) {
            throw new Exception('ЛС не найден');
        }

        $model->is_blocked = !$model->is_blocked;
        $model->save();

        return $this->redirect(['client/view', 'id' => $id]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function actionSetVoipDisable($id)
    {
        $model = ClientAccount::findOne($id);
        if (!$model) {
            throw new Exception('ЛС не найден');
        }

        $model->voip_disabled = !$model->voip_disabled;
        $model->save();
        return $this->redirect(['client/view', 'id' => $id]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function actionFixFinLock($id)
    {
        $account = ClientAccount::findOne($id);
        if (!$account) {
            throw new Exception('ЛС не найден');
        }

        $warnings = $account->voipWarnings;
        $lockByCredit = isset($warnings[ClientAccount::WARNING_CREDIT]) || isset($warnings[ClientAccount::WARNING_FINANCE]);

        if (!$lockByCredit) {
            \Yii::$app->session->addFlash('error', 'Не найдена блокировка');
            return $this->redirect(['client/view', 'id' => $id]);
        }

        if (!\Yii::$app->isRus()) {
            \Yii::$app->session->addFlash('error', 'Доступно только в России');
            return $this->redirect(['client/view', 'id' => $id]);
        }

        $httpClient = (new HttpClient())
            ->setResponseFormat(HttpClient::FORMAT_RAW_URLENCODED)
            ->createRequest()
            ->setMethod('DELETE')
            ->setUrl("http://reg99.mcntelecom.ru:8033/v1/api/client_lock/" . $account->id);

        try {
            $response = $httpClient->send();
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', 'Произошла ошибка во время удаления блокировки');
            \Yii::$app->session->addFlash('error', $e->getMessage());
            return $this->redirect(['client/view', 'id' => $id]);
        }

        if ($response->getIsOk()) {
            \Yii::$app->session->addFlash('success', 'Блокировки стерты');
            \Yii::$app->session->addFlash('success', $response->getContent());
        } else {
            \Yii::$app->session->addFlash('error', 'Произошла ошибка во время удаления блокировки');
            \Yii::$app->session->addFlash('error', $response->getContent());
        }

        return $this->redirect(['client/view', 'id' => $id]);
    }


    /**
     * @param int $accountId
     * @return Response
     * @throws Exception
     */
    public function actionAdditionalInnCreate($accountId)
    {
        $account = ClientAccount::findOne($accountId);

        if (!$account) {
            throw new Exception('Account does not exist');
        }

        try {
            $model = new ClientInn();
            $model->load(Yii::$app->request->post());
            $model->client_id = $accountId;
            if (!$model->save()) {
                throw new ModelValidationException($model);
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect(['account/edit', 'id' => $accountId]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function actionAdditionalInnDelete($id)
    {
        $model = ClientInn::findOne($id);
        if (!$model) {
            throw new Exception('Inn does not exist');
        }

        $model->is_active = 0;
        $model->save();

        return $this->redirect(['account/edit', 'id' => $model->client_id]);
    }

    /**
     * @param int $accountId
     * @return Response
     * @throws Exception
     */
    public function actionAdditionalPayAccCreate($accountId)
    {
        $account = ClientAccount::findOne($accountId);
        if (!$account) {
            throw new Exception('Account does not exist');
        }

        $model = new ClientPayAcc();
        $model->load(Yii::$app->request->post());
        $model->client_id = $accountId;
        $model->save();

        return $this->redirect(['account/edit', 'id' => $accountId]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function actionAdditionalPayAccDelete($id)
    {
        $model = ClientPayAcc::findOne($id);
        if (!$model) {
            throw new Exception('Pay does not exist');
        }

        $model->delete();

        return $this->redirect(['account/edit', 'id' => $model->client_id]);
    }

    public function actionCall($id, $contact_id)
    {
        $account = ClientAccount::findOne(['id' => $id]);
        Assert::isObject($account);

        $contact = ClientContact::findOne(['id' => $contact_id]);
        Assert::isObject($contact);
        Assert::isTrue($contact->isPhone());

        $userAbon = \Yii::$app->user->identity->phone_work;
        Assert::isNotEmpty($userAbon, 'Не установлен внутрениий номер пользователя');

        $phone = preg_replace('/[^\d]/', '', $contact->data);

        Call::clean();

        $call = Call::findOne(['abon' => $userAbon, 'calling_number' => $phone]);

        if (!$call) {
            $call = new Call;
            $call->abon = $userAbon;
            $call->calling_number = $phone;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$call->save()) {
                throw new ModelValidationException($call);
            }

            EventQueue::go(EventQueue::MAKE_CALL, ['abon' => $userAbon, 'calling_number' => $phone]);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return $this->redirect($account->getUrl());
    }

    public function actionCallDirect($phone)
    {
        $userAbon = \Yii::$app->user->identity->phone_work;
        Assert::isNotEmpty($userAbon, 'Не установлен внутрениий номер пользователя');

        $phone = preg_replace('/[^\d]/', '', $phone);

        Call::clean();

        $call = Call::findOne(['abon' => $userAbon, 'calling_number' => $phone]);

        if (!$call) {
            $call = new Call;
            $call->abon = $userAbon;
            $call->calling_number = $phone;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$call->save()) {
                throw new ModelValidationException($call);
            }

            EventQueue::go(EventQueue::MAKE_CALL, ['abon' => $userAbon, 'calling_number' => $phone]);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return $this->redirect(Yii::$app->request->referrer);


    }
}