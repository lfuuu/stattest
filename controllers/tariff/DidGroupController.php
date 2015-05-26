<?php
namespace app\controllers\tariff;

use app\forms\tariff\DidGroupForm;
use app\forms\tariff\DidGroupListForm;
use app\models\DidGroup;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\classes\BaseController;
use yii\web\BadRequestHttpException;

class DidGroupController extends BaseController
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

    public function actionList()
    {
        $model = new DidGroupListForm();
        $model->load(Yii::$app->request->getQueryParams());

        return $this->render('list', [
            'dataProvider' => $model->spawnDataProvider(),
            'filterModel' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $model = new DidGroupForm();

        $tariff = DidGroup::findOne($id);
        if ($tariff === null) throw new BadRequestHttpException();
        $model->setAttributes($tariff->getAttributes(), false);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
