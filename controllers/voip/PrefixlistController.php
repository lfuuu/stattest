<?php
namespace app\controllers\voip;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\voip\Prefixlist;
use app\forms\voip\prefixlist\PrefixlistListForm;
use app\forms\voip\prefixlist\PrefixlistForm;

class PrefixlistController extends BaseController
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
        $model = new PrefixlistListForm;
        $model->load(Yii::$app->request->getQueryParams());

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdd()
    {
        $model = new PrefixlistForm;

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
        $model = new PrefixlistForm;

        $prefixlist = Prefixlist::findOne($id);
        Assert::isObject($prefixlist);

        $model->setAttributes($prefixlist->getAttributes(), false);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($prefixlist)) {
            $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }

    public function actionDelete($id)
    {
        $prefixlist = Prefixlist::findOne($id);
        Assert::isObject($prefixlist);

        $model = new PrefixlistForm;
        $model->delete($prefixlist);

        $this->redirect('/voip/prefixlist');
    }

}