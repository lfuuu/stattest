<?php
/**
 * Типы услуг
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\modules\uu\filter\ServiceFolderFilter;
use app\modules\uu\forms\ServiceFolderForm;
use app\modules\uu\forms\ServiceTypeEditForm;
use Yii;
use yii\filters\AccessControl;


class ServiceFolderController extends BaseController
{
    /**
     * Права доступа
     *
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
                        'actions' => ['index'],
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['tarifs.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new ServiceFolderFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', ['filterModel' => $filterModel]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($service_type_id)
    {
        try {
            /** @var ServiceFolderForm $formModel */
            $formModel = new ServiceFolderForm(['service_type_id' => $service_type_id]);
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('//layouts/empty', ['content' => '']);
        }

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', $formModel->id ? 'The object was saved successfully' : 'The object was dropped successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('edit', ['formModel' => $formModel]);

    }
}