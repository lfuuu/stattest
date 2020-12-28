<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\modules\sim\filters\RegistryFilter;
use app\modules\sim\forms\registry\Form;
use app\modules\sim\models\RegionSettings;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

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
                        'actions' => ['add', 'get-region-setting', 'start', 'cancel', 'restore'],
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
     * Получение настроек для SIN по региону
     *
     * @param $regionSettingsId
     * @return string
     */
    public function actionGetRegionSetting($regionSettingsId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [];
        if ($regionSettings = RegionSettings::findOne(['id' => $regionSettingsId])) {
            $result = [
                'id' => $regionSettings->id,
                'iccid_prefix' => $regionSettings->getICCIDPrefix(),
                'iccid_length' => $regionSettings->iccid_range_length,
                'imsi_prefix' => $regionSettings->getIMSIPrefix(),
                'imsi_length' => $regionSettings->imsi_range_length,
            ];
        }

        return json_encode($result);
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
