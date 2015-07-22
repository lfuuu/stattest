<?php
namespace app\controllers\voip;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\voip\Destination;
use app\forms\voip\destination\DestinationListForm;
use app\forms\voip\destination\DestinationForm;
use yii\helpers\ArrayHelper;

class DestinationController extends BaseController
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
                        'roles' => ['voip.access'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'delete'],
                        'roles' => ['voip.admin'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new DestinationListForm;
        $model->load(Yii::$app->request->getQueryParams());

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdd()
    {
        $model = new DestinationForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('index');
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => true,
        ]);
    }

    public function actionEdit($id)
    {
        $model = new DestinationForm;

        $destination = Destination::findOne($id);
        Assert::isObject($destination);

        $model->setAttributes($destination->getAttributes(), false);
        $model->prefixes = ArrayHelper::getColumn($destination->destinationPrefixes, 'prefixlist_id');

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($destination)) {
            $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }

    public function actionDelete($id)
    {
        $destination = Destination::findOne($id);
        Assert::isObject($destination);

        $model = new DestinationForm;
        $model->delete($destination);

        $this->redirect('/voip/destination');
    }

}