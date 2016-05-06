<?php
namespace app\controllers\usage;

use app\classes\Assert;
use app\models\UsageCallChat;
use Yii;
use app\models\ClientAccount;
use yii\filters\AccessControl;
use app\classes\BaseController;

use app\forms\usage\UsageCallChatListForm;
use app\forms\usage\UsageCallChatEditForm;


class CallChatController extends BaseController
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        global $fixclient_data;

        $clientAccountId = null;
        if ($fixclient_data && isset($fixclient_data['id'])) {
            $clientAccountId = $fixclient_data['id'];
        }
        $clientAccount = ClientAccount::findOne($clientAccountId);

        $model = new UsageCallChatListForm();

        if ($clientAccount) {
            $model->client = $clientAccount->client;
        }

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdd()
    {
        global $fixclient_data;

        $clientAccountId = null;

        if ($fixclient_data && isset($fixclient_data['id'])) {
            $clientAccountId = $fixclient_data['id'];
        }

        $clientAccount = ClientAccount::findOne($clientAccountId);

        Assert::isObject($clientAccount);

        $model = new UsageCallChatEditForm();
        $model->initModel($clientAccount);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->add()) {
                Yii::$app->session->addFlash('success', 'Услуга добавлена');
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'clientAccount' => $model->clientAccount,
        ]);
    }

    public function actionEdit($id)
    {
        $usage = UsageCallChat::findOne($id);
        $clientAccount = $usage->clientAccount;


        $model = new UsageCallChatEditForm();
        $model->initModel($clientAccount, $usage);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->edit()) {
                Yii::$app->session->addFlash('success', 'Услуга сохранена');
                return $this->redirect(['edit', 'id' => $usage->id]);
            }
        }


        return $this->render('edit', [
            'model' => $model,
            'clientAccount' => $clientAccount
        ]);
    }

}
