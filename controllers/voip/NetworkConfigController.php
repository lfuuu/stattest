<?php
namespace app\controllers\voip;

use app\classes\Assert;
use app\forms\billing\NetworkConfigAddForm;
use app\forms\billing\NetworkConfigEditForm;
use app\forms\billing\PricelistAddForm;
use app\models\billing\NetworkConfig;
use Yii;
use app\classes\BaseController;
use app\forms\billing\PricelistEditForm;
use app\models\billing\Pricelist;
use app\models\Region;
use yii\filters\AccessControl;

class NetworkConfigController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list'],
                        'roles' => ['voip.access'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add','edit'],
                        'roles' => ['voip.admin'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $query =
            NetworkConfig::find()
                ->orderBy('instance_id desc, name asc');

        $list = $query->all();
        $connectionPoints = Region::dao()->getList();
        $pricelists = Pricelist::dao()->getList();

        return $this->render("list", [
            'list' => $list,
            'connectionPoints' => $connectionPoints,
            'pricelists' => $pricelists,
        ]);
    }



    public function actionAdd()
    {
        $model = new NetworkConfigAddForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("add", [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $networkConfig = NetworkConfig::findOne($id);
        Assert::isObject($networkConfig);

        $model = new NetworkConfigEditForm();
        $model->setAttributes($networkConfig->getAttributes(), false);
        $model->connection_point_id = $networkConfig->instance_id;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("edit", [
            'model' => $model,
            'networkConfig' => $networkConfig,
        ]);
    }
}