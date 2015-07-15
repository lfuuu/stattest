<?php
namespace app\controllers\voip;

use app\models\billing\GeoRegion;
use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use app\models\voip\Prefixlist;
use app\forms\voip\prefixlist\PrefixlistListForm;
use app\forms\voip\prefixlist\PrefixlistForm;

class PrefixlistController extends BaseController
{
    /*
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'files', 'file-download'],
                        'roles' => ['voip.access'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'file-upload', 'file-parse'],
                        'roles' => ['voip.admin'],
                    ],
                ],
            ],
        ];
    }
    */

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

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->scenario == 'save' && $model->save()) {
                $this->redirect('index');
            }
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

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->scenario == 'save' && $model->save()) {
                $this->redirect('index');
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }

}