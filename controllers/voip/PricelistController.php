<?php
namespace app\controllers\voip;

use app\classes\Assert;
use app\forms\billing\PricelistAddForm;
use app\models\billing\NetworkConfig;
use Yii;
use app\classes\BaseController;
use app\forms\billing\PricelistEditForm;
use app\models\billing\Pricelist;
use app\models\Region;
use yii\filters\AccessControl;

class PricelistController extends BaseController
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

    public function actionList($local, $orig, $connectionPointId = 0)
    {
        $query =
            Pricelist::find()
                ->orderBy('region desc, name asc');

        $query->andWhere('local = ' . ($local > 0 ? 'true' : 'false'));
        $query->andWhere('orig = ' . ($orig > 0 ? 'true' : 'false'));
        if ($connectionPointId > 0) {
            $query->andWhere(['region' => $connectionPointId]);
        }

        $pricelists = $query->all();


        return $this->render("list", [
            'connectionPointId' => $connectionPointId,
            'pricelists' => $pricelists,
            'connectionPoints' => Region::dao()->getList(),
            'networkConfigs' => NetworkConfig::dao()->getList(),
            'orig' => (int)$orig,
            'local' => (int)$local,
        ]);

    }


    public function actionAdd($local = 0, $orig = 0, $connectionPointId = 0)
    {
        $model = new PricelistAddForm();
        $model->orig = $orig;
        $model->local = $local;
        $model->connection_point_id = $connectionPointId;
        $model->tariffication_by_minutes = 0;
        $model->tariffication_full_first_minute = 0;
        $model->initiate_mgmn_cost = 0;
        $model->initiate_zona_cost = 0;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("add", [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $pricelist = Pricelist::findOne($id);

        Assert::isObject($pricelist);

        $model = new PricelistEditForm();
        $model->setAttributes($pricelist->getAttributes(), false);
        $model->connection_point_id = $pricelist->region;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render("edit", [
            'model' => $model,
            'pricelist' => $pricelist,
        ]);
    }
}