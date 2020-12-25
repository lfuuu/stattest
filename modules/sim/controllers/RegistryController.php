<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\modules\sim\filters\RegistryFilter;
use app\modules\sim\forms\registry\Form;
use Yii;
use yii\filters\AccessControl;

/**
 * Реест SIM-карт
 */
class RegistryController extends BaseController
{
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
                        'actions' => ['index', 'view'],
                        'roles' => ['sim.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'start', 'cancel', 'restore'],
                        'roles' => ['sim.write'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список заливок
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new RegistryFilter();
        if ($sort = \Yii::$app->request->get('sort')) {
            $filterModel->sort = $sort;
        }
        if ($date = \Yii::$app->request->get('date')) {
            $filterModel->date = $date;
        }
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Добавление заливки
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function actionAdd()
    {
        try {
            $addForm = new Form();

            if ($addForm->tryToSave()) {
                Yii::$app->session->addFlash('success', 'Заливка добавлена');

                return $this->redirect(['index']);
            }
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->render('add', [
            'model' => $addForm,
        ]);
    }

    /**
     * Просмотр
     *
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionView($id = 0)
    {
        $registry = null;
        try {
            $form = new Form(['id' => $id]);
            $registry = $form->registry;
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->render('view', [
            'model' => $registry,
            'form' => $form,
        ]);
    }

    /**
     * Start
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionStart($id = 0)
    {
        try {
            Form::start($id);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sim/registry/');
    }

    /**
     * Cancel
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCancel($id = 0)
    {
        try {
            Form::cancel($id);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sim/registry/');
    }

    /**
     * Restore
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRestore($id = 0)
    {
        try {
            Form::restore($id);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sim/registry/');
    }
}
