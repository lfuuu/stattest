<?php
namespace app\controllers;

use app\classes\grid\GridFactory;
use app\classes\Assert;
use app\classes\voip\VoipStatus;
use app\forms\client\AccountEditForm;
use app\forms\client\ContractEditForm;
use app\forms\client\ContragentEditForm;
use app\models\ClientAccount;
use app\models\ClientSearch;
use app\models\ClientSuper;
use app\models\Number;
use app\models\TechCpe;
use app\models\Trouble;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;
use app\models\Saldo;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Response;

class ClientController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
        ];
    }

    public function actionView($id)
    {
        $account = ClientAccount::findOne($id);
        if (!$account)
            throw new Exception('Client not found');

        //Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', $account->id);
        $this->applyFixClient($account->id);

        $client = ClientSuper::findOne($account->super_id);

        $contractForm = new ContractEditForm(['id' => $account->contract_id]);

        $troubles = Trouble::find()
            ->andWhere(['client' => $account->client])
            ->andWhere(['server_id' => 0])
            ->orderBy('`date_creation` DESC')
            ->all();

        $serverTroubles = Trouble::findAll(['id' => Trouble::dao()->getServerTroublesIDsForClient($account)]);

        $services = [];

        $services['voip'] =
            UsageVoip::find()
                ->where(['client' => $account->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['device'] =
            TechCpe::find()
                ->where(['client' => $account->client])
                ->hideNotLinked()
                ->orderBy(['actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['welltime'] =
            UsageWelltime::find()
                ->where(['client' => $account->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['extra'] =
            UsageExtra::find()
                ->where(['client' => $account->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['virtpbx'] =
            UsageVirtpbx::find()
                ->where(['client' => $account->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['sms'] =
            UsageSms::find()
                ->where(['client' => $account->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['ipport'] =
            UsageIpPorts::find()
                ->where(['client' => $account->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['voip_reserve'] =
                Number::find()
                    ->where(['status' => Number::STATUS_RESERVED])
                    ->andWhere(['client_id' => $account->id])
                    ->all();
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
                ]
            );
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $contragent = new ContragentEditForm();
        $contract = new ContractEditForm();
        $account = new AccountEditForm();
        if ($request) {
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
                        if ($account->load($request) && $account->validate() && $account->save())
                            $commit = true;
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

        return $this->render('create', ['contragent' => $contragent, 'account' => $account, 'contract' => $contract]);
    }

    public function actionGrid($businessProcessId, $folderId = null)
    {
        $accountGrid = GridFactory::me()->getAccountGridByBusinessProcessId($businessProcessId);
        $gridFolder = $accountGrid->getFolder($folderId);
        $gridFolder->setAttributes(Yii::$app->request->get());

        $dataProvider = $gridFolder->spawnDataProvider();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'activeFolder' => $gridFolder,
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
                    'value' => $model->contract->contragent->name ? $model->contract->contragent->name : $model->contract->contragent->name_full,
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

}