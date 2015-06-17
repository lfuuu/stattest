<?php
namespace app\controllers;

use app\forms\client\ClientEditForm;
use app\forms\contract\ContractEditForm;
use app\models\ClientAccount;
use app\models\ClientBP;
use app\models\ClientGridSettings;
use app\models\ClientSearch;
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
use yii\web\Response;
use yii\helpers\Url;

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
                        'actions' => ['view'],
                        'roles' => ['clients.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['edit'],
                        'roles' => ['clients.edit'],
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

    public function actionCreate($parentId)
    {
        $model = new ClientEditForm(['contract_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['client/view', 'id' => $model->id]);
        }

        return $this->render("edit", [
            'model' => $model
        ]);
    }

    public function actionEdit($id, $date = null)
    {
        $model = new ClientEditForm(['id' => $id, 'ddate' => $date]);

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

    public function actionSetvoipdisable($id)
    {
        $model = ClientAccount::findOne($id);
        if (!$model)
            throw new Exception('ЛС не найден');
        $model->voip_disabled = !$model->voip_disabled;
        $model->save();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }

    public function actionLoadbpstatuses()
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