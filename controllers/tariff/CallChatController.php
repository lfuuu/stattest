<?php

namespace app\controllers\tariff;

use app\forms\tariff\call_chat\CallChatForm;
use app\forms\tariff\call_chat\CallChatListForm;
use app\models\TariffCallChat;
use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\filters\AccessControl;

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
                        'actions' => ['index'],
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit'],
                        'roles' => ['tarifs.edit'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new CallChatListForm();
        $model->load(Yii::$app->request->queryParams);

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $model,
        ]);
    }

    public function actionAdd()
    {
        $model = new CallChatForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->addFlash('success', 'Запись добавлена');
            return $this->redirect('index');
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => true,
        ]);
    }

    public function actionEdit($id)
    {
        $model = new CallChatForm;

        $tariff = TariffCallChat::findOne($id);
        Assert::isObject($tariff);

        $model->setAttributes($tariff->getAttributes(), false);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($tariff)) {
            Yii::$app->session->addFlash('success', 'Запись сохранена');
            return $this->redirect('index');
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }

}