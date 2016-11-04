<?php

namespace app\controllers\dictionary;

use app\models\EntryPoint;
use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\models\InvoiceSettings;

/**
 * Class EntryPointController
 * @package app\controllers\dictionary
 */
class EntryPointController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['organization.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'delete'],
                        'roles' => ['organization.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => EntryPoint::find(),
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionAdd()
    {
        /** @var InvoiceSettings $model */
        $model = new EntryPoint;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/dictionary/entry-point');
        }

        if ($model->hasErrors()) {
            Yii::$app->session->addFlash('error', implode("<br>", $model->getFirstErrors()));
        }


        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование точки входа
     *
     * @param int $id id точки входа
     * @return string
     * @throws \Exception
     */
    public function actionEdit($id = 0)
    {
        $model = EntryPoint::findOne(['id' => $id]);

        if (!$model) {
            throw new \Exception('Точка входа не найдена');
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/dictionary/entry-point');
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Удаленеи точки входа
     *
     * @param int $id id точки входа
     */
    public function actionDelete($id)
    {
        $model = EntryPoint::findOne(['id' => $id]);

        if (!$model) {
            Yii::$app->session->addFlash('error', 'Точка входа не найдена!');
        } else {
            $model->delete();

            Yii::$app->session->addFlash('success', 'Точка входа удалена!');
        }

        $this->redirect('/dictionary/entry-point');
    }

}