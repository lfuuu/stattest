<?php

namespace app\controllers\usage;

use app\classes\Assert;
use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\forms\usage\UsageCallChatEditForm;
use app\forms\usage\UsageCallChatListForm;
use app\models\UsageCallChat;
use Yii;
use yii\filters\AccessControl;


class CallChatController extends BaseController
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $clientAccount = $this->_getCurrentClientAccount();

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

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionAdd()
    {
        $clientAccount = $this->_getCurrentClientAccount();
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

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        $usage = UsageCallChat::findOne($id);
        $clientAccount = $usage->clientAccount;

        $model = new UsageCallChatEditForm();
        $model->initModel($clientAccount, $usage);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->edit()) {
            Yii::$app->session->addFlash('success', 'Услуга сохранена');
            return $this->redirect(['edit', 'id' => $usage->id]);
        }

        return $this->render('edit', [
            'model' => $model,
            'clientAccount' => $clientAccount
        ]);
    }

}
