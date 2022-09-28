<?php

namespace app\controllers;

use ActiveRecord\ModelException;
use app\classes\Assert;
use app\classes\BaseController;
use app\classes\grid\account\telecom\reports\IncomeFromCustomersFolder;
use app\classes\grid\account\telecom\reports\IncomeFromManagersAndUsagesFolder;
use app\classes\grid\GridFactory;
use app\classes\traits\AddClientAccountFilterTraits;
use app\exceptions\ModelValidationException;
use app\forms\client\AccountEditForm;
use app\forms\client\ContractEditForm;
use app\forms\client\ContragentEditForm;
use app\models\ClientAccount;
use app\models\ClientBlockedComment;
use app\models\ClientContact;
use app\models\ClientSearch;
use app\models\ClientSuper;
use app\models\EntryPoint;
use app\models\EventQueue;
use app\models\Number;
use app\models\Saldo;
use app\models\Trouble;
use app\models\UsageCallChat;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageTechCpe;
use app\models\UsageTrunk;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;
use app\modules\uu\filter\AccountTariffFilter;
use kartik\widgets\ActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\Response;

class ClientController extends BaseController
{
    use AddClientAccountFilterTraits;

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
                        'roles' => ['clients.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['clients.new'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'save-comment' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws Exception
     */
    public function actionView($id)
    {
        if (\Yii::$app->is2fAuth() && !\Yii::$app->user->identity->phone_mobile) {
            \Yii::$app->session->addFlash('error', 'Вступила в силу обязательная двухфакторная аутентификацию. </br> Просим заполнить номер мобильного телефона на который будет приходить код.');
            return $this->redirect('/user/profile');
        }

        $account = ClientAccount::findOne($id);
        if (!$account) {
            throw new Exception('Client not found');
        }

        // Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', $account->id);
        $this->applyFixClient($account->id);

        $client = ClientSuper::findOne($account->super_id);

        $contractForm = new ContractEditForm(['id' => $account->contract_id]);

        $troubles = Trouble::find()
            ->andWhere([
                'client' => $account->client,
                'server_id' => 0,
                'is_closed' => 0
            ])
            ->orderBy('`date_creation` DESC')
            ->all();

        $serverTroubles = Trouble::findAll(['id' => Trouble::dao()->getServerTroublesIDsForClient($account)]);

        $services = [];

        $services['voip'] = UsageVoip::find()
            ->where(['client' => $account->client])
            ->with('voipNumber.city', 'connectionPoint')
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['device'] = UsageTechCpe::find()
            ->where(['client' => $account->client])
            ->hideNotLinked()
            ->orderBy(['actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['welltime'] = UsageWelltime::find()
            ->where(['client' => $account->client])
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['extra'] = UsageExtra::find()
            ->where(['client' => $account->client])
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['virtpbx'] = UsageVirtpbx::find()
            ->where(['client' => $account->client])
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['sms'] = UsageSms::find()
            ->where(['client' => $account->client])
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['ipport'] = UsageIpPorts::find()
            ->where(['client' => $account->client])
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['voip_reserve'] = Number::find()
            ->where(['status' => Number::STATUS_NOTACTIVE_RESERVED])
            ->andWhere(['client_id' => $account->id])
            ->all();

        $services['trunk'] = UsageTrunk::find()
            ->where(['client_account_id' => $account->id])
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $services['call_chat'] = UsageCallChat::find()
            ->where(['client' => $account->client])
            ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
            ->all();

        $uuFilterModel = null;
        if ($account->account_version === ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $uuFilterModel = new AccountTariffFilter('');
            $this->_addClientAccountFilter($uuFilterModel);
        }

        // редактирование контактов
        $contacts = $account->allContacts;
        $post = Yii::$app->request->post();
        if (isset($post['ClientContact'])) {

            if (Yii::$app->request->isAjax) {
                // ajax-валидация
                $models = [];
                $modelIds = array_keys($post['ClientContact']);
                foreach ($modelIds as $modelId) {
                    $models[$modelId] = new ClientContact();
                }

                Model::loadMultiple($models, $post);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validateMultiple($models);
            }

            // создать/отредактировать контакты
            //
            // заготовка новой модели
            $clientContactNew = new ClientContact();
            $clientContactNew->client_id = $id;

            $contacts = $this->crudMultiple($contacts, $post, $clientContactNew);

            if ($this->validateErrors) {
                Yii::$app->session->setFlash('error', implode('. ', $this->validateErrors));
            } else {
                Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            }
        }

        return
            $this->render(
                'view',
                [
                    'client' => $client,
                    'account' => $account,
                    'contractForm' => $contractForm,
                    'troubles' => $troubles,
                    'serverTroubles' => $serverTroubles,
                    'services' => $services,
                    'uuFilterModel' => $uuFilterModel,
                    'contacts' => $contacts,
                    'isDanycom' => $client->entry_point_id == EntryPoint::ID_MNP_RU_DANYCOM,
                ]
            );
    }

    /**
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $contragent = new ContragentEditForm();
        $contragent->load($request);
        $contract = new ContractEditForm();
        $contract->load($request);
        $account = new AccountEditForm();
        $account->load($request);

        $notSave = (isset($request['notSave']) && $request['notSave']);
        if ($request && !$notSave) {
            $transaction = Yii::$app->db->beginTransaction();
            $commit = false;
            $super = new ClientSuper();
            $super->setAttribute('name', 'autocreate');
            if ($super->save()) {
                unset($request['ContragentEditForm']['super_id']);
                $contragent = new ContragentEditForm(['super_id' => $super->id]);
                if ($contragent->load($request) && $contragent->validate() && $contragent->save()) {
                    $super->name = $contragent->name;
                    $super->save();
                    $contract = new ContractEditForm(['contragent_id' => $contragent->id]);
                    if ($contract->load($request) && $contract->validate() && $contract->save()) {
                        $account = new AccountEditForm(['id' => $contract->newClient->id]);
                        $account->load($request) && $account->validate();
                        if ($account->load($request) && $account->validate() && $account->save()) {
                            Trouble::dao()->createTrouble(
                                $account->id,
                                Trouble::TYPE_CONNECT,
                                Trouble::SUBTYPE_CONNECT,
                                'Заявка на подключение услуг'
                            );
                            $commit = true;
                        }
                    }
                }
            }

            if ($commit) {
                $transaction->commit();
                return $this->redirect(['client/view', 'id' => $account->id]);
            } else {
                $transaction->rollback();
            }
        }

        return $this->render('create',
            [
                'contragent' => $contragent,
                'account' => $account,
                'contract' => $contract
            ]
        );
    }

    /**
     * @param int $businessProcessId
     * @param null $folderId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionGrid($businessProcessId, $folderId = null)
    {
        $accountGrid = GridFactory::me()->getAccountGridByBusinessProcessId($businessProcessId);
        $gridFolder = $accountGrid->getFolder($folderId);
        $gridFolder->setAttributes(Yii::$app->request->get());
        $gridFolder->initExtraValues();
        $dataProvider = $gridFolder->spawnDataProvider();

        return $this->render('index',
            [
                'dataProvider' => $dataProvider,
                'activeFolder' => $gridFolder,
            ]
        );
    }

    /**
     * @return array|string|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionSearch()
    {
        $searchQuery = Yii::$app->request->queryParams;

        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search($searchQuery);

        if (Yii::$app->request->isAjax) {
            $res = [];
            /** @var ClientAccount $model */
            foreach ($dataProvider->models as $model) {
                if (isset(Yii::$app->request->queryParams['contractNo'])) {
                    $res[] = [
                        'url' => Url::toRoute(['client/view', 'id' => $model->id]),
                        'value' => $model->contract->number,
                        'color' => $model->contract->businessProcessStatus->color,
                        'id' => $model->id,
                    ];
                }
                if (isset($searchQuery['is_term'])) {

                    $contragent = $model->contract->contragent;
                    $name = htmlspecialchars($contragent->name ?: $contragent->name_full);

                    $res[] = [
                        'id' => $model->id,
                        'value' => $model->getAccountTypeAndId() . ' - ' . $name,
                    ];
                } else {
                    $res[] = [
                        'url' => Url::toRoute(['client/view', 'id' => $model->id]),
                        'value' => htmlspecialchars($model->contract->contragent->name ?: $model->contract->contragent->name_full),
                        'color' => $model->contract->businessProcessStatus->color,
                        'id' => $model->id,
                        'accountType' => $model->getAccountType(),
                    ];
                }
            }

            Yii::$app->response->format = Response::FORMAT_JSON;
            return $res;
        } else {
            if ($dataProvider->query->count() == 1) {
                return $this->redirect(['client/view', 'id' => $dataProvider->query->one()->id]);
            } else {
                return $this->render('search',
                    [
                        // 'searchModel' => $dataProvider,
                        'dataProvider' => $dataProvider,
                    ]
                );
            }
        }
    }

    /**
     * @param int $id
     * @param int $clientId
     * @return Response
     * @throws Exception
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function actionCancelSaldo($id, $clientId)
    {
        $saldo = Saldo::find()->where(['id' => $id, 'client_id' => $clientId])->one();

        Assert::isObject($saldo);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $saldo->delete();
            ClientAccount::dao()->updateBalance($clientId);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Создание админа ЛК из карточки ЛС.
     *
     * @param int $account_id
     * @param int $admin_email_id
     * @return Response
     */
    public function actionAddAdminLk($account_id, $admin_email_id)
    {
        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(['id' => $account_id]);

        Assert::isObject($account);

        /** @var ClientContact $contact */
        $contact = ClientContact::find()
            ->where([
                'id' => $admin_email_id,
            ])->one();

        Assert::isObject($contact);

        /** @var ClientContact $adminEmail */
        $phone = $account
            ->getContacts()
            ->andWhere([
                'type' => ClientContact::TYPE_PHONE,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->select('data')
            ->scalar();

        EventQueue::goWithIndicator(
            EventQueue::CORE_CREATE_OWNER,
            ['id' => $account->super_id, 'account_id' => $account->id, 'email' => $contact->data] + ($phone ? ['phone' => str_replace('+', '', $phone)] : []),
            ClientSuper::tableName(),
            $account->super_id
        );

        return $this->redirect(Url::to(['/client/view', 'id' => $account->id]));
    }

    /**
     * Сохраняет комментарий о заблокированном клиенте
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\HttpException
     */
    public function actionSaveComment()
    {
        $accountId = Yii::$app->request->post('id');
        $comment = Yii::$app->request->post('text');
        if (!$accountId) {
            throw new HttpException(400, 'Invalid parameters exception');
        }
        $clientBlockedComment = ClientBlockedComment::findOne(['account_id' => $accountId]);
        if (!$comment && $clientBlockedComment) {
            if (!$clientBlockedComment->delete()) {
                throw new ModelException('Ошибка при удалении комментария о заблокированном пользователе');
            }
            return;
        }
        if (!$clientBlockedComment) {
            $clientBlockedComment = new ClientBlockedComment();
            $clientBlockedComment->account_id = $accountId;
        }
        $clientBlockedComment->comment = $comment;
        if (!$clientBlockedComment->save()) {
            throw new ModelValidationException($clientBlockedComment);
        }
    }

    public function actionCheckEmailExists($clientAccountId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'result' => ClientContact::find()->where([
                'client_id' => $clientAccountId,
                'type' => ClientContact::TYPE_EMAIL,
                'is_official' => 1
            ])->exists()];
    }
}
