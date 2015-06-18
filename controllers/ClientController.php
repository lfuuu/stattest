<?php
namespace app\controllers;

use app\forms\client\AccountEditForm;
use app\forms\client\ContractEditForm;
use app\forms\client\ContragentEditForm;
use app\models\ClientAccount;
use app\models\ClientSuper;
use app\models\Trouble;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\filters\AccessControl;

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
                        'roles' => ['@'],
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
        $client = ClientAccount::findOne($id);
        if (!$client)
            throw new Exception('Client not found');

        //Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', $client->id);
        $this->applyFixClient($client->id);

        $sClient = ClientSuper::findOne($client->super_id);

        $contractForm = new ContractEditForm(['id' => $client->contract_id]);

        $troubles = Trouble::find()
            ->andWhere(['client' => $client->client])
            ->andWhere(['server_id' => 0])
            ->orderBy('`date_creation` DESC')
            ->all();

        $services = [];
        $services['voip'] = UsageVoip::find()->where(['client' => $client->client])->all();
        $services['welltime'] = UsageWelltime::find()->where(['client' => $client->client])->all();
        $services['extra'] = UsageExtra::find()->where(['client' => $client->client])->all();
        $services['virtpbx'] = UsageVirtpbx::find()->where(['client' => $client->client])->all();
        $services['sms'] = UsageSms::find()->where(['client' => $client->client])->all();
        $services['ipport'] = UsageIpPorts::find()->where(['client' => $client->client])->all();

        return
            $this->render(
                'view',
                [
                    'sClient' => $sClient,
                    'activeClient' => $client,
                    'contractForm' => $contractForm,
                    'troubles' => $troubles,
                    'services' => $services,
                ]
            );
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $contragent = new ContragentEditForm(['super_id' => 1]);
        $contract = new ContractEditForm(['contragent_id' => 1, 'super_id' => 1]);
        $client = new AccountEditForm(['contract_id' => 1, 'contragent_id' => 1, 'super_id' => 1]);
        if ($request) {
            $transaction = Yii::$app->db->beginTransaction();
            $commit = false;
            $super = new ClientSuper();
            $super->setAttribute('name', 'autocreate');
            if ($super->save()) {
                unset($request['ContragentEditForm']['super_id']);
                $contragent = new ContragentEditForm(['super_id' => $super->id]);
                if ($contragent->load($request) && $contragent->validate() && $contragent->save()) {
                    $contract = new ContractEditForm(['contragent_id' => $contragent->id]);
                    if ($contract->load($request) && $contract->validate() && $contract->save()) {
                        $client = new AccountEditForm(['id' => $contract->id]);
                        if ($client->load($request) && $client->validate() && $client->save())
                            $commit = true;
                    }
                }
            }
            if ($commit) {
                $transaction->commit();
                return $this->redirect(['client/view', 'id' => $client->id]);
            } else {
                $transaction->rollback();
            }
        }

        return $this->render('create', ['contragent' => $contragent, 'client' => $client, 'contract' => $contract]);
    }
}