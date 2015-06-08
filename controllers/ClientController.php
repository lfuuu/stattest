<?php
namespace app\controllers;

use app\forms\client\ClientEditForm;
use app\forms\contract\ContractEditForm;
use app\models\Client;
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
use yii\helpers\Url;

class ClientController extends BaseController
{

    public function actionSuperclientview($id)
    {
        $model = ClientSuper::findOne($id);
        return $this->render('superclientview', ['sClient' => $model]);
    }

    public function actionClientview($id)
    {
        $client = Client::findOne($id);
        if (!$client)
            throw new Exception('Client not found');

        //Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', $client->client);

        $sClient = ClientSuper::findOne($client->super_id);

        $contractForm = new ContractEditForm(['id' => $client->contract_id]);

        $troubles = Trouble::findAllByClientId($client->client)
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

        return $this->render('clientview', ['sClient' => $sClient, 'activeClient' => $client, 'contractForm' => $contractForm, 'troubles' => $troubles, 'services' => $services,]);
    }

    public function actionCreate($parentId)
    {
        $model = new ClientEditForm(['contract_id' => $parentId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::toRoute(['client/clientview', 'id' => $model->id]));
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionEdit($id)
    {
        $model = new ClientEditForm(['id' => $id]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::toRoute(['client/clientview', 'id' => $id]));
        }

        return $this->render("edit", [
            'model' => $model
        ]);

    }

    public function actionIndex()
    {
        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if($dataProvider->query->count() == 1)
            return $this->redirect(Url::toRoute(['client/clientview', 'id' => $dataProvider->query->one()->id]));

        return $this->render('index', [
//          'searchModel' => $dataProvider,
            'dataProvider' => $dataProvider,
        ]);
    }

}